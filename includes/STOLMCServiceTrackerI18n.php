<?php
namespace STOLMCServiceTracker\includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

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
class STOLMCServiceTrackerI18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'service-tracker-stolmc',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}
