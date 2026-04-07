<?php
namespace STOLMCServiceTracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handles plugin uninstallation and cleanup.
 *
 * This class is responsible for removing all plugin data
 * from the database when the plugin is uninstalled.
 */
class STOLMCServiceTrackerUninstall {

	/**
	 * Uninstall the plugin and remove database tables.
	 *
	 * Drops all Service Tracker custom tables from the database.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return void
	 */
	public static function uninstall() {
		global $wpdb;

		$table_array = [
			'servicetracker_cases',
			'servicetracker_progress',
			'servicetracker_uploads',
		];

		foreach ( $table_array as $tablename ) {
			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Dropping tables on uninstall.
			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name cannot be parameterized in DROP TABLE.
			$wpdb->query( "DROP TABLE IF EXISTS $tablename" );
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}
	}
}
