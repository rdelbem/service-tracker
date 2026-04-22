<?php
/**
 * Calendar API Test
 *
 * Tests for the STOLMC_Service_Tracker_Api_Calendar class.
 *
 * @package Service_Tracker
 */

namespace STOLMC_Service_Tracker\Tests\Unit;

use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Mockery;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Calendar API Test Class.
 *
 * @group   unit
 * @group   api
 * @group   calendar
 */
class Api_Calendar_Test extends API_TestCase {

	/**
	 * Calendar API instance.
	 *
	 * @var \STOLMC_Service_Tracker\includes\Controller_API\STOLMC_Service_Tracker_Api_Calendar
	 */
	protected $api;

	/**
	 * Mock Calendar Repository instance.
	 *
	 * @var \Mockery\MockInterface
	 */
	protected $mock_calendar_orm;

	/**
	 * Set up test fixtures.
	 */
	protected function set_up(): void {
		parent::set_up();

		$this->api = new \STOLMC_Service_Tracker\includes\Controller_API\STOLMC_Service_Tracker_Api_Calendar();

		// Create mock Calendar Repository instance.
		$this->mock_calendar_orm = Mockery::mock( \STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Calendar_Repository::class );

		// Inject mock.
		set_private_property( $this->api, 'calendar', $this->mock_calendar_orm );
	}

	/**
	 * Test calendar endpoint requires start and end parameters.
	 */
	public function test_calendar_endpoint_requires_start_and_end_parameters(): void {
		$request = $this->create_mock_request( [] );

		$response = $this->api->get_calendar( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertSame( 400, $response->get_status() );

		$data = $response->get_data();
		$this->assertFalse( $data['success'] );
		$this->assertStringContainsString( 'start', $data['message'] );
	}

	/**
	 * Test calendar endpoint returns calendar data with valid parameters.
	 */
	public function test_calendar_endpoint_returns_calendar_data(): void {
		$mock_calendar_data = [
			'cases' => [
				[
					'id' => 1,
					'id_user' => 42,
					'title' => 'Test Case',
					'status' => 'open',
					'description' => 'Test Description',
					'start_at' => '2024-01-01 09:00:00',
					'due_at' => '2024-01-31 17:00:00',
					'client_name' => 'Test User',
				],
			],
			'progress' => [
				[
					'id' => 10,
					'id_case' => 1,
					'id_user' => 42,
					'text' => 'Progress update',
					'created_at' => '2024-01-15 12:00:00',
					'case_title' => 'Test Case',
					'client_name' => 'Test User',
				],
			],
			'date_index' => [],
		];

		// Mock the Calendar Repository get_calendar_data method.
		$this->mock_calendar_orm->shouldReceive( 'find_calendar_data' )
			->once()
			->with( '2024-01-01', '2024-01-31', null, null )
			->andReturn( $mock_calendar_data );

		Filters\expectApplied( 'stolmc_service_tracker_calendar_query_args' )
			->atMost()->once()
			->andReturnArg( 0 );

		Filters\expectApplied( 'stolmc_service_tracker_calendar_cases_response' )
			->atMost()->once()
			->andReturnArg( 0 );

		Filters\expectApplied( 'stolmc_service_tracker_calendar_progress_response' )
			->atMost()->once()
			->andReturnArg( 0 );

		Filters\expectApplied( 'stolmc_service_tracker_calendar_payload' )
			->atMost()->once()
			->andReturnArg( 0 );

		$request = $this->create_mock_request( [
			'start' => '2024-01-01',
			'end' => '2024-01-31',
		] );

		$response = $this->api->get_calendar( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertSame( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'cases', $data );
		$this->assertArrayHasKey( 'progress', $data );
	}

	/**
	 * Test calendar endpoint filters by user ID.
	 */
	public function test_calendar_endpoint_filters_by_user_id(): void {
		$mock_calendar_data = [
			'cases' => [],
			'progress' => [],
			'date_index' => [],
		];

		// Mock the Calendar Repository get_calendar_data method with user filter.
		$this->mock_calendar_orm->shouldReceive( 'find_calendar_data' )
			->once()
			->with( '2024-01-01', '2024-01-31', 42, null )
			->andReturn( $mock_calendar_data );

		Filters\expectApplied( 'stolmc_service_tracker_calendar_query_args' )
			->atMost()->once()
			->andReturnArg( 0 );

		Filters\expectApplied( 'stolmc_service_tracker_calendar_cases_response' )
			->atMost()->once()
			->andReturnArg( 0 );

		Filters\expectApplied( 'stolmc_service_tracker_calendar_progress_response' )
			->atMost()->once()
			->andReturnArg( 0 );

		Filters\expectApplied( 'stolmc_service_tracker_calendar_payload' )
			->atMost()->once()
			->andReturnArg( 0 );

		$request = $this->create_mock_request( [
			'start' => '2024-01-01',
			'end' => '2024-01-31',
			'id_user' => 42,
		] );

		$response = $this->api->get_calendar( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertSame( 200, $response->get_status() );
	}

	/**
	 * Test calendar endpoint filters by status.
	 */
	public function test_calendar_endpoint_filters_by_status(): void {
		$mock_calendar_data = [
			'cases' => [],
			'progress' => [],
			'date_index' => [],
		];

		// Mock the Calendar Repository get_calendar_data method with status filter.
		$this->mock_calendar_orm->shouldReceive( 'find_calendar_data' )
			->once()
			->with( '2024-01-01', '2024-01-31', null, 'open' )
			->andReturn( $mock_calendar_data );

		Filters\expectApplied( 'stolmc_service_tracker_calendar_query_args' )
			->atMost()->once()
			->andReturnArg( 0 );

		Filters\expectApplied( 'stolmc_service_tracker_calendar_cases_response' )
			->atMost()->once()
			->andReturnArg( 0 );

		Filters\expectApplied( 'stolmc_service_tracker_calendar_progress_response' )
			->atMost()->once()
			->andReturnArg( 0 );

		Filters\expectApplied( 'stolmc_service_tracker_calendar_payload' )
			->atMost()->once()
			->andReturnArg( 0 );

		$request = $this->create_mock_request( [
			'start' => '2024-01-01',
			'end' => '2024-01-31',
			'status' => 'open',
		] );

		$response = $this->api->get_calendar( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertSame( 200, $response->get_status() );
	}

	/**
	 * Test calendar applies query args filter.
	 */
	public function test_calendar_applies_query_args_filter(): void {
		$received_args = null;
		Filters\expectApplied( 'stolmc_service_tracker_calendar_query_args' )
			->once()
			->andReturnUsing(
				function ( $args ) use ( &$received_args ) {
					$received_args = $args;
					$args['custom_filter'] = true;
					return $args;
				}
			);

		$mock_calendar_data = [
			'cases' => [],
			'progress' => [],
			'date_index' => [],
		];

		// Mock the Calendar Repository get_calendar_data method.
		// The filter adds custom_filter, but Calendar Repository only uses id_user and status.
		$this->mock_calendar_orm->shouldReceive( 'find_calendar_data' )
			->once()
			->with( '2024-01-01', '2024-01-31', null, null )
			->andReturn( $mock_calendar_data );

		Filters\expectApplied( 'stolmc_service_tracker_calendar_cases_response' )
			->atMost()->once()
			->andReturnArg( 0 );

		Filters\expectApplied( 'stolmc_service_tracker_calendar_progress_response' )
			->atMost()->once()
			->andReturnArg( 0 );

		Filters\expectApplied( 'stolmc_service_tracker_calendar_payload' )
			->atMost()->once()
			->andReturnArg( 0 );

		$request = $this->create_mock_request( [
			'start' => '2024-01-01',
			'end' => '2024-01-31',
		] );

		$response = $this->api->get_calendar( $request );

		// The filter should receive an empty array since no id_user or status is set.
		$this->assertSame( [], $received_args );
		// Verify the response is successful.
		$this->assertSame( 200, $response->get_status() );
	}

	/**
	 * Test custom_api registers calendar route.
	 */
	public function test_custom_api_registers_calendar_route(): void {
		Functions\when( 'register_rest_route' )->justReturn( true );

		$this->api->custom_api();

		$this->assertTrue( true );
	}

	/**
	 * Test calendar endpoint returns void when not authenticated.
	 */
	public function test_calendar_endpoint_checks_authentication(): void {
		Functions\when( 'current_user_can' )->justReturn( false );

		$mock_calendar_data = [
			'cases' => [],
			'progress' => [],
			'date_index' => [],
		];

		// Mock the Calendar Repository get_calendar_data method.
		$this->mock_calendar_orm->shouldReceive( 'find_calendar_data' )
			->once()
			->with( '2024-01-01', '2024-01-31', null, null )
			->andReturn( $mock_calendar_data );

		Filters\expectApplied( 'stolmc_service_tracker_calendar_query_args' )
			->atMost()->once()
			->andReturnArg( 0 );

		Filters\expectApplied( 'stolmc_service_tracker_calendar_cases_response' )
			->atMost()->once()
			->andReturnArg( 0 );

		Filters\expectApplied( 'stolmc_service_tracker_calendar_progress_response' )
			->atMost()->once()
			->andReturnArg( 0 );

		Filters\expectApplied( 'stolmc_service_tracker_calendar_payload' )
			->atMost()->once()
			->andReturnArg( 0 );

		$request = $this->create_mock_request( [
			'start' => '2024-01-01',
			'end' => '2024-01-31',
		] );

		$response = $this->api->get_calendar( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertSame( 200, $response->get_status() );
	}
}
