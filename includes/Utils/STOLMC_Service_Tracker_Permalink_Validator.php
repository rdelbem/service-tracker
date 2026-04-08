<?php

namespace STOLMC_Service_Tracker\includes\Utils;

/**
 * Validates WordPress permalink structure for plugin compatibility.
 *
 * Ensures the permalink structure is set to /%postname%/ which is required
 * for the Service Tracker plugin to function correctly.
 */
class STOLMC_Service_Tracker_Permalink_Validator {

	/**
	 * Check if the permalink structure is valid for this plugin.
	 *
	 * Verifies that the WordPress permalink structure is set to '/%postname%/'.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return bool True if permalink structure is valid, false otherwise.
	 */
	public function is_permalink_structure_valid(): bool {
		/**
		 * Filters the required permalink structure.
		 *
		 * @since 1.0.0
		 *
		 * @param string $structure The required permalink structure.
		 */
		$required_structure = apply_filters( 'stolmc_service_tracker_permalink_required_structure', '/%postname%/' );

		if ( get_option( 'permalink_structure' ) !== $required_structure ) {
			$result = false;
		} else {
			$result = true;
		}

		/**
		 * Filters the permalink validation result.
		 *
		 * @since 1.0.0
		 *
		 * @param bool   $result             Whether the permalink structure is valid.
		 * @param string $current_structure The current permalink structure.
		 */
		return apply_filters( 'stolmc_service_tracker_permalink_is_valid', $result, get_option( 'permalink_structure' ) );
	}
}
