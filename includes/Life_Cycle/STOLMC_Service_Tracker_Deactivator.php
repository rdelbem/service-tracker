<?php

namespace STOLMC_Service_Tracker\includes\Life_Cycle;

/**
 * Handles plugin deactivation.
 *
 * Deactivation is non-destructive: tables and data are preserved so
 * the plugin can be re-activated without data loss.  Only the
 * uninstall handler (STOLMC_Service_Tracker_Uninstall) should
 * remove data permanently.
 *
 * @since    1.1.0
 * @package  STOLMC_Service_Tracker
 */
class STOLMC_Service_Tracker_Deactivator {

	/**
	 * Deactivate the plugin.
	 *
	 * Performs non-destructive cleanup only (e.g. clearing scheduled
	 * events or transients).  Database tables and options are left
	 * intact so re-activation is seamless.
	 *
	 * @since    1.1.0
	 * @access   public
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		// Nothing to clean up on deactivation.
	}
}
