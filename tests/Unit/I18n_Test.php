<?php
/**
 * I18n Test
 *
 * Tests for the STOLMC_Service_Tracker_I18n class.
 *
 * @package Service_Tracker
 */

namespace STOLMC_Service_Tracker\Tests\Unit;

use Brain\Monkey\Functions;

/**
 * I18n Test Class.
 *
 * @group   unit
 * @group   i18n
 */
class I18n_Test extends Unit_TestCase {

	/**
	 * I18n instance.
	 *
	 * @var \STOLMC_Service_Tracker\includes\I18n\STOLMC_Service_Tracker_I18n
	 */
	protected $i18n;

	/**
	 * Set up test fixtures.
	 */
	protected function set_up(): void {
		parent::set_up();

		$this->i18n = new \STOLMC_Service_Tracker\includes\I18n\STOLMC_Service_Tracker_I18n();
	}

	/**
	 * Test load_plugin_textdomain calls WordPress function with correct parameters.
	 */
	public function test_load_plugin_textdomain_calls_wordpress_function(): void {
		$domain_used = null;
		$path_used = null;

		Functions\when( 'load_plugin_textdomain' )->alias(
			static function ( $domain, $deprecated, $path ) use ( &$domain_used, &$path_used ) {
				$domain_used = $domain;
				$path_used = $path;
				return true;
			}
		);
		Functions\when( 'plugin_basename' )->justReturn( 'service-tracker/includes/I18n/STOLMC_Service_Tracker_I18n.php' );

		$this->i18n->load_plugin_textdomain();

		$this->assertSame( 'service-tracker-stolmc', $domain_used );
		$this->assertStringContainsString( '/languages/', $path_used );
	}

	/**
	 * Test load_plugin_textdomain uses correct text domain.
	 */
	public function test_load_plugin_textdomain_uses_correct_domain(): void {
		$domain_used = null;

		Functions\when( 'load_plugin_textdomain' )->alias(
			static function ( $domain ) use ( &$domain_used ) {
				$domain_used = $domain;
				return true;
			}
		);
		Functions\when( 'plugin_basename' )->justReturn( 'service-tracker/includes/I18n/STOLMC_Service_Tracker_I18n.php' );

		$this->i18n->load_plugin_textdomain();

		$this->assertSame( 'service-tracker-stolmc', $domain_used );
	}

	/**
	 * Test load_plugin_textdomain constructs correct path.
	 */
	public function test_load_plugin_textdomain_constructs_correct_path(): void {
		$path_used = null;

		Functions\when( 'load_plugin_textdomain' )->alias(
			static function ( $domain, $deprecated, $path ) use ( &$path_used ) {
				$path_used = $path;
				return true;
			}
		);
		Functions\when( 'plugin_basename' )->justReturn( 'service-tracker/includes/I18n/STOLMC_Service_Tracker_I18n.php' );

		$this->i18n->load_plugin_textdomain();

		// The path should end with /languages/ as specified in the I18n class.
		$this->assertStringEndsWith( '/languages/', $path_used );
		$this->assertStringContainsString( 'service-tracker', $path_used );
	}

	/**
	 * Test load_plugin_textdomain returns void.
	 */
	public function test_load_plugin_textdomain_returns_void(): void {
		Functions\when( 'load_plugin_textdomain' )->justReturn( true );
		Functions\when( 'plugin_basename' )->justReturn( 'service-tracker/includes/I18n/STOLMC_Service_Tracker_I18n.php' );

		$result = $this->i18n->load_plugin_textdomain();

		$this->assertNull( $result );
	}

	/**
	 * Test load_plugin_textdomain can be called multiple times.
	 */
	public function test_load_plugin_textdomain_can_be_called_multiple_times(): void {
		$call_count = 0;

		Functions\when( 'load_plugin_textdomain' )->alias(
			static function () use ( &$call_count ) {
				$call_count++;
				return true;
			}
		);
		Functions\when( 'plugin_basename' )->justReturn( 'service-tracker/includes/I18n/STOLMC_Service_Tracker_I18n.php' );

		$this->i18n->load_plugin_textdomain();
		$this->i18n->load_plugin_textdomain();
		$this->i18n->load_plugin_textdomain();

		$this->assertSame( 3, $call_count );
	}

	/**
	 * Test I18n class instantiates correctly.
	 */
	public function test_i18n_class_instantiates(): void {
		$this->assertInstanceOf(
			\STOLMC_Service_Tracker\includes\I18n\STOLMC_Service_Tracker_I18n::class,
			$this->i18n
		);
	}

	/**
	 * Test load_plugin_textdomain method exists.
	 */
	public function test_load_plugin_textdomain_method_exists(): void {
		$this->assertTrue( method_exists( $this->i18n, 'load_plugin_textdomain' ) );
	}
}
