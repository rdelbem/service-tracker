<?php
/**
 * Service Tracker bootstrap file
 *
 * @link https://delbem.net/plugins/service-tracker-sto/
 * @since 1.0.0
 * @package Service Tracker STO
 *
 * Plugin Name: Service Tracker STO
 * Version: 2.0.0
 * Description: This plugin offers the possibilitie to track the services you provide.
 * Author: Rodrigo Del Bem <rodrigo@delbem.net>
 * Author URI: https://delbem.net/
 * Plugin URI: https://delbem.net/plugins/service-tracker-sto/
 * Text Domain: service-tracker-stolmc
 * Domain Path: /languages
 * Tested up to: 6.9.4
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';

/**
 * Register a fallback autoloader for plugin classes.
 *
 * This prevents runtime failures when Composer autoload metadata is generated
 * on a different machine/path and cannot resolve this plugin namespace.
 *
 * @return void
 */
function stolmc_register_service_tracker_fallback_autoloader(): void {
		spl_autoload_register(
			static function ( string $class_name ): void {
				$prefix = 'STOLMC_Service_Tracker\\';
				if ( ! str_starts_with( $class_name, $prefix ) ) {
					return;
				}

				$relative_class = substr( $class_name, strlen( $prefix ) );
				$file_path      = plugin_dir_path( __FILE__ ) . str_replace( '\\', '/', $relative_class ) . '.php';

				if ( is_readable( $file_path ) ) {
					require_once $file_path;
				}
			}
	);
}

stolmc_register_service_tracker_fallback_autoloader();

use STOLMC_Service_Tracker\includes\Life_Cycle\STOLMC_Service_Tracker_Activator;
use STOLMC_Service_Tracker\includes\Life_Cycle\STOLMC_Service_Tracker_Deactivator;
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
 * Deactivate the Service Tracker plugin.
 *
 * Called on plugin deactivation. Drops all plugin-managed database
 * tables and clears the schema version.  Re-activating the plugin
 * will recreate tables from the current schema definition.
 *
 * @since    1.1.0
 *
 * @return void
 */
function stolmc_deactivate_service_tracker(): void {
	STOLMC_Service_Tracker_Deactivator::deactivate();

	/**
	 * Fires after the plugin has been deactivated.
	 *
	 * @since 1.1.0
	 */
	do_action( 'stolmc_service_tracker_deactivated' );
}

/**
 * Uninstall the Service Tracker plugin.
 *
 * Called when the plugin is permanently deleted from WordPress.
 * Drops all plugin data from the database.
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
register_deactivation_hook( __FILE__, 'stolmc_deactivate_service_tracker' );
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
