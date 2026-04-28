<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package STOLMC_Service_Tracker
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';

use STOLMC_Service_Tracker\STOLMC_Service_Tracker_Uninstall;

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
