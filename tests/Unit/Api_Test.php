<?php
/**
 * API Base Class Test
 *
 * Tests for the STOLMC_Service_Tracker_Api base class.
 *
 * @package Service_Tracker
 */

namespace STOLMC_Service_Tracker\Tests\Unit;

use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use WP_REST_Response;

/**
 * API Base Class Test.
 *
 * @group   unit
 * @group   api
 */
class Api_Test extends API_TestCase {

	/**
	 * API instance.
	 *
	 * @var \STOLMC_Service_Tracker\includes\API\STOLMC_Service_Tracker_Api
	 */
	protected $api;

	/**
	 * Set up test fixtures.
	 */
	protected function set_up(): void {
		parent::set_up();
		$this->api = new \STOLMC_Service_Tracker\includes\API\STOLMC_Service_Tracker_Api();
	}

	/**
	 * Test user verification returns true for authorized user.
	 */
	public function test_user_verification_returns_true_for_authorized_user(): void {
		Functions\when( 'current_user_can' )->justReturn( true );
		Filters\expectApplied( 'stolmc_service_tracker_api_user_can' )
			->once()
			->with( true, self::TEST_USER_ID )
			->andReturnArg( 1 );

		$result = $this->api->user_verification();

		$this->assertTrue( $result );
	}

	/**
	 * Test user verification returns false for unauthorized user.
	 */
	public function test_user_verification_returns_false_for_unauthorized_user(): void {
		// Since current_user_can is stubbed to return true in setup_common_stubs,
		// we test the filter behavior by having it return false.
		Filters\expectApplied( 'stolmc_service_tracker_api_user_can' )
			->once()
			->with( true, self::TEST_USER_ID )
			->andReturn( false );

		$result = $this->api->user_verification();

		$this->assertFalse( $result );
	}

	/**
	 * Test user verification filter can modify result.
	 */
	public function test_user_verification_can_be_filtered(): void {
		Functions\when( 'current_user_can' )->justReturn( false );
		Filters\expectApplied( 'stolmc_service_tracker_api_user_can' )
			->once()
			->with( false, self::TEST_USER_ID )
			->andReturn( true );

		$result = $this->api->user_verification();

		$this->assertTrue( $result );
	}

	/**
	 * Test security check returns null for valid nonce.
	 */
	public function test_security_check_returns_null_for_valid_nonce(): void {
		Functions\when( 'wp_verify_nonce' )->justReturn( true );
		
		$request = $this->create_mock_request();

		$result = $this->api->security_check( $request );

		$this->assertNull( $result );
	}

	/**
	 * Test security check returns error for invalid nonce.
	 */
	public function test_security_check_returns_error_for_invalid_nonce(): void {
		Functions\when( 'wp_verify_nonce' )->justReturn( false );
		Functions\when( '__' )->returnArg( 1 );
		Filters\expectApplied( 'stolmc_service_tracker_api_security_check' )
			->once()
			->andReturnUsing( static fn( $value ) => $value );

		$request = $this->create_mock_request();

		$result = $this->api->security_check( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $result );
		$this->assertSame( 'Sorry, invalid credentials', $result->get_data() );
	}

	/**
	 * Test security check returns null for empty request.
	 */
	public function test_security_check_returns_null_for_empty_request(): void {
		$result = $this->api->security_check( null );

		$this->assertNull( $result );
	}

	/**
	 * Test register new route registers REST endpoint.
	 */
	public function test_register_new_route_registers_rest_endpoint(): void {
		$registered = false;
		Functions\when( 'register_rest_route' )->alias(
			static function () use ( &$registered ) {
				$registered = true;
				return true;
			}
		);

		$this->api->register_new_route( 'cases', '_user', 'GET', [ $this->api, 'read' ] );

		$this->assertTrue( $registered );
	}

	/**
	 * Test register new route fires action hook.
	 */
	public function test_register_new_route_fires_action_hook(): void {
		Functions\when( 'register_rest_route' )->justReturn( true );

		Actions\expectDone( 'stolmc_service_tracker_api_route_registered' )
			->once();

		$this->api->register_new_route( 'cases', '_user', 'GET', [ $this->api, 'read' ] );

		// If we get here without exception, the action was fired.
		$this->assertTrue( true );
	}

	/**
	 * Test register new route applies filter to route args.
	 */
	public function test_register_new_route_applies_filter_to_args(): void {
		Functions\when( 'register_rest_route' )->justReturn( true );

		$filtered_value = null;
		Filters\expectApplied( 'stolmc_service_tracker_api_route_args' )
			->once()
			->andReturnUsing(
				static function ( $args ) use ( &$filtered_value ) {
					$filtered_value = $args;
					return $args;
				}
			);

		$this->api->register_new_route( 'cases', '_user', 'GET', [ $this->api, 'read' ] );

		$this->assertNotNull( $filtered_value );
		$this->assertArrayHasKey( 'methods', $filtered_value );
	}
}
