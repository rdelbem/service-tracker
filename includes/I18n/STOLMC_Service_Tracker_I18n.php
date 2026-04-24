<?php
namespace STOLMC_Service_Tracker\includes\I18n;

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Service_Tracker
 * @subpackage Service_Tracker/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Service_Tracker
 * @subpackage Service_Tracker/includes
 * @author     Rodrigo Del Bem <rodrigodelbem@gmail.com>
 */
class STOLMC_Service_Tracker_I18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function load_plugin_textdomain(): void {

		load_plugin_textdomain(
			'service-tracker-stolmc',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}
