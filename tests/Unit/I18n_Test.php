<?php
/**
 * I18n Test
 *
 * Forward-only i18n tests after removing the dedicated i18n class.
 *
 * @package Service_Tracker
 */

namespace STOLMC_Service_Tracker\Tests\Unit;

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
	 * Ensure there is no i18n service namespace class loaded.
	 */
	public function test_no_dedicated_i18n_runtime_service_exists(): void {
		$this->assertFalse( class_exists( '\\STOLMC_Service_Tracker\\includes\\I18n' ) );
	}
}
