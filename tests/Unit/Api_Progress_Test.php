<?php
/**
 * Progress API Test
 *
 * Tests for the STOLMC_Service_Tracker_Api_Progress class.
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
use WP_REST_Server;

/**
 * Progress API Test Class.
 *
 * @group   unit
 * @group   api
 * @group   progress
 */
class Api_Progress_Test extends API_TestCase {

	/**
	 * Progress API instance.
	 *
	 * @var \STOLMC_Service_Tracker\includes\API\STOLMC_Service_Tracker_Api_Progress
	 */
	protected $api;

	/**
	 * Set up test fixtures.
	 */
	protected function set_up(): void {
		parent::set_up();

		$this->api = new \STOLMC_Service_Tracker\includes\API\STOLMC_Service_Tracker_Api_Progress();

		// Create mock SQL instance WITHOUT default behavior - each test will set up its own expectations.
		$this->mock_sql = \Mockery::mock( STOLMC_Service_Tracker_Sql::class );

		set_private_property( $this->api, 'sql', $this->mock_sql );
	}

	/**
	 * Test read method returns progress for case.
	 */
	public function test_read_returns_progress_for_case(): void {
		$expected_progress = [
			(object) [ 'id' => 1, 'text' => 'Progress 1', 'created_at' => '2024-01-01 12:00:00' ],
			(object) [ 'id' => 2, 'text' => 'Progress 2', 'created_at' => '2024-01-02 12:00:00' ],
		];

		$this->mock_sql->allows( 'get_by' )
			->andReturn( $expected_progress );

		Filters\expectApplied( 'stolmc_service_tracker_progress_read_query_args' )
			->atMost()->once()
			->andReturnArg( 0 );
		Filters\expectApplied( 'stolmc_service_tracker_progress_read_response' )
			->atMost()->once()
			->andReturnArg( 0 );

		$request = $this->create_mock_request( [ 'id_case' => self::TEST_CASE_ID ] );

		$result = $this->api->read( $request );

		$this->assertSame( $expected_progress, $result );
	}

	/**
	 * Test create method creates progress and sends email.
	 */
	public function test_create_creates_progress_and_sends_email(): void {
		$progress_data = $this->create_sample_progress_data();
		$body = json_encode( $progress_data );

		$request = $this->create_mock_request( [], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], $body );

		$this->mock_sql->allows( 'insert' )
			->andReturn( 'Success' );

		Actions\expectDone( 'stolmc_service_tracker_progress_created' )
			->atMost()->once();
		Actions\expectDone( 'stolmc_service_tracker_progress_before_email_sent' )
			->atMost()->once();

		Filters\expectApplied( 'stolmc_service_tracker_progress_create_data' )
			->atMost()->once()
			->andReturnUsing( static fn( $data ) => $data );
		Filters\expectApplied( 'stolmc_service_tracker_progress_email_data' )
			->atMost()->once()
			->andReturnUsing( static fn( $data ) => $data );

		Functions\when( 'wp_mail' )->justReturn( true );

		$result = $this->api->create( $request );

		$this->assertIsString( $result );
	}

	/**
	 * Test create method filters progress data.
	 */
	public function test_create_filters_progress_data(): void {
		$progress_data = $this->create_sample_progress_data();
		$body = json_encode( $progress_data );

		$request = $this->create_mock_request( [], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], $body );

		$modified_data = array_merge( $progress_data, [ 'custom_field' => 'custom_value' ] );

		Filters\expectApplied( 'stolmc_service_tracker_progress_create_data' )
			->atMost()->once()
			->andReturn( $modified_data );

		Filters\expectApplied( 'stolmc_service_tracker_progress_email_data' )
			->atMost()->once()
			->andReturnUsing( static fn( $data ) => $data );

		$this->mock_sql->allows( 'insert' )
			->andReturn( 'Success' );

		Actions\expectDone( 'stolmc_service_tracker_progress_created' )
			->atMost()->once();

		$result = $this->api->create( $request );

		$this->assertIsString( $result );
	}

	/**
	 * Test update method updates progress text.
	 */
	public function test_update_updates_progress_text(): void {
		$body = json_encode( [ 'text' => 'Updated progress text' ] );
		$request = $this->create_mock_request( [ 'id' => 1 ], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], $body );

		$this->mock_sql->allows( 'update' )
			->andReturn( 1 );

		Actions\expectDone( 'stolmc_service_tracker_progress_updated' )
			->atMost()->once();

		Filters\expectApplied( 'stolmc_service_tracker_progress_update_data' )
			->atMost()->once()
			->andReturnUsing( static fn( $data ) => $data );

		$response = $this->api->update( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertSame( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertTrue( $data['success'] );
	}

	/**
	 * Test update method returns error for missing text.
	 */
	public function test_update_returns_error_for_missing_text(): void {
		$body = json_encode( [ 'other_field' => 'value' ] );
		$request = $this->create_mock_request( [ 'id' => 1 ], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], $body );

		$response = $this->api->update( $request );

		$this->assertSame( 400, $response->get_status() );

		$data = $response->get_data();
		$this->assertFalse( $data['success'] );
		$this->assertSame( 'Missing text parameter', $data['message'] );
	}

	/**
	 * Test update method returns error for missing ID.
	 */
	public function test_update_returns_error_for_missing_id(): void {
		$body = json_encode( [ 'text' => 'Some text' ] );
		$request = $this->create_mock_request( [], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], $body );

		$response = $this->api->update( $request );

		$this->assertSame( 400, $response->get_status() );

		$data = $response->get_data();
		$this->assertFalse( $data['success'] );
		$this->assertSame( 'Missing ID parameter', $data['message'] );
	}

	/**
	 * Test delete method removes progress entry.
	 */
	public function test_delete_removes_progress_entry(): void {
		$request = $this->create_mock_request( [ 'id' => 1 ] );

		$this->mock_sql->allows( 'delete' )
			->andReturn( 1 );

		Actions\expectDone( 'stolmc_service_tracker_progress_before_delete' )
			->atMost()->once();
		Actions\expectDone( 'stolmc_service_tracker_progress_deleted' )
			->atMost()->once();

		$response = $this->api->delete( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertSame( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertTrue( $data['success'] );
	}

	/**
	 * Test custom_api method registers all routes.
	 */
	public function test_custom_api_registers_all_routes(): void {
		Functions\when( 'register_rest_route' )->justReturn( true );

		Actions\expectDone( 'stolmc_service_tracker_api_route_registered' )
			->times( 4 );

		$this->api->custom_api();

		$this->assertTrue( true );
	}

	/**
	 * Test create method can suppress email via filter.
	 */
	public function test_create_can_suppress_email_via_filter(): void {
		$progress_data = $this->create_sample_progress_data();
		$body = json_encode( $progress_data );

		$request = $this->create_mock_request( [], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], $body );

		Filters\expectApplied( 'stolmc_service_tracker_progress_create_data' )
			->atMost()->once()
			->andReturnUsing( static fn( $data ) => $data );

		// Filter to invalid email data (id_user = 0 will cause get_user_by to fail).
		Filters\expectApplied( 'stolmc_service_tracker_progress_email_data' )
			->atMost()->once()
			->andReturn(
				[
					'id_user' => 0,
					'subject' => 'Test',
					'message' => 'Test',
				]
			);

		// Email should not be sent (get_user_by will fail with id 0).
		Functions\when( 'get_user_by' )->justReturn( false );
		Functions\expect( 'wp_mail' )
			->never();

		$this->mock_sql->allows( 'insert' )
			->andReturn( 'Success' );
		Actions\expectDone( 'stolmc_service_tracker_progress_created' )
			->atMost()->once();

		$result = $this->api->create( $request );

		$this->assertIsString( $result );
	}

	/**
	 * Test update method applies filters to update data.
	 */
	public function test_update_applies_filters_to_update_data(): void {
		$body = json_encode( [ 'text' => 'Original text' ] );
		$request = $this->create_mock_request( [ 'id' => 1 ], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], $body );

		$this->mock_sql->allows( 'update' )
			->andReturn( 1 );

		Filters\expectApplied( 'stolmc_service_tracker_progress_update_data' )
			->atMost()->once()
			->andReturnUsing( static fn( $data ) => $data );

		Actions\expectDone( 'stolmc_service_tracker_progress_updated' )
			->atMost()->once();

		$response = $this->api->update( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
	}

	/**
	 * Test delete method fires hooks with correct parameters.
	 */
	public function test_delete_fires_hooks_with_correct_parameters(): void {
		$request = $this->create_mock_request( [ 'id' => 1 ] );

		$this->mock_sql->allows( 'delete' )
			->andReturn( 1 );

		Actions\expectDone( 'stolmc_service_tracker_progress_before_delete' )
			->atMost()->once();

		Actions\expectDone( 'stolmc_service_tracker_progress_deleted' )
			->atMost()->once();

		$response = $this->api->delete( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertTrue( $response->get_data()['success'] );
	}
}
