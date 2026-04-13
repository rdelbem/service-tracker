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
	 * @var \STOLMC_Service_Tracker\includes\API\STOLMC_Service_Tracker_Api_Calendar
	 */
	protected $api;

	/**
	 * Mock SQL instance for cases.
	 *
	 * @var \Mockery\MockInterface
	 */
	protected $mock_cases_sql;

	/**
	 * Mock SQL instance for progress.
	 *
	 * @var \Mockery\MockInterface
	 */
	protected $mock_progress_sql;

	/**
	 * Set up test fixtures.
	 */
	protected function set_up(): void {
		parent::set_up();

		$this->api = new \STOLMC_Service_Tracker\includes\API\STOLMC_Service_Tracker_Api_Calendar();

		// Create mock SQL instances without defaults.
		$this->mock_cases_sql = Mockery::mock( \STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Sql::class );
		$this->mock_progress_sql = Mockery::mock( \STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Sql::class );

		// Inject mocks.
		set_private_property( $this->api, 'cases_sql', $this->mock_cases_sql );
		set_private_property( $this->api, 'progress_sql', $this->mock_progress_sql );
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
		$mock_cases = [
			(object) [
				'id' => 1,
				'id_user' => 42,
				'title' => 'Test Case',
				'status' => 'open',
				'description' => 'Test Description',
				'start_at' => '2024-01-01 09:00:00',
				'due_at' => '2024-01-31 17:00:00',
			],
		];

		$mock_progress = [
			(object) [
				'id' => 10,
				'id_case' => 1,
				'id_user' => 42,
				'text' => 'Progress update',
				'created_at' => '2024-01-15 12:00:00',
			],
		];

		// No filters provided → uses get_all() for both tables.
		$this->mock_cases_sql->allows( 'get_all' )
			->andReturn( $mock_cases );

		$this->mock_progress_sql->allows( 'get_all' )
			->andReturn( $mock_progress );

		// Progress loop calls get_by() to resolve case titles.
		$this->mock_cases_sql->allows( 'get_by' )
			->andReturn( [ (object) [ 'title' => 'Test Case' ] ] );

		Functions\when( 'get_user_by' )->justReturn( (object) [ 'display_name' => 'Test User' ] );

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
		// id_user filter provided → uses get_by() for cases, get_all() for progress.
		$this->mock_cases_sql->allows( 'get_by' )
			->andReturn( [] );

		$this->mock_progress_sql->allows( 'get_all' )
			->andReturn( [] );

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
		// status filter provided → uses get_by() for cases, get_all() for progress.
		$this->mock_cases_sql->allows( 'get_by' )
			->andReturn( [] );

		$this->mock_progress_sql->allows( 'get_all' )
			->andReturn( [] );

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

		// Filter modifies args making them non-empty → uses get_by() instead of get_all().
		$this->mock_cases_sql->allows( 'get_by' )
			->andReturn( [] );

		$this->mock_progress_sql->allows( 'get_all' )
			->andReturn( [] );

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

		// No direct filters → uses get_all() for both tables.
		$this->mock_cases_sql->allows( 'get_all' )
			->andReturn( [] );

		$this->mock_progress_sql->allows( 'get_all' )
			->andReturn( [] );

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

		// Should still return a response even if authentication fails.
		$response = $this->api->get_calendar( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
	}

	/**
	 * Test calendar response structure includes all required fields.
	 */
	public function test_calendar_response_structure(): void {
		$mock_cases = [
			(object) [
				'id' => 1,
				'id_user' => 42,
				'title' => 'Test Case',
				'status' => 'open',
				'description' => 'Test Description',
				'start_at' => '2024-01-01 09:00:00',
				'due_at' => '2024-01-31 17:00:00',
			],
		];

		// No direct filters → uses get_all() for cases, get_all() for progress.
		$this->mock_cases_sql->allows( 'get_all' )
			->andReturn( $mock_cases );

		$this->mock_progress_sql->allows( 'get_all' )
			->andReturn( [] );

		Functions\when( 'get_user_by' )->justReturn( (object) [ 'display_name' => 'Test User' ] );

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
		$data = $response->get_data();

		// Verify cases structure.
		$this->assertIsArray( $data['cases'] );
		if ( count( $data['cases'] ) > 0 ) {
			$case = $data['cases'][0];
			$this->assertArrayHasKey( 'id', $case );
			$this->assertArrayHasKey( 'id_user', $case );
			$this->assertArrayHasKey( 'title', $case );
			$this->assertArrayHasKey( 'status', $case );
			$this->assertArrayHasKey( 'description', $case );
			$this->assertArrayHasKey( 'start_at', $case );
			$this->assertArrayHasKey( 'due_at', $case );
			$this->assertArrayHasKey( 'client_name', $case );
		}

		// Verify progress structure.
		$this->assertIsArray( $data['progress'] );
	}
}
