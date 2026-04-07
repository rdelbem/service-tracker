<?php

namespace STOLMCServiceTracker\includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Validates WordPress permalink structure for plugin compatibility.
 *
 * Ensures the permalink structure is set to /%postname%/ which is required
 * for the Service Tracker plugin to function correctly.
 */
class STOLMCServiceTrackerPermalinkValidator {

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
	public function is_permalink_structure_valid() {
		if ( get_option( 'permalink_structure' ) !== '/%postname%/' ) {
			return false;
		}

		return true;
	}
}
