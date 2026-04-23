<?php

namespace STOLMC_Service_Tracker\Tests\Unit;

use Mockery;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Cases_Service;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Case_Create_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Case_Delete_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Case_Update_Dto;
use STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Cases_Repository;

class Cases_Service_Transaction_Test extends Unit_TestCase {

	public function test_delete_case_rolls_back_transaction_when_progress_delete_fails(): void {
		$repository = Mockery::mock( STOLMC_Service_Tracker_Cases_Repository::class );

		$repository->shouldReceive( 'begin_transaction' )
			->once()
			->andReturn( true );
		$repository->shouldReceive( 'delete_by_id' )
			->once()
			->with( 55 )
			->andReturn( 1 );
		$repository->shouldReceive( 'delete_progress_by_case_id' )
			->once()
			->with( 55 )
			->andReturn( false );
		$repository->shouldReceive( 'rollback_transaction' )
			->once()
			->andReturn( true );
		$repository->shouldReceive( 'commit_transaction' )
			->never();

		$service = new STOLMC_Service_Tracker_Cases_Service( $repository );

		$result = $service->delete_case( new STOLMC_Service_Tracker_Case_Delete_Dto( 55 ) );

		$this->assertFalse( $result->success );
		$this->assertSame( 'case_deletion_failed', $result->error_code );
	}

	public function test_delete_case_commits_transaction_on_success(): void {
		$repository = Mockery::mock( STOLMC_Service_Tracker_Cases_Repository::class );

		$repository->shouldReceive( 'begin_transaction' )
			->once()
			->andReturn( true );
		$repository->shouldReceive( 'delete_by_id' )
			->once()
			->with( 11 )
			->andReturn( 1 );
		$repository->shouldReceive( 'delete_progress_by_case_id' )
			->once()
			->with( 11 )
			->andReturn( 2 );
		$repository->shouldReceive( 'commit_transaction' )
			->once()
			->andReturn( true );
		$repository->shouldReceive( 'rollback_transaction' )
			->never();

		$service = new STOLMC_Service_Tracker_Cases_Service( $repository );

		$result = $service->delete_case( new STOLMC_Service_Tracker_Case_Delete_Dto( 11 ) );

		$this->assertTrue( $result->success );
		$this->assertSame( 200, $result->http_status );
	}

	public function test_update_case_returns_error_when_transaction_start_fails(): void {
		$repository = Mockery::mock( STOLMC_Service_Tracker_Cases_Repository::class );

		$repository->shouldReceive( 'begin_transaction' )
			->once()
			->andReturn( false );
		$repository->shouldReceive( 'update_by_id' )->never();

		$service = new STOLMC_Service_Tracker_Cases_Service( $repository );
		$result  = $service->update_case( new STOLMC_Service_Tracker_Case_Update_Dto( 12, [ 'title' => 'Updated' ] ) );

		$this->assertFalse( $result->success );
		$this->assertSame( 'transaction_start_failed', $result->error_code );
	}

	public function test_update_case_rolls_back_when_commit_fails(): void {
		$repository = Mockery::mock( STOLMC_Service_Tracker_Cases_Repository::class );

		$repository->shouldReceive( 'begin_transaction' )
			->once()
			->andReturn( true );
		$repository->shouldReceive( 'update_by_id' )
			->once()
			->with( 12, [ 'title' => 'Updated' ] )
			->andReturn( 1 );
		$repository->shouldReceive( 'commit_transaction' )
			->once()
			->andReturn( false );
		$repository->shouldReceive( 'rollback_transaction' )
			->once()
			->andReturn( true );

		$service = new STOLMC_Service_Tracker_Cases_Service( $repository );
		$result  = $service->update_case( new STOLMC_Service_Tracker_Case_Update_Dto( 12, [ 'title' => 'Updated' ] ) );

		$this->assertFalse( $result->success );
		$this->assertSame( 'transaction_commit_failed', $result->error_code );
	}

	public function test_create_case_returns_error_when_transaction_start_fails(): void {
		$repository = Mockery::mock( STOLMC_Service_Tracker_Cases_Repository::class );

		$repository->shouldReceive( 'begin_transaction' )
			->once()
			->andReturn( false );
		$repository->shouldReceive( 'create' )->never();

		$service = new STOLMC_Service_Tracker_Cases_Service( $repository );
		$result  = $service->create_case(
			new STOLMC_Service_Tracker_Case_Create_Dto(
				[
					'id_user' => 4,
					'title'   => 'New case',
				]
			)
		);

		$this->assertFalse( $result->success );
		$this->assertSame( 'transaction_start_failed', $result->error_code );
	}

	public function test_create_case_rolls_back_when_commit_fails(): void {
		$repository = Mockery::mock( STOLMC_Service_Tracker_Cases_Repository::class );

		$repository->shouldReceive( 'begin_transaction' )
			->once()
			->andReturn( true );
		$repository->shouldReceive( 'create' )
			->once()
			->with( \Mockery::type( 'array' ) )
			->andReturn( 'Success, data was inserted' );
		$repository->shouldReceive( 'get_last_insert_id' )
			->once()
			->andReturn( 99 );
		$repository->shouldReceive( 'commit_transaction' )
			->once()
			->andReturn( false );
		$repository->shouldReceive( 'rollback_transaction' )
			->once()
			->andReturn( true );

		$service = new STOLMC_Service_Tracker_Cases_Service( $repository );
		$result  = $service->create_case(
			new STOLMC_Service_Tracker_Case_Create_Dto(
				[
					'id_user' => 4,
					'title'   => 'New case',
				]
			)
		);

		$this->assertFalse( $result->success );
		$this->assertSame( 'transaction_commit_failed', $result->error_code );
	}
}
