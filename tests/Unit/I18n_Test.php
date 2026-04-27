<?php
/**
 * I18n Test
 *
 * Tests for the centralised UI_Copy i18n class.
 *
 * @package Service_Tracker
 */

namespace STOLMC_Service_Tracker\Tests\Unit;

use Brain\Monkey;
use STOLMC_Service_Tracker\admin\I18n\UI_Copy;

/**
 * I18n Test Class.
 *
 * @group unit
 * @group i18n
 */
class I18n_Test extends Unit_TestCase {

	/**
	 * Ensure the legacy i18n class was intentionally removed.
	 */
	public function test_legacy_i18n_class_is_removed(): void {
		$this->assertFalse( class_exists( '\\STOLMC_Service_Tracker\\includes\\I18n\\STOLMC_Service_Tracker_I18n' ) );
	}

	/**
	 * Ensure the UI_Copy class exists and is instantiable.
	 */
	public function test_ui_copy_class_exists(): void {
		$this->assertTrue( class_exists( UI_Copy::class ) );
	}

	/**
	 * Ensure get_texts returns an array from the copy file.
	 */
	public function test_get_texts_returns_array(): void {
		$copy_file = dirname( __DIR__, 2 ) . '/admin/translation/ui_copy.php';

		Monkey\Functions\stubs(
			[
				'__' => static function ( string $text, string $domain = '' ): string {
					return $text;
				},
			]
		);

		$ui_copy = new UI_Copy( $copy_file );
		$texts   = $ui_copy->get_texts();

		$this->assertIsArray( $texts );
		$this->assertNotEmpty( $texts );
	}

	/**
	 * Ensure the copy file contains expected UI string keys.
	 */
	public function test_copy_file_contains_expected_keys(): void {
		$copy_file = dirname( __DIR__, 2 ) . '/admin/translation/ui_copy.php';

		Monkey\Functions\stubs(
			[
				'__' => static function ( string $text, string $domain = '' ): string {
					return $text;
				},
			]
		);

		$ui_copy = new UI_Copy( $copy_file );
		$texts   = $ui_copy->get_texts();

		$expected_keys = [
			'search_bar',
			'home_screen',
			'btn_add_case',
			'btn_save_case',
			'btn_dismiss_edit',
			'toast_case_added',
			'toast_case_deleted',
			'toast_case_edited',
			'toast_case_toggled',
			'toast_status_added',
			'alert_blank_case_title',
			'alert_blank_status_title',
			'alert_error_base',
			'no_progress_yet',
			'instructions_page_title',
		];

		foreach ( $expected_keys as $key ) {
			$this->assertArrayHasKey( $key, $texts, "Missing expected key: {$key}" );
		}
	}

	/**
	 * Ensure get_localize_data merges texts with config keys.
	 */
	public function test_get_localize_data_includes_config_keys(): void {
		$copy_file = dirname( __DIR__, 2 ) . '/admin/translation/ui_copy.php';

		Monkey\Functions\stubs(
			[
				'__' => static function ( string $text, string $domain = '' ): string {
					return $text;
				},
				'get_site_url'  => 'https://example.com',
				'get_rest_url'  => 'https://example.com/wp-json/',
				'wp_create_nonce' => 'test-nonce',
			]
		);

		$ui_copy     = new UI_Copy( $copy_file );
		$localize    = $ui_copy->get_localize_data();

		$this->assertIsArray( $localize );
		$this->assertArrayHasKey( 'root_url', $localize );
		$this->assertArrayHasKey( 'nonce', $localize );
		$this->assertArrayHasKey( 'api_url', $localize );
		$this->assertArrayHasKey( 'search_bar', $localize );
	}

	/**
	 * Ensure texts are cached across multiple calls.
	 */
	public function test_get_texts_is_cached(): void {
		$copy_file = dirname( __DIR__, 2 ) . '/admin/translation/ui_copy.php';

		Monkey\Functions\stubs(
			[
				'__' => static function ( string $text, string $domain = '' ): string {
					return $text;
				},
			]
		);

		$ui_copy = new UI_Copy( $copy_file );
		$first  = $ui_copy->get_texts();
		$second = $ui_copy->get_texts();

		$this->assertSame( $first, $second );
	}

	/**
	 * Ensure a missing copy file returns an empty array.
	 */
	public function test_missing_copy_file_returns_empty_array(): void {
		$ui_copy = new UI_Copy( '/nonexistent/path/ui_copy.php' );
		$texts   = $ui_copy->get_texts();

		$this->assertIsArray( $texts );
		$this->assertEmpty( $texts );
	}
}
