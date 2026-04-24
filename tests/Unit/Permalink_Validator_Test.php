<?php
/**
 * Permalink Validator Test
 *
 * Tests for the STOLMC_Service_Tracker_Permalink_Validator class.
 *
 * @package Service_Tracker
 */

namespace STOLMC_Service_Tracker\Tests\Unit;

use Brain\Monkey\Functions;
use STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Permalink_Validator;

/**
 * Permalink Validator Test Class.
 *
 * @group   unit
 * @group   permalink
 */
class Permalink_Validator_Test extends Unit_TestCase {

	/**
	 * Validator instance.
	 *
	 * @var STOLMC_Service_Tracker_Permalink_Validator
	 */
	private $validator;

	/**
	 * Set up test fixtures.
	 */
	protected function set_up(): void {
		parent::set_up();
		$this->validator = new STOLMC_Service_Tracker_Permalink_Validator();
	}

	/**
	 * Test valid permalink structure.
	 */
	public function test_valid_permalink_structure(): void {
		Functions\when( 'get_option' )->justReturn( '/%postname%/' );

		$result = $this->validator->is_permalink_structure_valid();

		$this->assertTrue( $result );
	}

	/**
	 * Test invalid permalink structure.
	 */
	public function test_invalid_permalink_structure(): void {
		Functions\when( 'get_option' )->justReturn( '/?p=123' );

		$result = $this->validator->is_permalink_structure_valid();

		$this->assertFalse( $result );
	}

	/**
	 * Test empty permalink structure.
	 */
	public function test_empty_permalink_structure(): void {
		Functions\when( 'get_option' )->justReturn( '' );

		$result = $this->validator->is_permalink_structure_valid();

		$this->assertFalse( $result );
	}

	/**
	 * Test permalink structure with filter.
	 */
	public function test_permalink_structure_can_be_filtered(): void {
		Functions\when( 'get_option' )->justReturn( '/?p=123' );
		Functions\when( 'apply_filters' )->alias(
			static function ( $tag, $value ) {
				if ( 'stolmc_service_tracker_permalink_required_structure' === $tag ) {
					return '/?p=123'; // Accept default structure for this test.
				}
				return $value;
			}
		);

		$result = $this->validator->is_permalink_structure_valid();

		$this->assertTrue( $result );
	}
}
