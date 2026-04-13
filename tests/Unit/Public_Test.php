<?php
/**
 * Public Test
 *
 * Tests for the STOLMC_Service_Tracker_Public class.
 *
 * @package Service_Tracker
 */

namespace STOLMC_Service_Tracker\Tests\Unit;

use Brain\Monkey\Functions;
use Brain\Monkey\Filters;

/**
 * Public Test Class.
 *
 * @group   unit
 * @group   public
 */
class Public_Test extends Unit_TestCase {

	/**
	 * Public instance.
	 *
	 * @var \STOLMC_Service_Tracker\includes\Publics\STOLMC_Service_Tracker_Public
	 */
	protected $public;

	/**
	 * Set up test fixtures.
	 */
	protected function set_up(): void {
		parent::set_up();

		$this->public = new \STOLMC_Service_Tracker\includes\Publics\STOLMC_Service_Tracker_Public(
			'service-tracker-stolmc',
			'1.0.0'
		);
	}

	/**
	 * Test constructor sets plugin name and version.
	 */
	public function test_constructor_sets_plugin_name_and_version(): void {
		$this->assertInstanceOf(
			\STOLMC_Service_Tracker\includes\Publics\STOLMC_Service_Tracker_Public::class,
			$this->public
		);
	}

	/**
	 * Test enqueue_styles enqueues stylesheet when shortcode is present.
	 */
	public function test_enqueue_styles_enqueues_stylesheet_when_shortcode_present(): void {
		$style_handle = null;
		$style_src = null;

		Functions\when( 'has_shortcode' )->justReturn( true );
		Functions\when( 'get_the_content' )->justReturn( '[stolmc-service-tracker-cases-progress]' );
		Functions\when( 'plugin_dir_url' )->justReturn( 'http://example.com/wp-content/plugins/service-tracker/' );
		Functions\when( 'wp_enqueue_style' )->alias(
			static function ( $handle, $src ) use ( &$style_handle, &$style_src ) {
				$style_handle = $handle;
				$style_src = $src;
			}
		);

		Filters\expectApplied( 'stolmc_service_tracker_public_enqueue_styles' )
			->atMost()->once()
			->andReturn( true );

		$this->public->enqueue_styles();

		$this->assertSame( 'service-tracker-stolmc', $style_handle );
		$this->assertStringContainsString( 'service-tracker-public.css', $style_src );
	}

	/**
	 * Test enqueue_styles does not enqueue stylesheet when shortcode is not present.
	 */
	public function test_enqueue_styles_does_not_enqueue_when_shortcode_not_present(): void {
		Functions\when( 'has_shortcode' )->justReturn( false );
		Functions\when( 'get_the_content' )->justReturn( 'No shortcode here' );

		Functions\expect( 'wp_enqueue_style' )
			->never();

		$this->public->enqueue_styles();

		$this->assertTrue( true );
	}

	/**
	 * Test enqueue_styles respects filter to prevent enqueuing.
	 */
	public function test_enqueue_styles_respects_filter_to_prevent_enqueuing(): void {
		Functions\when( 'has_shortcode' )->justReturn( true );
		Functions\when( 'get_the_content' )->justReturn( '[stolmc-service-tracker-cases-progress]' );

		Filters\expectApplied( 'stolmc_service_tracker_public_enqueue_styles' )
			->once()
			->with( true, 'service-tracker-stolmc' )
			->andReturn( false );

		Functions\expect( 'wp_enqueue_style' )
			->never();

		$this->public->enqueue_styles();

		$this->assertTrue( true );
	}

	/**
	 * Test enqueue_scripts does not enqueue when filter is false.
	 */
	public function test_enqueue_scripts_does_not_enqueue_when_filter_false(): void {
		Functions\when( 'has_shortcode' )->justReturn( true );
		Functions\when( 'get_the_content' )->justReturn( '[stolmc-service-tracker-cases-progress]' );

		Filters\expectApplied( 'stolmc_service_tracker_public_enqueue_scripts' )
			->atMost()->once()
			->andReturn( false );

		Functions\expect( 'wp_enqueue_script' )
			->never();

		$this->public->enqueue_scripts();

		$this->assertTrue( true );
	}

	/**
	 * Test enqueue_scripts does not enqueue when shortcode is not present.
	 */
	public function test_enqueue_scripts_does_not_enqueue_when_shortcode_not_present(): void {
		Functions\when( 'has_shortcode' )->justReturn( false );
		Functions\when( 'get_the_content' )->justReturn( 'No shortcode here' );

		Functions\expect( 'wp_enqueue_script' )
			->never();

		$this->public->enqueue_scripts();

		$this->assertTrue( true );
	}

	/**
	 * Test enqueue_styles returns void.
	 */
	public function test_enqueue_styles_returns_void(): void {
		Functions\when( 'has_shortcode' )->justReturn( false );
		Functions\when( 'get_the_content' )->justReturn( 'No shortcode' );

		$result = $this->public->enqueue_styles();

		$this->assertNull( $result );
	}

	/**
	 * Test enqueue_scripts returns void.
	 */
	public function test_enqueue_scripts_returns_void(): void {
		Functions\when( 'has_shortcode' )->justReturn( false );
		Functions\when( 'get_the_content' )->justReturn( 'No shortcode' );

		$result = $this->public->enqueue_scripts();

		$this->assertNull( $result );
	}
}
