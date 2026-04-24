<?php

namespace STOLMC_Service_Tracker\includes\Life_Cycle;

use STOLMC_Service_Tracker\includes\DB\Schema_Manager;
use STOLMC_Service_Tracker\includes\DB\Schema;

/**
 * Handles plugin activation and database table creation.
 *
 * On activation this class delegates table creation to the Schema_Manager,
 * which uses the declarative schema definition to build CREATE TABLE
 * statements.  Any schema updates required by a future plugin version
 * are handled by the Schema_Manager on every `init` hook, not here.
 *
 * @since    1.0.0
 * @package  STOLMC_Service_Tracker\includes\Life_Cycle
 */
class STOLMC_Service_Tracker_Activator {

	/**
	 * Activate the plugin and create database tables.
	 *
	 * Delegates table creation to the Schema_Manager and records the
	 * current schema version so that the init-time migration system
	 * knows the database is up-to-date.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return void
	 */
	public static function activate(): void {
		$manager = new Schema_Manager();
		$manager->create_tables();

		// Mark the schema as current so the init-time sync skips work.
		update_option( Schema::VERSION_OPTION, Schema::VERSION, true );
	}
}
