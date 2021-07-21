<?php
/**
 * Service Tracker bootstrap file
 *
 * @link http://delbem.net/service-tracker
 * @since 1.0.0
 * @package Service Tracker
 *
 * Plugin Name: Service Tracker
 * Version: 1.0
 * Description: This plugin offers the possibilitie to track the services you provide.
 * Author: Rodrigo Del Bem <rodrigodelbem@gmail.com>
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
use ServiceTracker\includes\Service_Tracker_Api;

function activate_st_service_tracker() {
	Service_Tracker_Activator::activate();
}

// Service Tracker should do nothing on deactivation

function uninstall_st_service_tracker() {
	Service_Tracker_Uninstall::uninstall();
}

register_activation_hook( __FILE__, 'activate_st_service_tracker' );

register_uninstall_hook( __FILE__, 'uninstall_st_service_tracker' );

$ST_serviceTracker = new Service_Tracker();

$ST_serviceTracker->run();
