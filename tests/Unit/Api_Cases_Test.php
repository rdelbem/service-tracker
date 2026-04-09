<?php
/**
 * Cases API Test
 *
 * Tests for the STOLMC_Service_Tracker_Api_Cases class.
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
 * Cases API Test Class.
 *
 * @group   unit
 * @group   api
 * @group   cases
 */
class Api_Cases_Test extends API_TestCase {

	/**
	 * Cases API instance.
	 *
	 * @var \STOLMC_Service_Tracker\includes\API\STOLMC_Service_Tracker_Api_Cases
	 */
	protected $api;

	/**
	 * Set up test fixtures.
	 */
	protected function set_up(): void {
		parent::set_up();

		$this->api = new \STOLMC_Service_Tracker\includes\API\STOLMC_Service_Tracker_Api_Cases();

		// Inject mock SQL instances using helper function.
		$this->mock_sql = $this->create_mock_sql();
		$this->mock_progress_sql = $this->create_mock_sql();

		set_private_property( $this->api, 'sql', $this->mock_sql );
		set_private_property( $this->api, 'progress_sql', $this->mock_progress_sql );
	}

	/**
	 * Test read method returns cases for user.
	 */
	public function test_read_returns_cases_for_user(): void {
		$expected_cases = [
			$this->create_sample_case_object(),
			$this->create_sample_case_object( [ 'id' => 101 ] ),
		];

		$this->mock_sql->allows( 'get_by' )
			->andReturn( $expected_cases );

		Filters\expectApplied( 'stolmc_service_tracker_cases_read_query_args' )
			->once()
			->withAnyArgs()
			->andReturnUsing( static fn( ...$args ) => $args[0] );
		Filters\expectApplied( 'stolmc_service_tracker_cases_read_response' )
			->once()
			->withAnyArgs()
			->andReturn( $expected_cases );

		$request = $this->create_mock_request( [ 'id_user' => self::TEST_USER_ID ] );

		$result = $this->api->read( $request );

		$this->assertSame( $expected_cases, $result );
	}

	/**
	 * Test create method creates case successfully.
	 */
	public function test_create_creates_case_successfully(): void {
		$case_data = $this->create_sample_case_data();
		$body = json_encode( $case_data );

		$request = $this->create_mock_request( [], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], $body );

		$this->mock_sql->allows( 'insert' )
			->andReturn( 'Success, data was inserted' );

		$this->mock_wpdb->insert_id = self::TEST_CASE_ID;

		Actions\expectDone( 'stolmc_service_tracker_case_created' )
			->once()
			->with( self::TEST_CASE_ID, \Mockery::type( 'array' ), \Mockery::type( WP_REST_Request::class ) );

		Filters\expectApplied( 'stolmc_service_tracker_case_create_data' )
			->once()
			->withAnyArgs()
			->andReturnUsing( static fn( ...$args ) => $args[0] );

		$response = $this->api->create( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertSame( 201, $response->get_status() );

		$data = $response->get_data();
		$this->assertTrue( $data['success'] );
		$this->assertSame( self::TEST_CASE_ID, $data['id'] );
	}

	/**
	 * Test create method returns error on failure.
	 */
	public function test_create_returns_error_on_failure(): void {
		$case_data = $this->create_sample_case_data();
		$body = json_encode( $case_data );

		$request = $this->create_mock_request( [], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], $body );

		$this->mock_wpdb->insert_id = 0;

		$this->mock_sql->allows( 'insert' )
			->andReturn( 'Error: Insert failed' );

		Filters\expectApplied( 'stolmc_service_tracker_case_create_data' )
			->once()
			->withAnyArgs()
			->andReturnUsing( static fn( ...$args ) => $args[0] );

		$response = $this->api->create( $request );

		$this->assertSame( 500, $response->get_status() );

		$data = $response->get_data();
		$this->assertFalse( $data['success'] );
		$this->assertSame( 1, Actions\did( 'stolmc_service_tracker_case_create_failed' ) );
	}

	/**
	 * Test update method updates case title.
	 */
	public function test_update_updates_case_title(): void {
		$body = json_encode( [ 'title' => 'Updated Title' ] );
		$request = $this->create_mock_request( [ 'id' => self::TEST_CASE_ID ], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], $body );

		$this->mock_sql->allows( 'update' )
			->andReturn( 1 );

		Actions\expectDone( 'stolmc_service_tracker_case_updated' )
			->once()
			->with(
				1,
				[ 'title' => 'Updated Title' ],
				[ 'id' => self::TEST_CASE_ID ],
				\Mockery::type( WP_REST_Request::class )
			);

		Filters\expectApplied( 'stolmc_service_tracker_case_update_data' )
			->once()
			->withAnyArgs()
			->andReturnUsing( static fn( ...$args ) => $args[0] );

		$result = $this->api->update( $request );

		$this->assertSame( 1, $result );
	}

	/**
	 * Test delete method removes case and progress.
	 */
	public function test_delete_removes_case_and_progress(): void {
		$request = $this->create_mock_request( [ 'id' => self::TEST_CASE_ID ] );

		$this->mock_sql->allows( 'delete' )
			->andReturn( 1 );
		$this->mock_progress_sql->allows( 'delete' )
			->andReturn( 1 );

		Actions\expectDone( 'stolmc_service_tracker_case_before_delete' )
			->once()
			->with( self::TEST_CASE_ID, \Mockery::type( WP_REST_Request::class ) );
		Actions\expectDone( 'stolmc_service_tracker_case_deleted' )
			->once()
			->with(
				1,
				1,
				self::TEST_CASE_ID,
				\Mockery::type( WP_REST_Request::class )
			);

		$result = $this->api->delete( $request );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'case_delete', $result );
		$this->assertArrayHasKey( 'progress_delete', $result );
	}

	/**
	 * Test custom_api method registers all routes.
	 */
	public function test_custom_api_registers_all_routes(): void {
		$registered_count = 0;
		Functions\when( 'register_rest_route' )->alias(
			static function () use ( &$registered_count ) {
				$registered_count++;
				return true;
			}
		);
		Functions\when( 'do_action' )->justReturn( null );

		$this->api->custom_api();

		// Should register 4 routes: read, update, delete, create.
		$this->assertSame( 4, $registered_count );
	}

	/**
	 * Test create method handles missing optional fields.
	 */
	public function test_create_handles_missing_optional_fields(): void {
		$case_data = [ 'id_user' => self::TEST_USER_ID, 'title' => 'Test Case' ];
		$body = json_encode( $case_data );

		$request = $this->create_mock_request( [], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], $body );

		$this->mock_sql->allows( 'insert' )
			->andReturn( 'Success, data was inserted' );

		$this->mock_wpdb->insert_id = self::TEST_CASE_ID;

		Filters\expectApplied( 'stolmc_service_tracker_case_create_data' )
			->once()
			->withAnyArgs()
			->andReturnUsing(
				static function ( ...$args ) {
					$data = $args[0];
					// Verify defaults are set.
					self::assertSame( 'open', $data['status'] );
					self::assertSame( '', $data['description'] );
					return $data;
				}
			);

		Actions\expectDone( 'stolmc_service_tracker_case_created' )
			->once()
			->with( self::TEST_CASE_ID, \Mockery::type( 'array' ), \Mockery::type( WP_REST_Request::class ) );

		$response = $this->api->create( $request );

		$this->assertSame( 201, $response->get_status() );
	}

	/**
	 * Test read method applies filters to query args.
	 */
	public function test_read_applies_filters_to_query_args(): void {
		$modified_args = null;
		Filters\expectApplied( 'stolmc_service_tracker_cases_read_query_args' )
			->once()
			->withAnyArgs()
			->andReturnUsing(
				static function ( ...$args ) use ( &$modified_args ) {
					$modified_args = $args[0];
					return [ 'id_user' => 999 ];
				}
			);

		$this->mock_sql->allows( 'get_by' )
			->andReturnUsing(
				static function ( $args ) {
					self::assertSame( [ 'id_user' => 999 ], $args );
					return [];
				}
			);

		Filters\expectApplied( 'stolmc_service_tracker_cases_read_response' )
			->once()
			->withAnyArgs()
			->andReturnUsing( static fn( ...$args ) => $args[0] );

		$request = $this->create_mock_request( [ 'id_user' => self::TEST_USER_ID ] );

		$this->api->read( $request );

		$this->assertNotNull( $modified_args );
		$this->assertSame( self::TEST_USER_ID, $modified_args['id_user'] );
	}

	/**
	 * Test update method applies filters to update data.
	 */
	public function test_update_applies_filters_to_update_data(): void {
		$body = json_encode( [ 'title' => 'Original Title' ] );
		$request = $this->create_mock_request( [ 'id' => self::TEST_CASE_ID ], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], $body );

		$modified_data = null;
		Filters\expectApplied( 'stolmc_service_tracker_case_update_data' )
			->once()
			->withAnyArgs()
			->andReturnUsing(
				static function ( ...$args ) use ( &$modified_data ) {
					$modified_data = $args[0];
					return [ 'title' => 'Modified Title' ];
				}
			);

		$this->mock_sql->allows( 'update' )
			->andReturnUsing(
				static function ( $data, $condition ) {
					self::assertSame( [ 'title' => 'Modified Title' ], $data );
					self::assertSame( [ 'id' => self::TEST_CASE_ID ], $condition );
					return 1;
				}
			);

		Actions\expectDone( 'stolmc_service_tracker_case_updated' )
			->once()
			->with(
				1,
				[ 'title' => 'Modified Title' ],
				[ 'id' => self::TEST_CASE_ID ],
				\Mockery::type( WP_REST_Request::class )
			);

		$this->api->update( $request );

		$this->assertNotNull( $modified_data );
		$this->assertSame( 'Original Title', $modified_data['title'] );
	}

	/**
	 * Test delete method fires hooks with correct parameters.
	 */
	public function test_delete_fires_hooks_with_correct_parameters(): void {
		$request = $this->create_mock_request( [ 'id' => self::TEST_CASE_ID ] );

		$this->mock_sql->allows( 'delete' )
			->andReturn( 1 );
		$this->mock_progress_sql->allows( 'delete' )
			->andReturn( 1 );

		Actions\expectDone( 'stolmc_service_tracker_case_before_delete' )
			->once()
			->with( self::TEST_CASE_ID, \Mockery::type( WP_REST_Request::class ) );

		$this->api->delete( $request );

		$this->assertSame( 1, Actions\did( 'stolmc_service_tracker_case_before_delete' ) );
	}
}
