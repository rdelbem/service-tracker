<?php

namespace STOLMC_Service_Tracker;

defined( 'ABSPATH' ) || exit;

use STOLMC_Service_Tracker\includes\DB\STOLMC_Schema;
use STOLMC_Service_Tracker\includes\DB\STOLMC_Schema_Manager;

/**
 * Handles plugin uninstallation and cleanup.
 *
 * This class is responsible for removing all plugin data
 * from the database when the plugin is uninstalled.
 * Table definitions are consumed from STOLMC_Schema — the single
 * source of truth for all plugin tables.
 */
class STOLMC_Service_Tracker_Uninstall {

	/**
	 * Uninstall the plugin and remove database tables.
	 *
	 * Drops all Service Tracker custom tables defined in Schema.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return void
	 */
	public static function uninstall(): void {
		$table_names = array_column( STOLMC_Schema::get_all_tables(), 'table_name' );

		/**
		 * Fires before database tables are dropped.
		 *
		 * @since 1.0.0
		 *
		 * @param array $table_names Array of full table names to be dropped.
		 */
		do_action( 'stolmc_service_tracker_before_drop_tables', $table_names );

		$manager = new STOLMC_Schema_Manager();
		$manager->drop_tables();
	}
}
