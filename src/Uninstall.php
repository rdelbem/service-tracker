<?php
namespace ServiceTracker;

class Uninstall {

	/**
	 * This class will drop all tables on uninstallation
	 */

	public static function uninstall() {
		global $wpdb;
		$tableArray = array(
			'ServiceTracker_cases',
			'ServiceTracker_progress',
			'ServiceTracker_uploads',
		);

		foreach ( $tableArray as $tablename ) {
				 $wpdb->query( "DROP TABLE IF EXISTS $tablename" );
		}
	}

}
