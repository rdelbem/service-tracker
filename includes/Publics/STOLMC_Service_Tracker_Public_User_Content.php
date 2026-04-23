<?php
namespace STOLMC_Service_Tracker\includes\Publics;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Moment\Moment;
use STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Sql;
use WP_User;

/**
 * Handles public-facing user content display.
 *
 * Manages shortcode registration and displays case progress
 * information to customers on the public-facing side of the site.
 */
class STOLMC_Service_Tracker_Public_User_Content {

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
	 * @var      array<int, array{case_title: string, case_id: int, created_at: string, case_status: string, progress: array<int, array{created_at: string, text: string}>}>|null
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
	public function get_user_id(): void {

		if ( ! is_user_logged_in() ) {
			return;
		}

		$this->current_user_id = get_current_user_id();

		/**
		 * Fires after a public user has been identified.
		 *
		 * @since 1.0.0
		 *
		 * @param int $user_id The current user ID.
		 */
		do_action( 'stolmc_service_tracker_public_user_identified', $this->current_user_id );

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
	public function check_user_role(): void {
		$user = new WP_User( $this->current_user_id );

		if ( empty( $user->roles ) ) {
			return;
		}

		if ( ! in_array( 'customer', $user->roles, true ) ) {
			return;
		}

		/**
		 * Fires after the customer role has been verified for a public user.
		 *
		 * @since 1.0.0
		 *
		 * @param int     $user_id The current user ID.
		 * @param WP_User $user    The WP_User object.
		 */
		do_action( 'stolmc_service_tracker_public_customer_role_verified', $this->current_user_id, $user );

		$this->get_statuses_by_cases();
		$this->add_shortcode();
	}

	/**
	 * SQL helper instance for cases table operations.
	 *
	 * @var STOLMC_Service_Tracker_Sql
	 */
	private $cases_sql;

	/**
	 * SQL helper instance for progress table operations.
	 *
	 * @var STOLMC_Service_Tracker_Sql
	 */
	private $progress_sql;

	/**
	 * Constructor.
	 *
	 * @param STOLMC_Service_Tracker_Sql|null $cases_sql SQL service for cases table.
	 * @param STOLMC_Service_Tracker_Sql|null $progress_sql SQL service for progress table.
	 */
	public function __construct( ?STOLMC_Service_Tracker_Sql $cases_sql = null, ?STOLMC_Service_Tracker_Sql $progress_sql = null ) {
		global $wpdb;

			// Use provided services or create defaults for backward compatibility.
		$this->cases_sql = $cases_sql ?? new STOLMC_Service_Tracker_Sql( $wpdb->prefix . 'servicetracker_cases' );
		$this->progress_sql = $progress_sql ?? new STOLMC_Service_Tracker_Sql( $wpdb->prefix . 'servicetracker_progress' );
	}

	/**
	 * Get all cases for the current user.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return array<object>|object|null Array of cases or null on failure.
	 */
	public function get_user_cases(): array|object|null {
		$cases = $this->cases_sql->get_by( [ 'id_user' => $this->current_user_id ] );

		/**
		 * Filters the cases shown to the public user.
		 *
		 * @since 1.0.0
		 *
		 * @param array|object|null $cases   The cases data.
		 * @param int               $user_id The current user ID.
		 */
		return apply_filters( 'stolmc_service_tracker_public_user_cases', $cases, $this->current_user_id );
	}

	/**
	 * Get progress entries for a specific case.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param int $id_case The case ID.
	 *
	 * @return array<int, array{created_at: string, text: string}> Array of progress entries with formatted dates.
	 */
	public function get_case_progress( int $id_case ): array {
		$status = $this->progress_sql->get_by( [ 'id_case' => $id_case ] );
		$progress_array = [];

		if ( ! is_iterable( $status ) ) {
			return $progress_array;
		}

		foreach ( $status as $stat ) {
			$status_obj = [];
			$status_obj['created_at'] = $this->locale_translation_time( $stat->{'created_at'} );
			$status_obj['text'] = $stat->{'text'};

			array_push( $progress_array, $status_obj );
		}

		/**
		 * Filters the case progress entries shown to the public user.
		 *
		 * @since 1.0.0
		 *
		 * @param array $progress_array The progress entries.
		 * @param int   $id_case        The case ID.
		 */
		return apply_filters( 'stolmc_service_tracker_public_case_progress', $progress_array, $id_case );
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
	public function locale_translation_time( string $date ): string {
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

		/**
		 * Filters the date format used for locale translation.
		 *
		 * @since 1.0.0
		 *
		 * @param string $time_format The time format string.
		 * @param string $locale      The site locale.
		 * @param string $date        The original date string.
		 */
		$time_format = apply_filters( 'stolmc_service_tracker_date_format', $time_format, $locale, $date );

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
	public function get_statuses_by_cases(): void {
		$cases = $this->get_user_cases();

		$case_and_statuses = [];

		if ( ! is_iterable( $cases ) ) {
			return;
		}

		foreach ( $cases as $case ) {
			// phpcs:ignore Generic.Commenting.DocComment.MissingShort -- PHPStan type hint for stdClass.
			/** @var \stdClass $case */
			$case_obj                = [];
			$case_obj['case_title']  = $case->title;
			$case_obj['case_id'] = $case->id;
			$case_obj['created_at'] = $this->locale_translation_time( $case->created_at );
			$case_obj['case_status'] = $case->status;
			$case_obj['progress'] = $this->get_case_progress( $case->id );

			array_push( $case_and_statuses, $case_obj );
		}

		/**
		 * Filters the complete cases and statuses structure for the public user.
		 *
		 * @since 1.0.0
		 *
		 * @param array $case_and_statuses The cases and statuses array.
		 * @param int   $user_id           The current user ID.
		 */
		$case_and_statuses = apply_filters( 'stolmc_service_tracker_public_user_cases_and_statuses', $case_and_statuses, $this->current_user_id );

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
	public function add_shortcode(): void {
		add_shortcode( 'stolmc-service-tracker-cases-progress', [ $this, 'use_partial' ] ); // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound -- WordPress shortcode callback format.
	}

	/**
	 * Render the shortcode partial view.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return string
	 */
	public function use_partial(): string {
		$user_cases_and_statuses = $this->user_cases_and_statuses ?? [];

		/**
		 * Fires before the public shortcode is rendered.
		 *
		 * @since 1.0.0
		 *
		 * @param array $user_cases_and_statuses The cases and statuses data.
		 */
		do_action( 'stolmc_service_tracker_public_before_shortcode_render', $user_cases_and_statuses );

		ob_start();
		include wp_normalize_path( plugin_dir_path( __FILE__ ) . 'partials/cases_progress.php' );
		$output = ob_get_clean();

		/**
		 * Filters the public shortcode HTML output.
		 *
		 * @since 1.0.0
		 *
		 * @param string $output                  The rendered HTML output.
		 * @param array  $user_cases_and_statuses The cases and statuses data.
		 */
		$output = apply_filters( 'stolmc_service_tracker_public_shortcode_output', false !== $output ? $output : '', $user_cases_and_statuses );

		return $output;
	}
}
