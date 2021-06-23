<?php
namespace ServiceTracker;

class Service_Tracker_Uninstall {

	/**
	 * This class will drop all tables
	 */

	public static function uninstall() {
		global $wpdb;
		$tableArray = array(
			'servicetracker_cases',
			'servicetracker_progress',
			'servicetracker_uploads',
		);

		foreach ( $tableArray as $tablename ) {
				 $wpdb->query( "DROP TABLE IF EXISTS $tablename" );
		}
	}

}
