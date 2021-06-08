<?php

namespace ServiceTracker\Sql;

class Activate {

	/**
	 * This is called on activation, it will create the necessary tables.
	 */

	public static function activate() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		global $wpdb;

		$tablename_cases = 'ServiceTracker_cases';
		$main_sql_create = 'CREATE TABLE ServiceTracker_cases (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY)';
		maybe_create_table( $tablename_cases, $main_sql_create );

		$tablename_progress = 'ServiceTracker_progress';
		$main_sql_create    = 'CREATE TABLE ' . $tablename_progress . ' id int(11) NOT NULL auto_increment;';
		maybe_create_table( $tablename_progress, $main_sql_create );
	}

}
