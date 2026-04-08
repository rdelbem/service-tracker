<?php

namespace STOLMC_Service_Tracker\includes\Life_Cycle;

/**
 * Handles plugin activation and database table creation.
 *
 * This class is responsible for creating the necessary database tables
 * when the plugin is activated.
 */
class STOLMC_Service_Tracker_Activator {

	/**
	 * Activate the plugin and create database tables.
	 *
	 * Creates the servicetracker_cases and servicetracker_progress tables
	 * if they do not already exist.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return void
	 */
	public static function activate(): void {

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		global $wpdb;

		$tablename_cases = $wpdb->prefix . 'servicetracker_cases';
		$main_sql_create_cases = 'CREATE TABLE ' . $tablename_cases . ' (';
		$main_sql_create_cases .= 'id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,';
		$main_sql_create_cases .= ' id_user INT(20) NOT NULL,'; // This will be filled with the user's ID.
		$main_sql_create_cases .= ' created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,';
		$main_sql_create_cases .= ' status VARCHAR(255),';
		$main_sql_create_cases .= ' title VARCHAR(255),';
		$main_sql_create_cases .= ' description TEXT)';

		/**
		 * Filters the cases table schema before creation.
		 *
		 * @since 1.0.0
		 *
		 * @param string $main_sql_create_cases The SQL CREATE TABLE statement.
		 */
		$main_sql_create_cases = apply_filters( 'stolmc_service_tracker_cases_table_schema', $main_sql_create_cases );

		maybe_create_table( $tablename_cases, $main_sql_create_cases );

		/**
		 * Fires after the cases table has been created.
		 *
		 * @since 1.0.0
		 *
		 * @param string $tablename_cases     The name of the cases table.
		 * @param string $main_sql_create_cases The SQL schema used.
		 */
		do_action( 'stolmc_service_tracker_cases_table_created', $tablename_cases, $main_sql_create_cases );

		// Add description column if table already exists (MySQL doesn't support IF NOT EXISTS in ALTER TABLE).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Checking schema during activation.
		$column_exists = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM INFORMATION_SCHEMA.COLUMNS
				WHERE TABLE_SCHEMA = %s
				AND TABLE_NAME = %s
				AND COLUMN_NAME = 'description'",
				$wpdb->dbname,
				$tablename_cases
			)
		);
		if ( empty( $column_exists ) ) {
			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Adding missing column during activation.
			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name cannot be parameterized in ALTER TABLE.
			$wpdb->query( "ALTER TABLE $tablename_cases ADD COLUMN description TEXT" );
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		$tablename_progress = $wpdb->prefix . 'servicetracker_progress';
		$main_sql_create_progress = 'CREATE TABLE ' . $tablename_progress . ' (';
		$main_sql_create_progress .= 'id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,';
		$main_sql_create_progress .= ' id_case INT(10) NOT NULL,';
		$main_sql_create_progress .= ' id_user INT(20) NOT NULL,'; // This will be filled with the user's ID.
		$main_sql_create_progress .= ' created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,';
		$main_sql_create_progress .= ' text TEXT)';

		/**
		 * Filters the progress table schema before creation.
		 *
		 * @since 1.0.0
		 *
		 * @param string $main_sql_create_progress The SQL CREATE TABLE statement.
		 */
		$main_sql_create_progress = apply_filters( 'stolmc_service_tracker_progress_table_schema', $main_sql_create_progress );

		maybe_create_table( $tablename_progress, $main_sql_create_progress );

		/**
		 * Fires after the progress table has been created.
		 *
		 * @since 1.0.0
		 *
		 * @param string $tablename_progress     The name of the progress table.
		 * @param string $main_sql_create_progress The SQL schema used.
		 */
		do_action( 'stolmc_service_tracker_progress_table_created', $tablename_progress, $main_sql_create_progress );
	}
}
