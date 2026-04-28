<?php
/**
 * PHPStan bootstrap file.
 *
 * Defines constants that are normally set by the WordPress plugin loader
 * (Service_Tracker_init.php) so PHPStan can resolve them during analysis.
 *
 * @package STOLMC_Service_Tracker
 */

if ( ! defined( 'STOLMC_SERVICE_TRACKER_ROOT_FILE' ) ) {
	define( 'STOLMC_SERVICE_TRACKER_ROOT_FILE', __DIR__ . '/Service_Tracker_init.php' );
}

if ( ! defined( 'STOLMC_SERVICE_TRACKER_VERSION' ) ) {
	define( 'STOLMC_SERVICE_TRACKER_VERSION', '2.0.0' );
}
