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

require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';

use STOLMC_Service_Tracker\includes\Life_Cycle\STOLMC_Service_Tracker_Activator;
use STOLMC_Service_Tracker\STOLMC_Service_Tracker_Uninstall;
use STOLMC_Service_Tracker\includes\STOLMC_Service_Tracker;

/**
 * Activate the Service Tracker plugin.
 *
 * Called on plugin activation to create database tables.
 *
 * @since    1.0.0
 *
 * @return void
 */
function stolmc_activate_service_tracker(): void {
	STOLMC_Service_Tracker_Activator::activate();
	
	/**
	 * Fires after the plugin has been activated.
	 *
	 * @since 1.0.0
	 */
	do_action( 'stolmc_service_tracker_activated' );
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
function stolmc_uninstall_service_tracker(): void {
	/**
	 * Fires before the plugin is uninstalled.
	 *
	 * @since 1.0.0
	 */
	do_action( 'stolmc_service_tracker_before_uninstall' );
	
	STOLMC_Service_Tracker_Uninstall::uninstall();
	
	/**
	 * Fires after the plugin has been uninstalled.
	 *
	 * @since 1.0.0
	 */
	do_action( 'stolmc_service_tracker_uninstalled' );
}

register_activation_hook( __FILE__, 'stolmc_activate_service_tracker' );
register_uninstall_hook( __FILE__, 'stolmc_uninstall_service_tracker' );

add_action(
	'plugins_loaded',
	function () {
		$plugin_instance = new STOLMC_Service_Tracker();
		
		/**
		 * Filters the plugin instance before it runs.
		 *
		 * @since 1.0.0
		 *
		 * @param STOLMC_Service_Tracker $plugin_instance The plugin instance.
		 */
		apply_filters( 'stolmc_service_tracker_before_run', $plugin_instance );
		
		$plugin_instance->run();
	}
);
