<?php
/**
 * Base Unit Test Case Class
 *
 * Extends Yoast's TestCase to provide BrainMonkey setup and teardown.
 *
 * @package Service_Tracker
 */

namespace STOLMC_Service_Tracker\Tests\Unit;

use Yoast\PHPUnitPolyfills\TestCases\TestCase;
use Brain\Monkey;

/**
 * Base Unit Test Case Class.
 */
abstract class Unit_TestCase extends TestCase {

	/**
	 * Set up test fixtures.
	 *
	 * @before
	 */
	protected function set_up(): void {
		parent::set_up();
		Monkey\setUp();
		$this->mock_wp_functions();
	}

	/**
	 * Tear down test fixtures.
	 *
	 * @after
	 */
	protected function tear_down(): void {
		Monkey\tearDown();
		parent::tear_down();
	}

	/**
	 * Mock common WordPress functions.
	 *
	 * Override this method in child classes to add specific mocks.
	 */
	protected function mock_wp_functions(): void {
		// Common stubs for all unit tests.
		Monkey\Functions\stubs(
			[
				'get_locale'   => 'en_US',
				'get_option'   => null,
				'update_option' => true,
				'delete_option' => true,
				'wp_parse_args' => static function ( $args, $defaults ) {
					return array_merge( $defaults, $args );
				},
			]
		);
	}
}
