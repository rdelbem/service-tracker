<?php
namespace STOLMCServiceTracker\publics;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Moment\Moment;
use STOLMCServiceTracker\includes\STOLMCServiceTrackerSql;
use WP_User;

/**
 * Handles public-facing user content display.
 *
 * Manages shortcode registration and displays case progress
 * information to customers on the public-facing side of the site.
 */
class STOLMCServiceTrackerPublicUserContent {

	/**
	 * The current logged-in user ID.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      int
	 */
	public $current_user_id;

	/**
	 * Array of user cases and their statuses.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      array
	 */
	public $user_cases_and_statuses;

	/**
	 * Get the current user ID and check their role.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return void
	 */
	public function get_user_id() {

		if ( ! is_user_logged_in() ) {
			return;
		}

		$this->current_user_id = get_current_user_id();
		$this->check_user_role();
	}

	/**
	 * Check if the current user has the customer role.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return void
	 */
	public function check_user_role() {
		$user = new WP_User( $this->current_user_id );

		if ( empty( $user->roles ) ) {
			return;
		}

		if ( is_array( $user->roles ) && ! in_array( 'customer', $user->roles, true ) ) {
			return;
		}

		if ( is_array( $user->roles ) && in_array( 'customer', $user->roles, true ) ) {
			$this->get_statuses_by_cases();
			$this->add_shortcode();
		}
	}

	/**
	 * Get all cases for the current user.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return array|object|null Array of cases or null on failure.
	 */
	public function get_user_cases() {
		$sql = new STOLMCServiceTrackerSql( 'servicetracker_cases' );
		$cases = $sql->get_by( [ 'id_user' => $this->current_user_id ] );
		return $cases;
	}

	/**
	 * Get progress entries for a specific case.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param int $id_case The case ID.
	 *
	 * @return array Array of progress entries with formatted dates.
	 */
	public function get_case_progress( $id_case ) {
		$sql = new STOLMCServiceTrackerSql( 'servicetracker_progress' );
		$status = $sql->get_by( [ 'id_case' => $id_case ] );
		$progress_array = [];

		foreach ( $status as $stat ) {
			$status_obj = [];
			$status_obj['created_at'] = $this->locale_translation_time( $stat->{'created_at'} );
			$status_obj['text'] = $stat->{'text'};

			array_push( $progress_array, $status_obj );
		}

		return $progress_array;
	}

	/**
	 * Format date according to site locale.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param string $date The date to format.
	 *
	 * @return string Formatted date string.
	 */
	public function locale_translation_time( $date ) {
		$locale = get_locale();

		switch ( $locale ) {
			case 'pt_BR':
				$time_format = 'd/m/y - H:i:s';
				break;
			case 'en_US':
				$time_format = 'M d/y - h:i:s a';
				break;
			default:
				$time_format = 'm/d/y - h:i:s a';
				break;
		}

		$format_date = new Moment( $date );

		return $format_date->format( $time_format );
	}

	/**
	 * Get all cases and their statuses for the current user.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return void
	 */
	public function get_statuses_by_cases() {
		$cases = $this->get_user_cases();

		$case_and_statuses = [];

		foreach ( $cases as $case ) {
			$case_obj = [];
			$case_obj['case_title'] = $case->title;
			$case_obj['case_id'] = $case->id;
			$case_obj['created_at'] = $this->locale_translation_time( $case->created_at );
			$case_obj['case_status'] = $case->status;
			$case_obj['progress'] = $this->get_case_progress( $case->id );

			array_push( $case_and_statuses, $case_obj );
		}

		$this->user_cases_and_statuses = $case_and_statuses;
	}

	/**
	 * Register the shortcode for displaying case progress.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return void
	 */
	public function add_shortcode() {
		add_shortcode( 'stolmc-service-tracker-cases-progress', [ $this, 'use_partial' ] );
	}

	/**
	 * Render the shortcode partial view.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return void
	 */
	public function use_partial() {
		$user_cases_and_statuses = $this->user_cases_and_statuses;
		include wp_normalize_path( plugin_dir_path( __FILE__ ) . 'partials/cases_progress.php' );
	}
}
