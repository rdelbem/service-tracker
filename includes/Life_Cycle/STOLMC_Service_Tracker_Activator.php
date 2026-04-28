<?php

namespace STOLMC_Service_Tracker\includes\Life_Cycle;

use STOLMC_Service_Tracker\includes\DB\STOLMC_Schema_Manager;
use STOLMC_Service_Tracker\includes\DB\STOLMC_Schema;

/**
 * Handles plugin activation and database table creation.
 *
 * On activation this class delegates table creation to the STOLMC_Schema_Manager,
 * which uses the declarative schema definition to build CREATE TABLE
 * statements.  Any schema updates required by a future plugin version
 * are handled by the STOLMC_Schema_Manager on every `init` hook, not here.
 *
 * @since    1.0.0
 * @package  STOLMC_Service_Tracker
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
		$manager = new STOLMC_Schema_Manager();
		$manager->create_tables();

		// Migrate existing users from old role names to new prefixed roles.
		self::migrate_roles();

		// Mark the schema as current so the init-time sync skips work.
		update_option( STOLMC_Schema::VERSION_OPTION, STOLMC_Schema::VERSION, true );
		update_option( STOLMC_Schema_Manager::PLUGIN_VERSION_OPTION, STOLMC_SERVICE_TRACKER_VERSION, false );
	}

	/**
	 * Migrate users from legacy role names to prefixed role names.
	 *
	 * Maps 'customer' → 'stolmc_customer' and 'staff' → 'stolmc_staff'.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return void
	 */
	private static function migrate_roles(): void {
		if ( ! function_exists( 'get_users' ) ) {
			return;
		}

		$migrations = [
			'customer' => 'stolmc_customer',
			'staff'    => 'stolmc_staff',
		];

		foreach ( $migrations as $old_role => $new_role ) {
			$users = get_users( [ 'role' => $old_role ] );

			foreach ( $users as $user ) {
				$user->remove_role( $old_role );
				$user->add_role( $new_role );
			}
		}
	}
}
