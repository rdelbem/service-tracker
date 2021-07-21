<?php
namespace ServiceTracker\includes;

	/**
	 * This is called on activation, it will create the necessary tables.
	 */

class Service_Tracker_Activator {

	public static function activate() {

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		global $wpdb;

		$tablename_cases        = 'ServiceTracker_cases';
		$main_sql_create_cases  = 'CREATE TABLE ' . $tablename_cases . ' (';
		$main_sql_create_cases .= 'id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,';
		$main_sql_create_cases .= ' id_user INT(20) NOT NULL,'; // this will be filled with the user's ID
		$main_sql_create_cases .= ' created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,';
		$main_sql_create_cases .= ' status VARCHAR(255),';
		$main_sql_create_cases .= ' title VARCHAR(255))';
		maybe_create_table( $tablename_cases, $main_sql_create_cases );

		$tablename_progress        = 'ServiceTracker_progress';
		$main_sql_create_progress  = 'CREATE TABLE ' . $tablename_progress . ' (';
		$main_sql_create_progress .= 'id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,';
		$main_sql_create_progress .= ' id_case INT(10) NOT NULL,';
		$main_sql_create_progress .= ' id_user INT(20) NOT NULL,'; // this will be filled with the user's ID
		$main_sql_create_progress .= ' created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,';
		$main_sql_create_progress .= ' text TEXT)';
		maybe_create_table( $tablename_progress, $main_sql_create_progress );
		/*
		$tablename_uploads        = 'ServiceTracker_uploads';
		$main_sql_create_uploads  = 'CREATE TABLE ' . $tablename_uploads . ' (';
		$main_sql_create_uploads .= 'id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,';
		$main_sql_create_uploads .= ' id_case INT(10) NOT NULL,';
		$main_sql_create_uploads .= ' id_user INT(20) NOT NULL,'; // this will be filled with the user's ID
		$main_sql_create_uploads .= ' docs VARCHAR(255),';
		$main_sql_create_uploads .= ' sent VARCHAR(255),';
		$main_sql_create_uploads .= ' status VARCHAR(255),';
		$main_sql_create_uploads .= ' created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)';
		maybe_create_table( $tablename_uploads, $main_sql_create_uploads ); */
	}

}
