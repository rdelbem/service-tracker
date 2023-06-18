<?php
namespace ServiceTracker\includes;

/**
 * This is called on activation, it will create the necessary tables.
 */

class STOServiceTrackerActivator
{

	public static function activate()
	{

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		global $wpdb;

		$tablenameCases = $wpdb->prefix . 'servicetracker_cases';
		$mainSqlCreateCases = 'CREATE TABLE ' . $tablenameCases . ' (';
		$mainSqlCreateCases .= 'id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,';
		$mainSqlCreateCases .= ' id_user INT(20) NOT NULL,'; // this will be filled with the user's ID
		$mainSqlCreateCases .= ' created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,';
		$mainSqlCreateCases .= ' status VARCHAR(255),';
		$mainSqlCreateCases .= ' title VARCHAR(255))';
		maybe_create_table($tablenameCases, $mainSqlCreateCases);

		$tablenameProgress = $wpdb->prefix . 'servicetracker_progress';
		$mainSqlCreateProgress = 'CREATE TABLE ' . $tablenameProgress . ' (';
		$mainSqlCreateProgress .= 'id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,';
		$mainSqlCreateProgress .= ' id_case INT(10) NOT NULL,';
		$mainSqlCreateProgress .= ' id_user INT(20) NOT NULL,'; // this will be filled with the user's ID
		$mainSqlCreateProgress .= ' created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,';
		$mainSqlCreateProgress .= ' text TEXT)';
		maybe_create_table($tablenameProgress, $mainSqlCreateProgress);
	}

}