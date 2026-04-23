<?php

namespace STOLMC_Service_Tracker\Tests\Unit;

use Brain\Monkey\Functions;
use Mockery;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Toggle_Service;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Case_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Toggle_Request_Dto;
use STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Toggle_Repository;

class Toggle_Service_Transaction_Test extends API_TestCase {

	public function test_toggle_case_status_returns_error_when_transaction_start_fails(): void {
		$repository = Mockery::mock( STOLMC_Service_Tracker_Toggle_Repository::class );
		$repository->shouldReceive( 'can_toggle' )
			->once()
			->with( 15 )
			->andReturn( true );
		$repository->shouldReceive( 'find_by_id' )
			->once()
			->with( 15 )
			->andReturn( new STOLMC_Service_Tracker_Case_Dto( 15, 7, 'Case A', 'open' ) );
		$repository->shouldReceive( 'begin_transaction' )
			->once()
			->andReturn( false );
		$repository->shouldReceive( 'close_case' )->never();
		$repository->shouldReceive( 'open_case' )->never();

		$service = new STOLMC_Service_Tracker_Toggle_Service( $repository );
		$result  = $service->toggle_case_status( new STOLMC_Service_Tracker_Toggle_Request_Dto( 15 ) );

		$this->assertFalse( $result->success );
		$this->assertSame( 'transaction_start_failed', $result->error_code );
	}

	public function test_toggle_case_status_rolls_back_when_close_fails(): void {
		$repository = Mockery::mock( STOLMC_Service_Tracker_Toggle_Repository::class );
		$repository->shouldReceive( 'can_toggle' )
			->once()
			->with( 15 )
			->andReturn( true );
		$repository->shouldReceive( 'find_by_id' )
			->once()
			->with( 15 )
			->andReturn( new STOLMC_Service_Tracker_Case_Dto( 15, 7, 'Case A', 'open' ) );
		$repository->shouldReceive( 'begin_transaction' )
			->once()
			->andReturn( true );
		$repository->shouldReceive( 'close_case' )
			->once()
			->with( 15 )
			->andReturn( false );
		$repository->shouldReceive( 'rollback_transaction' )
			->once()
			->andReturn( true );
		$repository->shouldReceive( 'commit_transaction' )->never();

		$service = new STOLMC_Service_Tracker_Toggle_Service( $repository );
		$result  = $service->toggle_case_status( new STOLMC_Service_Tracker_Toggle_Request_Dto( 15 ) );

		$this->assertFalse( $result->success );
		$this->assertSame( 'close_failed', $result->error_code );
	}

	public function test_toggle_case_status_rolls_back_when_commit_fails_and_skips_email(): void {
		Functions\expect( 'wp_mail' )->never();

		$repository = Mockery::mock( STOLMC_Service_Tracker_Toggle_Repository::class );
		$repository->shouldReceive( 'can_toggle' )
			->once()
			->with( 15 )
			->andReturn( true );
		$repository->shouldReceive( 'find_by_id' )
			->once()
			->with( 15 )
			->andReturn( new STOLMC_Service_Tracker_Case_Dto( 15, 7, 'Case A', 'open' ) );
		$repository->shouldReceive( 'begin_transaction' )
			->once()
			->andReturn( true );
		$repository->shouldReceive( 'close_case' )
			->once()
			->with( 15 )
			->andReturn( 1 );
		$repository->shouldReceive( 'commit_transaction' )
			->once()
			->andReturn( false );
		$repository->shouldReceive( 'rollback_transaction' )
			->once()
			->andReturn( true );

		$service = new STOLMC_Service_Tracker_Toggle_Service( $repository );
		$result  = $service->toggle_case_status( new STOLMC_Service_Tracker_Toggle_Request_Dto( 15 ) );

		$this->assertFalse( $result->success );
		$this->assertSame( 'transaction_commit_failed', $result->error_code );
	}
}

