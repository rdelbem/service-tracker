<?php

namespace STOLMC_Service_Tracker\Tests\Unit;

use Brain\Monkey\Functions;
use Mockery;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Progress_Service;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Progress_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Progress_Create_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Progress_Delete_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Progress_Update_Dto;
use STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Cases_Repository;
use STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Case_Progress_Repository;
use STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Progress_Repository;

class Progress_Service_Transaction_Test extends API_TestCase {

	private function create_service_with_mocks(
		\Mockery\MockInterface $cases_repository,
		\Mockery\MockInterface $progress_repository
	): STOLMC_Service_Tracker_Progress_Service {
		$service = Mockery::mock(
			STOLMC_Service_Tracker_Progress_Service::class,
			[ $cases_repository, $progress_repository ]
		)->makePartial();
		$service->shouldAllowMockingProtectedMethods();
		$service->shouldReceive( 'move_uploaded_file_to_destination' )->andReturn( true );
		$service->shouldReceive( 'delete_uploaded_file' )->andReturnNull();

		return $service;
	}

	private function stub_upload_functions(): void {
		Functions\when( 'wp_upload_dir' )->justReturn(
			[
				'path' => '/tmp',
				'url'  => 'http://localhost/uploads',
			]
		);
		Functions\when( 'sanitize_file_name' )->returnArg( 1 );
		Functions\when( 'wp_unique_filename' )->justReturn( 'a.txt' );
	}

	public function test_upload_file_returns_error_when_transaction_start_fails(): void {
		$this->stub_upload_functions();

		$cases_repository = Mockery::mock( STOLMC_Service_Tracker_Cases_Repository::class );
		$progress_repository = Mockery::mock( STOLMC_Service_Tracker_Progress_Repository::class );

		$progress_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( 10 )
			->andReturn( new STOLMC_Service_Tracker_Progress_Dto( 10, 1, 77, 'x', '[]' ) );
		$progress_repository->shouldReceive( 'begin_transaction' )
			->once()
			->andReturn( false );
		$progress_repository->shouldReceive( 'update_by_id_for_case' )->never();

		$service = $this->create_service_with_mocks( $cases_repository, $progress_repository );
		$result  = $service->upload_file(
			[
				'file' => [
					'error'    => UPLOAD_ERR_OK,
					'type'     => 'text/plain',
					'size'     => 10,
					'name'     => 'a.txt',
					'tmp_name' => '/tmp/php-upload-a',
				],
			],
			10
		);

		$this->assertFalse( $result->success );
		$this->assertSame( 'transaction_start_failed', $result->error_code );
	}

	public function test_upload_file_rolls_back_when_db_update_fails(): void {
		$this->stub_upload_functions();

		$cases_repository = Mockery::mock( STOLMC_Service_Tracker_Cases_Repository::class );
		$progress_repository = Mockery::mock( STOLMC_Service_Tracker_Progress_Repository::class );

		$progress_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( 10 )
			->andReturn( new STOLMC_Service_Tracker_Progress_Dto( 10, 1, 77, 'x', '[]' ) );
		$progress_repository->shouldReceive( 'begin_transaction' )
			->once()
			->andReturn( true );
		$progress_repository->shouldReceive( 'update_by_id_for_case' )
			->once()
			->andReturn( false );
		$progress_repository->shouldReceive( 'rollback_transaction' )
			->once()
			->andReturn( true );
		$progress_repository->shouldReceive( 'commit_transaction' )->never();

		$service = $this->create_service_with_mocks( $cases_repository, $progress_repository );
		$result  = $service->upload_file(
			[
				'file' => [
					'error'    => UPLOAD_ERR_OK,
					'type'     => 'text/plain',
					'size'     => 10,
					'name'     => 'a.txt',
					'tmp_name' => '/tmp/php-upload-b',
				],
			],
			10
		);

		$this->assertFalse( $result->success );
		$this->assertSame( 'attachment_update_failed', $result->error_code );
	}

	public function test_upload_file_rolls_back_when_commit_fails(): void {
		$this->stub_upload_functions();

		$cases_repository = Mockery::mock( STOLMC_Service_Tracker_Cases_Repository::class );
		$progress_repository = Mockery::mock( STOLMC_Service_Tracker_Progress_Repository::class );

		$progress_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( 10 )
			->andReturn( new STOLMC_Service_Tracker_Progress_Dto( 10, 1, 77, 'x', '[]' ) );
		$progress_repository->shouldReceive( 'begin_transaction' )
			->once()
			->andReturn( true );
		$progress_repository->shouldReceive( 'update_by_id_for_case' )
			->once()
			->andReturn( 1 );
		$progress_repository->shouldReceive( 'commit_transaction' )
			->once()
			->andReturn( false );
		$progress_repository->shouldReceive( 'rollback_transaction' )
			->once()
			->andReturn( true );

		$service = $this->create_service_with_mocks( $cases_repository, $progress_repository );
		$result  = $service->upload_file(
			[
				'file' => [
					'error'    => UPLOAD_ERR_OK,
					'type'     => 'text/plain',
					'size'     => 10,
					'name'     => 'a.txt',
					'tmp_name' => '/tmp/php-upload-c',
				],
			],
			10
		);

		$this->assertFalse( $result->success );
		$this->assertSame( 'transaction_commit_failed', $result->error_code );
	}

	public function test_delete_progress_returns_error_when_transaction_start_fails(): void {
		Functions\when( 'do_action' )->justReturn( null );

		$cases_repository = Mockery::mock( STOLMC_Service_Tracker_Cases_Repository::class );
		$progress_repository = Mockery::mock( STOLMC_Service_Tracker_Progress_Repository::class );

		$progress_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( 15 )
			->andReturn( new STOLMC_Service_Tracker_Progress_Dto( 15, 4, 77, 'x', '[]' ) );
		$progress_repository->shouldReceive( 'begin_transaction' )
			->once()
			->andReturn( false );
		$cases_repository->shouldReceive( 'progress' )->never();

		$service = $this->create_service_with_mocks( $cases_repository, $progress_repository );
		$result  = $service->delete_progress( new STOLMC_Service_Tracker_Progress_Delete_Dto( 15 ) );

		$this->assertFalse( $result->success );
		$this->assertSame( 'transaction_start_failed', $result->error_code );
	}

	public function test_delete_progress_rolls_back_when_commit_fails(): void {
		Functions\when( 'do_action' )->justReturn( null );

		$cases_repository = Mockery::mock( STOLMC_Service_Tracker_Cases_Repository::class );
		$progress_repository = Mockery::mock( STOLMC_Service_Tracker_Progress_Repository::class );
		$case_progress_repository = Mockery::mock( STOLMC_Service_Tracker_Case_Progress_Repository::class );

		$progress_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( 15 )
			->andReturn( new STOLMC_Service_Tracker_Progress_Dto( 15, 4, 77, 'x', '[]' ) );
		$progress_repository->shouldReceive( 'begin_transaction' )
			->once()
			->andReturn( true );
		$cases_repository->shouldReceive( 'progress' )
			->once()
			->with( 77 )
			->andReturn( $case_progress_repository );
		$case_progress_repository->shouldReceive( 'delete_by_id' )
			->once()
			->with( 15 )
			->andReturn( 1 );
		$progress_repository->shouldReceive( 'commit_transaction' )
			->once()
			->andReturn( false );
		$progress_repository->shouldReceive( 'rollback_transaction' )
			->once()
			->andReturn( true );

		$service = $this->create_service_with_mocks( $cases_repository, $progress_repository );
		$result  = $service->delete_progress( new STOLMC_Service_Tracker_Progress_Delete_Dto( 15 ) );

		$this->assertFalse( $result->success );
		$this->assertSame( 'transaction_commit_failed', $result->error_code );
	}

	public function test_update_progress_returns_error_when_transaction_start_fails(): void {
		$cases_repository = Mockery::mock( STOLMC_Service_Tracker_Cases_Repository::class );
		$progress_repository = Mockery::mock( STOLMC_Service_Tracker_Progress_Repository::class );

		$progress_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( 22 )
			->andReturn( new STOLMC_Service_Tracker_Progress_Dto( 22, 1, 77, 'old', '[]' ) );
		$progress_repository->shouldReceive( 'begin_transaction' )
			->once()
			->andReturn( false );
		$cases_repository->shouldReceive( 'progress' )->never();

		$service = $this->create_service_with_mocks( $cases_repository, $progress_repository );
		$result  = $service->update_progress( new STOLMC_Service_Tracker_Progress_Update_Dto( 22, [ 'text' => 'new' ] ) );

		$this->assertFalse( $result->success );
		$this->assertSame( 'transaction_start_failed', $result->error_code );
	}

	public function test_update_progress_rolls_back_when_commit_fails(): void {
		$cases_repository = Mockery::mock( STOLMC_Service_Tracker_Cases_Repository::class );
		$progress_repository = Mockery::mock( STOLMC_Service_Tracker_Progress_Repository::class );
		$case_progress_repository = Mockery::mock( STOLMC_Service_Tracker_Case_Progress_Repository::class );

		$progress_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( 22 )
			->andReturn( new STOLMC_Service_Tracker_Progress_Dto( 22, 1, 77, 'old', '[]' ) );
		$progress_repository->shouldReceive( 'begin_transaction' )
			->once()
			->andReturn( true );
		$cases_repository->shouldReceive( 'progress' )
			->once()
			->with( 77 )
			->andReturn( $case_progress_repository );
		$case_progress_repository->shouldReceive( 'update_by_id' )
			->once()
			->with( 22, [ 'text' => 'new' ] )
			->andReturn( 1 );
		$progress_repository->shouldReceive( 'commit_transaction' )
			->once()
			->andReturn( false );
		$progress_repository->shouldReceive( 'rollback_transaction' )
			->once()
			->andReturn( true );

		$service = $this->create_service_with_mocks( $cases_repository, $progress_repository );
		$result  = $service->update_progress( new STOLMC_Service_Tracker_Progress_Update_Dto( 22, [ 'text' => 'new' ] ) );

		$this->assertFalse( $result->success );
		$this->assertSame( 'transaction_commit_failed', $result->error_code );
	}

	public function test_create_progress_returns_error_when_transaction_start_fails(): void {
		$cases_repository = Mockery::mock( STOLMC_Service_Tracker_Cases_Repository::class );
		$progress_repository = Mockery::mock( STOLMC_Service_Tracker_Progress_Repository::class );

		$progress_repository->shouldReceive( 'begin_transaction' )
			->once()
			->andReturn( false );
		$cases_repository->shouldReceive( 'progress' )->never();

		$service = $this->create_service_with_mocks( $cases_repository, $progress_repository );
		$result  = $service->create_progress(
			new STOLMC_Service_Tracker_Progress_Create_Dto(
				[
					'id_case' => 77,
					'id_user' => 1,
					'text'    => 'created',
				]
			)
		);

		$this->assertFalse( $result->success );
		$this->assertSame( 'transaction_start_failed', $result->error_code );
	}

	public function test_create_progress_rolls_back_when_commit_fails(): void {
		$cases_repository = Mockery::mock( STOLMC_Service_Tracker_Cases_Repository::class );
		$progress_repository = Mockery::mock( STOLMC_Service_Tracker_Progress_Repository::class );
		$case_progress_repository = Mockery::mock( STOLMC_Service_Tracker_Case_Progress_Repository::class );

		$progress_repository->shouldReceive( 'begin_transaction' )
			->once()
			->andReturn( true );
		$cases_repository->shouldReceive( 'progress' )
			->once()
			->with( 77 )
			->andReturn( $case_progress_repository );
		$case_progress_repository->shouldReceive( 'create' )
			->once()
			->with( \Mockery::type( 'array' ) )
			->andReturn( 'Success, data was inserted' );
		$progress_repository->shouldReceive( 'commit_transaction' )
			->once()
			->andReturn( false );
		$progress_repository->shouldReceive( 'rollback_transaction' )
			->once()
			->andReturn( true );

		$GLOBALS['wpdb']->insert_id = 200;
		$service = $this->create_service_with_mocks( $cases_repository, $progress_repository );
		$result  = $service->create_progress(
			new STOLMC_Service_Tracker_Progress_Create_Dto(
				[
					'id_case' => 77,
					'id_user' => 1,
					'text'    => 'created',
				]
			)
		);

		$this->assertFalse( $result->success );
		$this->assertSame( 'transaction_commit_failed', $result->error_code );
	}
}
