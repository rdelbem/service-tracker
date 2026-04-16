<?php
/**
 * Users API Test
 *
 * Tests for the STOLMC_Service_Tracker_Api_Users class.
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
 * Users API Test Class.
 *
 * @group   unit
 * @group   api
 * @group   users
 */
class Api_Users_Test extends API_TestCase {

	/**
	 * Users API instance.
	 *
	 * @var \STOLMC_Service_Tracker\includes\API\STOLMC_Service_Tracker_Api_Users
	 */
	protected $api;

	/**
	 * Set up test fixtures.
	 */
	protected function set_up(): void {
		parent::set_up();

		$this->api = new \STOLMC_Service_Tracker\includes\API\STOLMC_Service_Tracker_Api_Users();
	}

	/**
	 * Test get_users returns customer users list.
	 */
	public function test_get_users_returns_customer_users_list(): void {
		$mock_users = [
			(object) [
				'ID' => 1,
				'display_name' => 'User One',
				'user_email' => 'user1@example.com',
				'user_registered' => '2024-01-01 12:00:00',
			],
			(object) [
				'ID' => 2,
				'display_name' => 'User Two',
				'user_email' => 'user2@example.com',
				'user_registered' => '2024-01-02 12:00:00',
			],
		];

		Functions\expect( 'get_users' )
			->twice()
			->andReturnUsing(
				static function ( $args ) use ( $mock_users ) {
					self::assertSame( 'customer', $args['role'] );
					self::assertSame( 'display_name', $args['orderby'] );
					self::assertSame( 'ASC', $args['order'] );

					if ( 'ids' === ( $args['fields'] ?? null ) ) {
						return [ 1, 2 ];
					}

					self::assertSame( 6, $args['number'] );
					self::assertSame( 0, $args['offset'] );

					return $mock_users;
				}
			);

		Functions\when( 'get_user_meta' )->justReturn( '123456789' );

		Filters\expectApplied( 'stolmc_service_tracker_get_users_count_args' )
			->once()
			->andReturnUsing( static fn( $args ) => $args );
		Filters\expectApplied( 'stolmc_service_tracker_get_users_args' )
			->once()
			->andReturnUsing( static fn( $args ) => $args );
		Filters\expectApplied( 'stolmc_service_tracker_users_response' )
			->once()
			->andReturnUsing( static fn( $data ) => $data );

		$request = $this->create_mock_request();

		$response = $this->api->get_users( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertSame( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertCount( 2, $data['data'] );
		$this->assertSame( 'User One', $data['data'][0]['name'] );
		$this->assertSame( 'user1@example.com', $data['data'][0]['email'] );
	}

	/**
	 * Test get_users applies filters to query args.
	 */
	public function test_get_users_applies_filters_to_query_args(): void {
		$modified_args = null;
		Functions\expect( 'get_users' )
			->twice()
			->andReturnUsing(
				static function ( $args ) use ( &$modified_args ) {
					if ( 'ids' === ( $args['fields'] ?? null ) ) {
						return [ 1, 2 ];
					}

					$modified_args = $args;
					self::assertSame( 'customer', $args['role'] );
					self::assertSame( 'email', $args['orderby'] );
					self::assertSame( 'DESC', $args['order'] );
					return [];
				}
			);

		Filters\expectApplied( 'stolmc_service_tracker_get_users_count_args' )
			->once()
			->with( [ 'role' => 'customer', 'fields' => 'ids', 'orderby' => 'display_name', 'order' => 'ASC' ] )
			->andReturnUsing( static fn( $args ) => $args );
		Filters\expectApplied( 'stolmc_service_tracker_get_users_args' )
			->once()
			->with( [ 'role' => 'customer', 'orderby' => 'display_name', 'order' => 'ASC', 'number' => 6, 'offset' => 0 ] )
			->andReturn( [ 'role' => 'customer', 'orderby' => 'email', 'order' => 'DESC', 'number' => 6, 'offset' => 0 ] );
		Filters\expectApplied( 'stolmc_service_tracker_users_response' )
			->once()
			->andReturnUsing( static fn( $data ) => $data );

		$request = $this->create_mock_request();

		$this->api->get_users( $request );

		$this->assertNotNull( $modified_args );
		$this->assertSame( 'email', $modified_args['orderby'] );
	}

	/**
	 * Test get_users applies filters to response.
	 */
	public function test_get_users_applies_filters_to_response(): void {
		Functions\expect( 'get_users' )
			->twice()
			->andReturnUsing(
				static function ( $args ) {
					if ( 'ids' === ( $args['fields'] ?? null ) ) {
						return [];
					}

					return [];
				}
			);

		Filters\expectApplied( 'stolmc_service_tracker_get_users_count_args' )
			->once()
			->andReturnUsing( static fn( $args ) => $args );
		Filters\expectApplied( 'stolmc_service_tracker_get_users_args' )
			->once()
			->andReturnUsing( static fn( $args ) => $args );
		Filters\expectApplied( 'stolmc_service_tracker_users_response' )
			->once()
			->with( [], [] )
			->andReturn( [ 'custom' => 'data' ] );

		$request = $this->create_mock_request();

		$response = $this->api->get_users( $request );

		$this->assertSame(
			[
				'data'        => [ 'custom' => 'data' ],
				'total'       => 0,
				'page'        => 1,
				'per_page'    => 6,
				'total_pages' => 1,
			],
			$response->get_data()
		);
	}

	/**
	 * Test create creates user successfully.
	 */
	public function test_create_creates_user_successfully(): void {
		$body = json_encode(
			[
				'name' => 'New User',
				'email' => 'newuser@example.com',
				'phone' => '123456789',
				'cellphone' => '987654321',
			]
		);

		$request = $this->create_mock_request( [], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], $body );

		// First call to get_user_by should return false (user doesn't exist).
		// Second call should return the created user.
		Functions\when( 'get_user_by' )->alias(
			function ( $search, $value ) {
				static $call_count = 0;
				$call_count++;
				if ( $call_count === 1 ) {
					return false; // User doesn't exist yet.
				}
				return (object) [
					'ID' => 100,
					'display_name' => 'New User',
					'user_email' => 'newuser@example.com',
					'user_registered' => '2024-01-01 12:00:00',
				];
			}
		);

		Filters\expectApplied( 'stolmc_service_tracker_user_password' )
			->once()
			->andReturn( 'generated_password' );
		Filters\expectApplied( 'stolmc_service_tracker_user_create_data' )
			->once()
			->andReturnUsing( static fn( $data ) => $data );

		$this->expect_action_hook( 'stolmc_service_tracker_user_created' );
		$this->expect_action_hook( 'stolmc_service_tracker_user_created_with_meta' );

		$response = $this->api->create( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertSame( 201, $response->get_status() );

		$data = $response->get_data();
		$this->assertTrue( $data['success'] );
		$this->assertSame( 'User created successfully', $data['message'] );
		$this->assertSame( 100, $data['user']['id'] );
	}

	/**
	 * Test create returns error for missing name.
	 */
	public function test_create_returns_error_for_missing_name(): void {
		$body = json_encode( [ 'email' => 'test@example.com' ] );
		$request = $this->create_mock_request( [], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], $body );

		$response = $this->api->create( $request );

		$this->assertSame( 400, $response->get_status() );

		$data = $response->get_data();
		$this->assertFalse( $data['success'] );
		$this->assertSame( 'Name and email are required', $data['message'] );
	}

	/**
	 * Test create returns error for missing email.
	 */
	public function test_create_returns_error_for_missing_email(): void {
		$body = json_encode( [ 'name' => 'Test User' ] );
		$request = $this->create_mock_request( [], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], $body );

		$response = $this->api->create( $request );

		$this->assertSame( 400, $response->get_status() );

		$data = $response->get_data();
		$this->assertFalse( $data['success'] );
		$this->assertSame( 'Name and email are required', $data['message'] );
	}

	/**
	 * Test create returns error for existing email.
	 */
	public function test_create_returns_error_for_existing_email(): void {
		$body = json_encode(
			[
				'name' => 'Test User',
				'email' => 'existing@example.com',
			]
		);
		$request = $this->create_mock_request( [], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], $body );

		Functions\when( 'get_user_by' )->alias(
			static function ( $search, $value ) {
				if ( 'email' === $search ) {
					return (object) [ 'ID' => 50 ];
				}
				return false;
			}
		);

		$response = $this->api->create( $request );

		$this->assertSame( 409, $response->get_status() );

		$data = $response->get_data();
		$this->assertFalse( $data['success'] );
		$this->assertSame( 'A user with this email already exists', $data['message'] );
	}

	/**
	 * Test create returns error for wp_insert_user failure.
	 */
	public function test_create_returns_error_for_wp_insert_user_failure(): void {
		$body = json_encode(
			[
				'name' => 'Test User',
				'email' => 'test@example.com',
			]
		);
		$request = $this->create_mock_request( [], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], $body );

		Functions\when( 'get_user_by' )->justReturn( false );
		Functions\when( 'wp_generate_password' )->justReturn( 'password' );
		Functions\when( 'wp_insert_user' )->justReturn(
			new \WP_Error( 'registration_error', 'Registration failed' )
		);

		Filters\expectApplied( 'stolmc_service_tracker_user_password' )
			->once()
			->andReturn( 'password' );
		Filters\expectApplied( 'stolmc_service_tracker_user_create_data' )
			->once()
			->andReturnUsing( static fn( $data ) => $data );

		$response = $this->api->create( $request );

		$this->assertSame( 500, $response->get_status() );

		$data = $response->get_data();
		$this->assertFalse( $data['success'] );
	}

	/**
	 * Test user_create_data filter can modify user data.
	 */
	public function test_user_create_data_filter_can_modify_user_data(): void {
		$body = json_encode( [
			'name' => 'Test User',
			'email' => 'test@example.com',
		] );
		$request = $this->create_mock_request( [], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], $body );

		// First call to get_user_by should return false (user doesn't exist).
		// Second call should return the created user.
		Functions\when( 'get_user_by' )->alias(
			static function ( $search, $value ) {
				static $call_count = 0;
				$call_count++;
				if ( $call_count === 1 ) {
					return false; // User doesn't exist yet.
				}
				return (object) [
					'ID' => 100,
					'display_name' => 'Modified User',
					'user_email' => 'test@example.com',
					'user_registered' => '2024-01-01 12:00:00',
				];
			}
		);
		Functions\when( 'wp_generate_password' )->justReturn( 'password' );
		Functions\when( 'wp_insert_user' )->justReturn( 100 );

		Filters\expectApplied( 'stolmc_service_tracker_user_password' )
			->once()
			->andReturn( 'password' );
		Filters\expectApplied( 'stolmc_service_tracker_user_create_data' )
			->once()
			->andReturnUsing(
				static function ( $data ) {
					$data['display_name'] = 'Modified User';
					return $data;
				}
			);

		Actions\expectDone( 'stolmc_service_tracker_user_created' )
			->once()
			->with( 100, Mockery::type( 'array' ), Mockery::type( 'object' ), 'password' );

		$response = $this->api->create( $request );

		$this->assertSame( 201, $response->get_status() );
	}

	/**
	 * Test user_created hook fires with correct parameters.
	 */
	public function test_user_created_hook_fires_with_correct_parameters(): void {
		$body = json_encode( [
			'name' => 'Test User',
			'email' => 'test@example.com',
			'phone' => '123456789',
		] );
		$request = $this->create_mock_request( [], [ 'x_wp_nonce' => [ 'valid_nonce' ] ], $body );

		// First call to get_user_by should return false (user doesn't exist).
		// Second call should return the created user.
		Functions\when( 'get_user_by' )->alias(
			static function ( $search, $value ) {
				static $call_count = 0;
				$call_count++;
				if ( $call_count === 1 ) {
					return false; // User doesn't exist yet.
				}
				return (object) [
					'ID' => 100,
					'display_name' => 'Test User',
					'user_email' => 'test@example.com',
					'user_registered' => '2024-01-01 12:00:00',
				];
			}
		);
		Functions\when( 'wp_generate_password' )->justReturn( 'password' );
		Functions\when( 'wp_insert_user' )->justReturn( 100 );
		Functions\when( 'update_user_meta' )->justReturn( true );

		Filters\expectApplied( 'stolmc_service_tracker_user_password' )
			->once()
			->andReturn( 'password' );
		Filters\expectApplied( 'stolmc_service_tracker_user_create_data' )
			->once()
			->andReturnUsing( static fn( $data ) => $data );

		Actions\expectDone( 'stolmc_service_tracker_user_created' )
			->once()
			->with(
				100,
				Mockery::type( 'array' ),
				Mockery::type( 'object' ),
				'password'
			);

		$response = $this->api->create( $request );

		$this->assertSame( 201, $response->get_status() );
	}
}
