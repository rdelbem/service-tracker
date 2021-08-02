<?php
/**
 * Service Tracker bootstrap file
 *
 * @link http://delbem.net/service-tracker
 * @since 1.0.0
 * @package Service Tracker
 *
 * Plugin Name: Service Tracker
 * Version: 1.0.0
 * Description: This plugin offers the possibilitie to track the services you provide.
 * Author: Rodrigo Del Bem <servicetracker@delbem.net>
 * Author URI: https://delbem.net
 * Plugin URI: https://delbem.net/services-tracker
 * Text Domain: service-tracker
 * Domain Path: languages
 */

defined( 'WPINC' ) or die();

require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';

use ServiceTracker\includes\Service_Tracker_Activator;
use ServiceTracker\Service_Tracker_Uninstall;
use ServiceTracker\includes\Service_Tracker;

function activate_st_service_tracker() {
	Service_Tracker_Activator::activate();
	Service_Tracker_Activator::activation_notice();
}

// Service Tracker should do nothing on deactivation

function uninstall_st_service_tracker() {
	Service_Tracker_Uninstall::uninstall();
}

register_activation_hook( __FILE__, 'activate_st_service_tracker' );

register_uninstall_hook( __FILE__, 'uninstall_st_service_tracker' );

$ST_serviceTracker = new Service_Tracker();

$ST_serviceTracker->run();

// UPDATE CHECKER
if ( is_admin() ) {
	require wp_normalize_path( plugin_dir_path( __FILE__ ) . 'plugin-update-checker/plugin-update-checker.php' );

	$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
		'https://delbem.net/plugins/service-tracker/update_verification.json',
		__FILE__, // Full path to the main plugin file or functions.php.
		'service-tracker'
	);
}
