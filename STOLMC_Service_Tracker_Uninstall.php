<?php
namespace STOLMC_Service_Tracker;

/**
 * Handles plugin uninstallation and cleanup.
 *
 * This class is responsible for removing all plugin data
 * from the database when the plugin is uninstalled.
 */
class STOLMC_Service_Tracker_Uninstall {

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
	public static function uninstall(): void {
		global $wpdb;

		$table_array = [
			'servicetracker_cases',
			'servicetracker_progress',
			'servicetracker_uploads',
		];

		/**
		 * Fires before database tables are dropped.
		 *
		 * @since 1.0.0
		 *
		 * @param array $table_array Array of table names to be dropped.
		 */
		do_action( 'stolmc_service_tracker_before_drop_tables', $table_array );

		foreach ( $table_array as $tablename ) {
			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Dropping tables on uninstall.
			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name cannot be parameterized in DROP TABLE.
			$wpdb->query( "DROP TABLE IF EXISTS $tablename" );
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			/**
			 * Fires after a database table has been dropped.
			 *
			 * @since 1.0.0
			 *
			 * @param string $tablename The name of the dropped table.
			 */
			do_action( 'stolmc_service_tracker_table_dropped', $tablename );
		}
	}
}
