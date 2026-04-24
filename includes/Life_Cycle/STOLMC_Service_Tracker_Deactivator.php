<?php

namespace STOLMC_Service_Tracker\includes\Life_Cycle;

use STOLMC_Service_Tracker\includes\DB\Schema_Manager;
use STOLMC_Service_Tracker\includes\DB\Schema;

/**
 * Handles plugin deactivation and database table removal.
 *
 * On deactivation this class drops all plugin-managed tables and
 * clears the stored schema version.  The plugin can be re-activated
 * at any time, at which point the Activator will recreate the tables
 * from the current declarative schema.
 *
 * @since    1.1.0
 * @package  STOLMC_Service_Tracker\includes\Life_Cycle
 */
class STOLMC_Service_Tracker_Deactivator {

	/**
	 * Deactivate the plugin and drop database tables.
	 *
	 * All data in the servicetracker_cases and servicetracker_progress
	 * tables will be permanently lost.
	 *
	 * @since    1.1.0
	 * @access   public
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		$manager = new Schema_Manager();
		$manager->drop_tables();

		// Clear version tracking so re-activation treats this as a fresh install.
		delete_option( Schema::VERSION_OPTION );
		delete_option( Schema_Manager::MIGRATIONS_LOG_OPTION );
	}
}
