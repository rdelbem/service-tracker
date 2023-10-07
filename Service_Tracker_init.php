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

defined('WPINC') or die();

require_once plugin_dir_path(__FILE__) . '/vendor/autoload.php';

use STOLMCServiceTracker\includes\STOLMCServiceTrackerActivator;
use STOLMCServiceTracker\STOLMCServiceTrackerUninstall;
use STOLMCServiceTracker\includes\STOLMCServiceTracker;

function STOLMC_activateServiceTracker()
{
	STOLMCServiceTrackerActivator::activate();
}

/**
 * Service Tracker should do nothing on deactivation,
 * that's because we want to preserve the tables created
 * during the plugin's usage
 */

function STOLMC_uninstallServiceTracker()
{
	STOLMCServiceTrackerUninstall::uninstall();
}

register_activation_hook(__FILE__, 'STOLMC_activateServiceTracker');

register_uninstall_hook(__FILE__, 'STOLMC_uninstallServiceTracker');

add_action('plugins_loaded', function () {
	(new STOLMCServiceTracker())->run();
});