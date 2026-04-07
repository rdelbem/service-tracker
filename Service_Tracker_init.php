<?php
/**
 * Service Tracker bootstrap file
 *
 * @link https://delbem.net/portfolio/service-tracker-sto/
 * @since 1.0.0
 * @package Service Tracker STO
 *
 * Plugin Name: Service Tracker STO
 * Version: 1.0.1
 * Description: This plugin offers the possibilitie to track the services you provide.
 * Author: Rodrigo Del Bem <servicetracker@delbem.net>
 * Author URI: https://delbem.net/portfolio/
 * Plugin URI: https://delbem.net/portfolio/service-tracker-sto/
 * Text Domain: service-tracker-stolmc
 * Domain Path: languages
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';

use STOLMCServiceTracker\includes\STOLMCServiceTrackerActivator;
use STOLMCServiceTracker\STOLMCServiceTrackerUninstall;
use STOLMCServiceTracker\includes\STOLMCServiceTracker;

/**
 * Activate the Service Tracker plugin.
 *
 * Called on plugin activation to create database tables.
 *
 * @since    1.0.0
 *
 * @return void
 */
function stolmc_activate_service_tracker() {
	STOLMCServiceTrackerActivator::activate();
}

/**
 * Uninstall the Service Tracker plugin.
 *
 * Service Tracker should do nothing on deactivation,
 * that's because we want to preserve the tables created
 * during the plugin's usage.
 *
 * @since    1.0.0
 *
 * @return void
 */
function stolmc_uninstall_service_tracker() {
	STOLMCServiceTrackerUninstall::uninstall();
}

register_activation_hook( __FILE__, 'stolmc_activate_service_tracker' );
register_uninstall_hook( __FILE__, 'stolmc_uninstall_service_tracker' );

add_action(
	'plugins_loaded',
	function () {
		( new STOLMCServiceTracker() )->run();
	}
);
