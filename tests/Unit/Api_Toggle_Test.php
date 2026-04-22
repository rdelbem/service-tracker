<?php
/**
 * Toggle API Test
 *
 * Tests for the STOLMC_Service_Tracker_Api_Toggle class.
 *
 * @package Service_Tracker
 */

namespace STOLMC_Service_Tracker\Tests\Unit;

use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Mockery;
use WP_REST_Request;
use STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Toggle_Repository;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Case_Dto;

/**
 * Toggle API Test Class.
 *
 * @group   unit
 * @group   api
 * @group   toggle
 */
class Api_Toggle_Test extends API_TestCase {

	/**
	 * Toggle API instance.
	 *
	 * @var \STOLMC_Service_Tracker\includes\Controller_API\STOLMC_Service_Tracker_Api_Toggle
	 */
	protected $api;

	/**
	 * Mock Toggle Repository.
	 *
	 * @var \Mockery\MockInterface
	 */
	protected $mock_toggle_repository;

	/**
	 * Set up test fixtures.
	 */
	protected function set_up(): void {
		parent::set_up();

		$this->api = new \STOLMC_Service_Tracker\includes\Controller_API\STOLMC_Service_Tracker_Api_Toggle();

		// Create mock Toggle Repository instance WITHOUT default behavior - each test will set up its own expectations.
		$this->mock_toggle_repository = \Mockery::mock( STOLMC_Service_Tracker_Toggle_Repository::class );

		set_private_property( $this->api, 'toggle_repository', $this->mock_toggle_repository );
	}

	/**
	 * Test toggle_status closes an open case.
	 */
	public function test_toggle_status_closes_open_case(): void {
		$case = new STOLMC_Service_Tracker_Case_Dto(
			self::TEST_CASE_ID,
			self::TEST_USER_ID,
			'Test Case',
			'open'
		);
		$request = $this->create_mock_request( [ 'id' => self::TEST_CASE_ID ] );

		$this->mock_toggle_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( self::TEST_CASE_ID )
			->andReturn( $case );

		$this->mock_toggle_repository->shouldReceive( 'close_case' )
			->once()
			->with( self::TEST_CASE_ID )
			->andReturn( 1 );

		Filters\expectApplied( 'stolmc_service_tracker_toggle_case_data' )
			->atMost()->once()
			->andReturnUsing( static fn( $data ) => $data );

		Actions\expectDone( 'stolmc_service_tracker_case_before_closing' )
			->atMost()->once();
		Actions\expectDone( 'stolmc_service_tracker_case_closed' )
			->atMost()->once();

		Filters\expectApplied( 'stolmc_service_tracker_closed_status_messages' )
			->atMost()->once()
			->andReturn( [ 'Case closed', 'is now closed' ] );
		Filters\expectApplied( 'stolmc_service_tracker_toggle_email_data' )
			->atMost()->once()
			->andReturnUsing( static fn( $data ) => $data );

		Functions\when( 'wp_mail' )->justReturn( true );

		$result = $this->api->toggle_status( $request );

		$this->assertSame( 1, $result );
	}

	/**
	 * Test toggle_status reopens a closed case.
	 */
	public function test_toggle_status_reopens_closed_case(): void {
		$case = new STOLMC_Service_Tracker_Case_Dto(
			self::TEST_CASE_ID,
			self::TEST_USER_ID,
			'Test Case',
			'close'
		);
		$request = $this->create_mock_request( [ 'id' => self::TEST_CASE_ID ] );

		$this->mock_toggle_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( self::TEST_CASE_ID )
			->andReturn( $case );

		$this->mock_toggle_repository->shouldReceive( 'open_case' )
			->once()
			->with( self::TEST_CASE_ID )
			->andReturn( 1 );

		Filters\expectApplied( 'stolmc_service_tracker_toggle_case_data' )
			->atMost()->once()
			->andReturnUsing( static fn( $data ) => $data );

		Actions\expectDone( 'stolmc_service_tracker_case_before_reopening' )
			->atMost()->once();
		Actions\expectDone( 'stolmc_service_tracker_case_reopened' )
			->atMost()->once();

		Filters\expectApplied( 'stolmc_service_tracker_opened_status_messages' )
			->atMost()->once()
			->andReturn( [ 'Case opened', 'is now open' ] );
		Filters\expectApplied( 'stolmc_service_tracker_toggle_email_data' )
			->atMost()->once()
			->andReturnUsing( static fn( $data ) => $data );

		Functions\when( 'wp_mail' )->justReturn( true );

		$result = $this->api->toggle_status( $request );

		$this->assertSame( 1, $result );
	}

	/**
	 * Test toggle_status returns false for non-existent case.
	 */
	public function test_toggle_status_returns_false_for_non_existent_case(): void {
		$request = $this->create_mock_request( [ 'id' => self::TEST_CASE_ID ] );

		$this->mock_toggle_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( self::TEST_CASE_ID )
			->andReturn( null );

		$result = $this->api->toggle_status( $request );

		$this->assertFalse( $result );
	}

	/**
	 * Test toggle_status returns null for invalid status.
	 */
	public function test_toggle_status_returns_null_for_invalid_status(): void {
		$case = new STOLMC_Service_Tracker_Case_Dto(
			self::TEST_CASE_ID,
			self::TEST_USER_ID,
			'Test Case',
			'invalid'
		);
		$request = $this->create_mock_request( [ 'id' => self::TEST_CASE_ID ] );

		$this->mock_toggle_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( self::TEST_CASE_ID )
			->andReturn( $case );

		Filters\expectApplied( 'stolmc_service_tracker_toggle_case_data' )
			->once()
			->with( (array) $case, self::TEST_CASE_ID )
			->andReturnUsing( static fn( $data ) => $data );

		$result = $this->api->toggle_status( $request );

		$this->assertNull( $result );
	}

	/**
	 * Test toggle_case_data filter can modify case data.
	 */
	public function test_toggle_case_data_filter_can_modify_case_data(): void {
		$case = new STOLMC_Service_Tracker_Case_Dto(
			self::TEST_CASE_ID,
			self::TEST_USER_ID,
			'Test Case',
			'open'
		);
		$request = $this->create_mock_request( [ 'id' => self::TEST_CASE_ID ] );

		$this->mock_toggle_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( self::TEST_CASE_ID )
			->andReturn( $case );

		$modified_case = array_merge( (array) $case, [ 'status' => 'close' ] );

		Filters\expectApplied( 'stolmc_service_tracker_toggle_case_data' )
			->once()
			->andReturn( $modified_case );

		// Since filter changes status to 'close', API will try to reopen it.
		$this->mock_toggle_repository->shouldReceive( 'open_case' )
			->once()
			->with( self::TEST_CASE_ID )
			->andReturn( 1 );

		Actions\expectDone( 'stolmc_service_tracker_case_before_reopening' )
			->atMost()->once();
		Actions\expectDone( 'stolmc_service_tracker_case_reopened' )
			->atMost()->once();

		Filters\expectApplied( 'stolmc_service_tracker_opened_status_messages' )
			->atMost()->once()
			->andReturn( [ 'Closed', 'closed' ] );
		Filters\expectApplied( 'stolmc_service_tracker_toggle_email_data' )
			->atMost()->once()
			->andReturnUsing( static fn( $data ) => $data );

		Functions\when( 'wp_mail' )->justReturn( true );

		$result = $this->api->toggle_status( $request );

		$this->assertNotNull( $result );
	}

	/**
	 * Test closed status messages can be filtered.
	 */
	public function test_closed_status_messages_can_be_filtered(): void {
		$case = new STOLMC_Service_Tracker_Case_Dto(
			self::TEST_CASE_ID,
			self::TEST_USER_ID,
			'Test Case',
			'open'
		);
		$request = $this->create_mock_request( [ 'id' => self::TEST_CASE_ID ] );

		$this->mock_toggle_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( self::TEST_CASE_ID )
			->andReturn( $case );

		$this->mock_toggle_repository->shouldReceive( 'close_case' )
			->once()
			->with( self::TEST_CASE_ID )
			->andReturn( 1 );

		Filters\expectApplied( 'stolmc_service_tracker_toggle_case_data' )
			->once()
			->with( (array) $case, self::TEST_CASE_ID )
			->andReturnUsing( static fn( $data ) => $data );

		Actions\expectDone( 'stolmc_service_tracker_case_before_closing' )
			->once()
			->with( self::TEST_CASE_ID, Mockery::type( 'array' ), Mockery::type( WP_REST_Request::class ) );
		Actions\expectDone( 'stolmc_service_tracker_case_closed' )
			->once()
			->with( 1, self::TEST_USER_ID, 'Test Case', Mockery::type( WP_REST_Request::class ) );

		$custom_messages = [ 'Custom closed message', 'Custom is closed' ];
		Filters\expectApplied( 'stolmc_service_tracker_closed_status_messages' )
			->once()
			->andReturn( $custom_messages );
		Filters\expectApplied( 'stolmc_service_tracker_toggle_email_data' )
			->once()
			->andReturnUsing(
				static function ( $data ) use ( $custom_messages ) {
					self::assertSame( $custom_messages[0], $data['subject'] );
					return $data;
				}
			);

		Functions\when( 'wp_mail' )->justReturn( true );

		$this->api->toggle_status( $request );
	}

	/**
	 * Test opened status messages can be filtered.
	 */
	public function test_opened_status_messages_can_be_filtered(): void {
		$case = new STOLMC_Service_Tracker_Case_Dto(
			self::TEST_CASE_ID,
			self::TEST_USER_ID,
			'Test Case',
			'close'
		);
		$request = $this->create_mock_request( [ 'id' => self::TEST_CASE_ID ] );

		$this->mock_toggle_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( self::TEST_CASE_ID )
			->andReturn( $case );

		$this->mock_toggle_repository->shouldReceive( 'open_case' )
			->once()
			->with( self::TEST_CASE_ID )
			->andReturn( 1 );

		Filters\expectApplied( 'stolmc_service_tracker_toggle_case_data' )
			->once()
			->with( (array) $case, self::TEST_CASE_ID )
			->andReturnUsing( static fn( $data ) => $data );

		Actions\expectDone( 'stolmc_service_tracker_case_before_reopening' )
			->once()
			->with( self::TEST_CASE_ID, Mockery::type( 'array' ), Mockery::type( WP_REST_Request::class ) );
		Actions\expectDone( 'stolmc_service_tracker_case_reopened' )
			->once()
			->with( 1, self::TEST_USER_ID, 'Test Case', Mockery::type( WP_REST_Request::class ) );

		$custom_messages = [ 'Custom opened message', 'Custom is opened' ];
		Filters\expectApplied( 'stolmc_service_tracker_opened_status_messages' )
			->once()
			->andReturn( $custom_messages );
		Filters\expectApplied( 'stolmc_service_tracker_toggle_email_data' )
			->once()
			->andReturnUsing(
				static function ( $data ) use ( $custom_messages ) {
					self::assertSame( $custom_messages[0], $data['subject'] );
					return $data;
				}
			);

		Functions\when( 'wp_mail' )->justReturn( true );

		$this->api->toggle_status( $request );
	}

	/**
	 * Test custom_api registers toggle route.
	 */
	public function test_custom_api_registers_toggle_route(): void {
		Functions\when( 'register_rest_route' )->justReturn( true );

		Actions\expectDone( 'stolmc_service_tracker_api_route_registered' )
			->once();

		$this->api->custom_api();

		$this->assertTrue( true );
	}
}
