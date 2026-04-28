<?php
namespace STOLMC_Service_Tracker\includes\Application;

use STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Sql;
use STOLMC_Service_Tracker\includes\Analytics\STOLMC_Analytics_Logger;
use STOLMC_Service_Tracker\includes\Analytics\STOLMC_Analytics_Hooks;
use STOLMC_Service_Tracker\includes\Controller_API\STOLMC_Service_Tracker_Api_Analytics;
use STOLMC_Service_Tracker\includes\Controller_API\STOLMC_Service_Tracker_Api_Calendar;
use STOLMC_Service_Tracker\includes\Controller_API\STOLMC_Service_Tracker_Api_Cases;
use STOLMC_Service_Tracker\includes\Controller_API\STOLMC_Service_Tracker_Api_Progress;
use STOLMC_Service_Tracker\includes\Controller_API\STOLMC_Service_Tracker_Api_Toggle;
use STOLMC_Service_Tracker\includes\Controller_API\STOLMC_Service_Tracker_Api_Users;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Analytics_Service;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Calendar_Service;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Cases_Service;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Progress_Service;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Toggle_Service;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Users_Service;

/**
 * Service Factory for the Service Tracker plugin.
 *
 * Centralizes creation of services and repositories to avoid scattered instantiation.
 * Provides a lightweight factory pattern without requiring a full DI container.
 *
 * @since    1.0.0
 * @package  STOLMC_Service_Tracker
 */
class STOLMC_Service_Tracker_Service_Factory {

	/**
	 * Create a SQL service instance for a specific table.
	 *
	 * @since 1.0.0
	 *
	 * @param string $table_name The full table name (including prefix).
	 * @return STOLMC_Service_Tracker_Sql
	 */
	public static function create_sql_service( string $table_name ): STOLMC_Service_Tracker_Sql {
		/**
		 * Filters the SQL service instance before creation.
		 *
		 * @since 1.0.0
		 *
		 * @param STOLMC_Service_Tracker_Sql|null $sql_service The SQL service instance.
		 * @param string $table_name The table name.
		 */
		$sql_service = apply_filters( 'stolmc_service_tracker_pre_create_sql_service', null, $table_name );

		if ( $sql_service instanceof STOLMC_Service_Tracker_Sql ) {
			return $sql_service;
		}

		$sql_service = new STOLMC_Service_Tracker_Sql( $table_name );

		/**
		 * Filters the SQL service instance after creation.
		 *
		 * @since 1.0.0
		 *
		 * @param STOLMC_Service_Tracker_Sql $sql_service The SQL service instance.
		 * @param string $table_name The table name.
		 */
		return apply_filters( 'stolmc_service_tracker_post_create_sql_service', $sql_service, $table_name );
	}

	/**
	 * Create an STOLMC_Analytics_Logger instance.
	 *
	 * @since 1.0.0
	 *
	 * @return STOLMC_Analytics_Logger
	 */
	public static function create_analytics_logger(): STOLMC_Analytics_Logger {
		global $wpdb;

		$notifications_table = $wpdb->prefix . 'servicetracker_notifications';
		$activity_log_table  = $wpdb->prefix . 'servicetracker_activity_log';

		/**
		 * Filters the STOLMC_Analytics_Logger instance before creation.
		 *
		 * @since 1.0.0
		 *
		 * @param STOLMC_Analytics_Logger|null $logger The STOLMC_Analytics_Logger instance.
		 * @param string $notifications_table The notifications table name.
		 * @param string $activity_log_table The activity log table name.
		 */
		$logger = apply_filters( 'stolmc_service_tracker_pre_create_analytics_logger', null, $notifications_table, $activity_log_table );

		if ( $logger instanceof STOLMC_Analytics_Logger ) {
			return $logger;
		}

		$logger = new STOLMC_Analytics_Logger( $notifications_table, $activity_log_table );

		/**
		 * Filters the STOLMC_Analytics_Logger instance after creation.
		 *
		 * @since 1.0.0
		 *
		 * @param STOLMC_Analytics_Logger $logger The STOLMC_Analytics_Logger instance.
		 * @param string $notifications_table The notifications table name.
		 * @param string $activity_log_table The activity log table name.
		 */
		return apply_filters( 'stolmc_service_tracker_post_create_analytics_logger', $logger, $notifications_table, $activity_log_table );
	}

	/**
	 * Create an STOLMC_Analytics_Hooks instance.
	 *
	 * @since 1.0.0
	 *
	 * @param STOLMC_Analytics_Logger $logger The STOLMC_Analytics_Logger instance.
	 * @return STOLMC_Analytics_Hooks
	 */
	public static function create_analytics_hooks( STOLMC_Analytics_Logger $logger ): STOLMC_Analytics_Hooks {
		/**
		 * Filters the STOLMC_Analytics_Hooks instance before creation.
		 *
		 * @since 1.0.0
		 *
		 * @param STOLMC_Analytics_Hooks|null $analytics_hooks The STOLMC_Analytics_Hooks instance.
		 * @param STOLMC_Analytics_Logger $logger The STOLMC_Analytics_Logger instance.
		 */
		$analytics_hooks = apply_filters( 'stolmc_service_tracker_pre_create_analytics_hooks', null, $logger );

		if ( $analytics_hooks instanceof STOLMC_Analytics_Hooks ) {
			return $analytics_hooks;
		}

		$analytics_hooks = new STOLMC_Analytics_Hooks( $logger );

		/**
		 * Filters the STOLMC_Analytics_Hooks instance after creation.
		 *
		 * @since 1.0.0
		 *
		 * @param STOLMC_Analytics_Hooks $analytics_hooks The STOLMC_Analytics_Hooks instance.
		 * @param STOLMC_Analytics_Logger $logger The STOLMC_Analytics_Logger instance.
		 */
		return apply_filters( 'stolmc_service_tracker_post_create_analytics_hooks', $analytics_hooks, $logger );
	}

	/**
	 * Create an STOLMC_Analytics_Hooks instance with its own logger.
	 *
	 * Convenience method that creates both logger and hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return STOLMC_Analytics_Hooks
	 */
	public static function create_analytics_hooks_with_logger(): STOLMC_Analytics_Hooks {
		$logger = self::create_analytics_logger();
		return self::create_analytics_hooks( $logger );
	}

	/**
	 * Create cases service.
	 *
	 * @return STOLMC_Service_Tracker_Cases_Service
	 */
	public static function create_cases_service(): STOLMC_Service_Tracker_Cases_Service {
		return new STOLMC_Service_Tracker_Cases_Service();
	}

	/**
	 * Create progress service.
	 *
	 * @return STOLMC_Service_Tracker_Progress_Service
	 */
	public static function create_progress_service(): STOLMC_Service_Tracker_Progress_Service {
		return new STOLMC_Service_Tracker_Progress_Service();
	}

	/**
	 * Create users service.
	 *
	 * @return STOLMC_Service_Tracker_Users_Service
	 */
	public static function create_users_service(): STOLMC_Service_Tracker_Users_Service {
		return new STOLMC_Service_Tracker_Users_Service();
	}

	/**
	 * Create toggle service.
	 *
	 * @return STOLMC_Service_Tracker_Toggle_Service
	 */
	public static function create_toggle_service(): STOLMC_Service_Tracker_Toggle_Service {
		return new STOLMC_Service_Tracker_Toggle_Service();
	}

	/**
	 * Create calendar service.
	 *
	 * @return STOLMC_Service_Tracker_Calendar_Service
	 */
	public static function create_calendar_service(): STOLMC_Service_Tracker_Calendar_Service {
		return new STOLMC_Service_Tracker_Calendar_Service();
	}

	/**
	 * Create analytics service.
	 *
	 * @return STOLMC_Service_Tracker_Analytics_Service
	 */
	public static function create_analytics_service(): STOLMC_Service_Tracker_Analytics_Service {
		return new STOLMC_Service_Tracker_Analytics_Service();
	}

	/**
	 * Create API controllers.
	 *
	 * @return array<int, object>
	 */
	public static function create_api_controllers(): array {
		return [
			new STOLMC_Service_Tracker_Api_Cases(),
			new STOLMC_Service_Tracker_Api_Progress(),
			new STOLMC_Service_Tracker_Api_Toggle(),
			new STOLMC_Service_Tracker_Api_Users(),
			new STOLMC_Service_Tracker_Api_Calendar(),
			new STOLMC_Service_Tracker_Api_Analytics(),
		];
	}
}
