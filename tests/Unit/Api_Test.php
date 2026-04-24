<?php
/**
 * API Base Class Test
 *
 * Tests for the STOLMC_Service_Tracker_Api base class.
 *
 * @package Service_Tracker
 */

namespace STOLMC_Service_Tracker\Tests\Unit;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

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
	 * @var \STOLMC_Service_Tracker\includes\Controller_API\STOLMC_Service_Tracker_Api
	 */
	protected $api;

	/**
	 * Set up test fixtures.
	 */
	protected function set_up(): void {
		parent::set_up();
		$this->api = new \STOLMC_Service_Tracker\includes\Controller_API\STOLMC_Service_Tracker_Api();
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
