<?php

namespace STOLMC_Service_Tracker\Tests\Unit;

use Brain\Monkey\Functions;
use Mockery;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Users_Service;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_User_Create_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_User_Delete_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_User_Update_Dto;
use STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Users_Repository;

class Users_Service_Transaction_Test extends API_TestCase {

	public function test_create_user_returns_error_when_transaction_start_fails(): void {
		$GLOBALS['wpdb'] = new class {
			public array $queries = [];

			public function query( string $sql ): int|false {
				$this->queries[] = $sql;
				if ( 'START TRANSACTION' === $sql ) {
					return false;
				}

				return 1;
			}
		};

		Functions\when( 'email_exists' )->justReturn( false );
		Functions\when( 'is_wp_error' )->alias(
			static fn( $value ): bool => $value instanceof \WP_Error
		);

		$repository = Mockery::mock( STOLMC_Service_Tracker_Users_Repository::class );
		$repository->shouldReceive( 'create' )->never();

		$service = new STOLMC_Service_Tracker_Users_Service( $repository );
		$dto     = new STOLMC_Service_Tracker_User_Create_Dto(
			[
				'name'  => 'User One',
				'email' => 'user1@example.com',
				'phone' => '123',
			]
		);

		$result = $service->create_user( $dto );

		$this->assertFalse( $result->success );
		$this->assertSame( 'transaction_start_failed', $result->error_code );
		$this->assertSame( [ 'START TRANSACTION' ], $GLOBALS['wpdb']->queries );
	}

	public function test_create_user_rolls_back_when_phone_meta_write_fails(): void {
		$GLOBALS['wpdb'] = new class {
			public array $queries = [];

			public function query( string $sql ): int|false {
				$this->queries[] = $sql;
				return 1;
			}
		};

		Functions\when( 'is_wp_error' )->justReturn( false );

		$repository = Mockery::mock( STOLMC_Service_Tracker_Users_Repository::class );
		$repository->shouldReceive( 'create' )
			->once()
			->andReturn( 101 );
		$repository->shouldReceive( 'update_meta' )
			->once()
			->with( 101, 'phone', '123' )
			->andReturn( false );

		$service = new STOLMC_Service_Tracker_Users_Service( $repository );
		$dto     = new STOLMC_Service_Tracker_User_Create_Dto(
			[
				'name'  => 'User One',
				'email' => 'user1@example.com',
				'phone' => '123',
			]
		);

		$result = $service->create_user( $dto );

		$this->assertFalse( $result->success );
		$this->assertSame( 'user_meta_update_failed', $result->error_code );
		$this->assertContains( 'ROLLBACK', $GLOBALS['wpdb']->queries );
	}

	public function test_update_user_commits_when_meta_updates_succeed(): void {
		$GLOBALS['wpdb'] = new class {
			public array $queries = [];

			public function query( string $sql ): int|false {
				$this->queries[] = $sql;
				return 1;
			}
		};

		Functions\when( 'is_wp_error' )->justReturn( false );
		Functions\when( 'get_user_by' )->justReturn( (object) [ 'ID' => 5 ] );

		$repository = Mockery::mock( STOLMC_Service_Tracker_Users_Repository::class );
		$repository->shouldReceive( 'update' )
			->once()
			->with( 5, [ 'name' => 'Updated' ] )
			->andReturn( 1 );
		$repository->shouldReceive( 'update_meta' )
			->once()
			->with( 5, 'cellphone', '999' )
			->andReturn( true );

		$service = new STOLMC_Service_Tracker_Users_Service( $repository );
		$dto     = new STOLMC_Service_Tracker_User_Update_Dto(
			5,
			[
				'name'      => 'Updated',
				'cellphone' => '999',
			]
		);

		$result = $service->update_user( $dto );

		$this->assertTrue( $result->success );
		$this->assertContains( 'COMMIT', $GLOBALS['wpdb']->queries );
	}

	public function test_update_user_rolls_back_when_repository_update_fails(): void {
		$GLOBALS['wpdb'] = new class {
			public array $queries = [];

			public function query( string $sql ): int|false {
				$this->queries[] = $sql;
				return 1;
			}
		};

		Functions\when( 'is_wp_error' )->alias(
			static fn( $value ): bool => $value instanceof \WP_Error
		);
		Functions\when( 'get_user_by' )->justReturn( (object) [ 'ID' => 5 ] );

		$repository = Mockery::mock( STOLMC_Service_Tracker_Users_Repository::class );
		$repository->shouldReceive( 'update' )
			->once()
			->with( 5, [ 'name' => 'Updated' ] )
			->andReturn( new \WP_Error( 'update_failed', 'fail update' ) );
		$repository->shouldReceive( 'update_meta' )->never();

		$service = new STOLMC_Service_Tracker_Users_Service( $repository );
		$dto     = new STOLMC_Service_Tracker_User_Update_Dto(
			5,
			[
				'name' => 'Updated',
			]
		);

		$result = $service->update_user( $dto );

		$this->assertFalse( $result->success );
		$this->assertSame( 'user_update_failed', $result->error_code );
		$this->assertContains( 'ROLLBACK', $GLOBALS['wpdb']->queries );
	}

	public function test_delete_user_returns_error_when_transaction_start_fails(): void {
		$GLOBALS['wpdb'] = new class {
			public array $queries = [];

			public function query( string $sql ): int|false {
				$this->queries[] = $sql;
				if ( 'START TRANSACTION' === $sql ) {
					return false;
				}

				return 1;
			}
		};

		Functions\when( 'is_wp_error' )->alias(
			static fn( $value ): bool => $value instanceof \WP_Error
		);
		Functions\when( 'get_user_by' )->justReturn( (object) [ 'ID' => 55 ] );

		$repository = Mockery::mock( STOLMC_Service_Tracker_Users_Repository::class );
		$repository->shouldReceive( 'delete' )->never();

		$service = new STOLMC_Service_Tracker_Users_Service( $repository );
		$result  = $service->delete_user( new STOLMC_Service_Tracker_User_Delete_Dto( 55 ) );

		$this->assertFalse( $result->success );
		$this->assertSame( 'transaction_start_failed', $result->error_code );
	}

	public function test_delete_user_rolls_back_when_commit_fails(): void {
		$GLOBALS['wpdb'] = new class {
			public array $queries = [];

			public function query( string $sql ): int|false {
				$this->queries[] = $sql;
				if ( 'COMMIT' === $sql ) {
					return false;
				}

				return 1;
			}
		};

		Functions\when( 'is_wp_error' )->alias(
			static fn( $value ): bool => $value instanceof \WP_Error
		);
		Functions\when( 'get_user_by' )->justReturn( (object) [ 'ID' => 55 ] );

		$repository = Mockery::mock( STOLMC_Service_Tracker_Users_Repository::class );
		$repository->shouldReceive( 'delete' )
			->once()
			->with( 55 )
			->andReturn( true );

		$service = new STOLMC_Service_Tracker_Users_Service( $repository );
		$result  = $service->delete_user( new STOLMC_Service_Tracker_User_Delete_Dto( 55 ) );

		$this->assertFalse( $result->success );
		$this->assertSame( 'transaction_commit_failed', $result->error_code );
		$this->assertContains( 'ROLLBACK', $GLOBALS['wpdb']->queries );
	}
}
