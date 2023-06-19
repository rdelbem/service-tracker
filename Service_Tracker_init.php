<?php
/**
 * Service Tracker bootstrap file
 *
 * @link http://delbem.net/service-tracker
 * @since 1.0.0
 * @package Service Tracker STO
 *
 * Plugin Name: Service Tracker STO
 * Version: 1.0.0
 * Description: This plugin offers the possibilitie to track the services you provide.
 * Author: Rodrigo Del Bem <servicetracker@delbem.net>
 * Author URI: https://delbem.net
 * Plugin URI: https://delbem.net/services-tracker
 * Text Domain: service-tracker-sto
 * Domain Path: languages
 */

defined('WPINC') or die();

require_once plugin_dir_path(__FILE__) . '/vendor/autoload.php';

use ServiceTracker\includes\STOServiceTrackerActivator;
use ServiceTracker\STOServiceTrackerUninstall;
use ServiceTracker\includes\STOServiceTracker;

function STOactivateServiceTracker()
{
	STOServiceTrackerActivator::activate();
}

/**
 * Service Tracker should do nothing on deactivation,
 * that's because we want to preserve the tables created
 * during the plugin's usage
 */

function STOuninstallServiceTracker()
{
	STOServiceTrackerUninstall::uninstall();
}

register_activation_hook(__FILE__, 'STOactivateServiceTracker');

register_uninstall_hook(__FILE__, 'STOuninstallServiceTracker');

add_action('plugins_loaded', function () {
	(new STOServiceTracker())->run();
});