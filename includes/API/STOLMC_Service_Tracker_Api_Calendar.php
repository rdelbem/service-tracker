<?php
namespace STOLMC_Service_Tracker\includes\API;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use STOLMC_Service_Tracker\includes\DB\CalendarIndex;
use STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Sql;

/**
 * Calendar API class for aggregating case and progress data.
 *
 * Provides a dedicated endpoint for calendar views that aggregates
 * cases with date ranges and progress entries as date markers.
 *
 * ENDPOINT => wp-json/service-tracker-stolmc/v1/calendar
 */
class STOLMC_Service_Tracker_Api_Calendar extends STOLMC_Service_Tracker_Api implements STOLMC_Service_Tracker_Api_Contract {

	/**
	 * SQL helper instance for cases table.
	 *
	 * @var STOLMC_Service_Tracker_Sql
	 */
	private $cases_sql;

	/**
	 * SQL helper instance for progress table.
	 *
	 * @var STOLMC_Service_Tracker_Sql
	 */
	private $progress_sql;

	/**
	 * Database table name constants.
	 */
	private const CASES_DB = 'servicetracker_cases';
	private const PROGRESS_DB = 'servicetracker_progress';

	/**
	 * Initialize the API and register routes.
	 *
	 * @return void
	 */
	public function run(): void {
		global $wpdb;

		$this->custom_api();
		$this->cases_sql = new STOLMC_Service_Tracker_Sql( $wpdb->prefix . self::CASES_DB );
		$this->progress_sql = new STOLMC_Service_Tracker_Sql( $wpdb->prefix . self::PROGRESS_DB );
	}

	/**
	 * Register custom API routes for calendar aggregation.
	 *
	 * @return void
	 */
	public function custom_api(): void {
		$route_args = [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_calendar' ],
			'permission_callback' => [ $this, 'permission_check' ],
		];

		register_rest_route(
			'service-tracker-stolmc/v1',
			'/calendar',
			$route_args
		);
	}

	/**
	 * Read method (required by contract, not used for calendar).
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Method not allowed response.
	 */
	public function read( WP_REST_Request $data ): mixed {
		return $this->get_calendar( $data );
	}

	/**
	 * Create method (not applicable for calendar).
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Method not allowed response.
	 */
	public function create( WP_REST_Request $data ): mixed {
		return $this->rest_response(
			[
				'success' => false,
				'message' => 'Method not allowed',
			],
			405
		);
	}

	/**
	 * Update method (not applicable for calendar).
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Method not allowed response.
	 */
	public function update( WP_REST_Request $data ): mixed {
		return $this->rest_response(
			[
				'success' => false,
				'message' => 'Method not allowed',
			],
			405
		);
	}

	/**
	 * Delete method (not applicable for calendar).
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Method not allowed response.
	 */
	public function delete( WP_REST_Request $data ): mixed {
		return $this->rest_response(
			[
				'success' => false,
				'message' => 'Method not allowed',
			],
			405
		);
	}

	/**
	 * Get calendar data aggregated from cases and progress.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Calendar data with cases and progress entries.
	 */
	public function get_calendar( WP_REST_Request $data ): WP_REST_Response {
		$start = $data->get_param( 'start' );
		$end = $data->get_param( 'end' );

		if ( empty( $start ) || empty( $end ) ) {
			return $this->rest_response(
				[
					'success' => false,
					'message' => 'Missing required parameters: start and end',
				],
				400
			);
		}

		$id_user = $data->get_param( 'id_user' );
		$status = $data->get_param( 'status' );

		// Fetch all progress entries (we'll filter by date range in PHP).
		$progress_entries = $this->progress_sql->get_all();

		// Build cases query args — only actual DB columns.
		$cases_query_args = [];
		if ( ! empty( $id_user ) ) {
			$cases_query_args['id_user'] = $id_user;
		}

		if ( ! empty( $status ) ) {
			$cases_query_args['status'] = $status;
		}

		/**
		 * Filters the calendar query arguments before fetching cases.
		 * Only real database column names should be included.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $cases_query_args The query arguments.
		 * @param WP_REST_Request $data             The REST request object.
		 */
		$cases_query_args = apply_filters( 'stolmc_service_tracker_calendar_query_args', $cases_query_args, $data );

		// Fetch cases. Use get_all() when no conditions to avoid invalid SQL.
		if ( empty( $cases_query_args ) ) {
			$cases = $this->cases_sql->get_all();
		} else {
			$cases = $this->cases_sql->get_by( $cases_query_args );
		}

		// Build calendar cases array.
		$calendar_cases = [];
		if ( is_iterable( $cases ) ) {
			foreach ( $cases as $case ) {
				// phpcs:ignore Squiz.PHP.CommentedOutCode.Found -- stdClass from wpdb.
				/**
				 * Date object.
				 *
				 * @var object{start_at: string|null, due_at: string|null, created_at: string, id_user: int, id: int, title: string, status: string, description: string|null} $case
				 * */
				// Filter by date range if start_at or due_at exists.
				if ( ! empty( $case->start_at ) || ! empty( $case->due_at ) ) {
					$case_start = ! empty( $case->start_at ) ? $case->start_at : $case->created_at;
					$case_end   = ! empty( $case->due_at ) ? $case->due_at : $case->created_at;

					// Check if case overlaps with the requested date range.
					if ( $case_start <= $end && $case_end >= $start ) {
						$user        = get_user_by( 'id', $case->id_user );
						$client_name = $user ? $user->display_name : 'Unknown';

						$calendar_cases[] = [
							'id'          => $case->id,
							'id_user'     => $case->id_user,
							'title'       => $case->title,
							'status'      => $case->status,
							'description' => $case->description ?? '',
							'start_at'    => $case->start_at,
							'due_at'      => $case->due_at,
							'client_name' => $client_name,
						];
					}
				}
			}
		}

		/**
		 * Filters the calendar cases response.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $calendar_cases The calendar cases data.
		 * @param WP_REST_Request $data           The REST request object.
		 */
		$calendar_cases = apply_filters( 'stolmc_service_tracker_calendar_cases_response', $calendar_cases, $data );

		// Build calendar progress array.
		$calendar_progress = [];
		if ( is_iterable( $progress_entries ) ) {
			foreach ( $progress_entries as $entry ) {
				// phpcs:ignore Squiz.PHP.CommentedOutCode.Found -- stdClass from wpdb.
				/**
				 * Progress entry object.
				 *
				 * @var object{created_at: string, id_user: int, id_case: int, id: int, text: string} $entry
				 * */
				// Filter by date range.
				if ( $entry->created_at >= $start && $entry->created_at <= $end ) {
					$user        = get_user_by( 'id', $entry->id_user );
					$client_name = $user ? $user->display_name : 'Unknown';

					// Get case title for context.
					$case_result = $this->cases_sql->get_by( [ 'id' => $entry->id_case ] );
					$case_title  = 'Unknown Case';
					if ( is_array( $case_result ) && isset( $case_result[0] ) ) {
						// phpcs:ignore Squiz.PHP.CommentedOutCode.Found -- stdClass from wpdb.
						/**
						 * Case object.
						 *
						 *  @var object{title: string} $first_case
						 * */
						$first_case = $case_result[0];
						$case_title = $first_case->title;
					}

					$calendar_progress[] = [
						'id'          => $entry->id,
						'id_case'     => $entry->id_case,
						'id_user'     => $entry->id_user,
						'text'        => $entry->text,
						'created_at'  => $entry->created_at,
						'case_title'  => $case_title,
						'client_name' => $client_name,
					];
				}
			}
		}

		/**
		 * Filters the calendar progress response.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $calendar_progress The calendar progress data.
		 * @param WP_REST_Request $data              The REST request object.
		 */
		$calendar_progress = apply_filters( 'stolmc_service_tracker_calendar_progress_response', $calendar_progress, $data );

		$payload = [
			'cases'      => $calendar_cases,
			'progress'   => $calendar_progress,
			'date_index' => CalendarIndex::get(),
		];

		/**
		 * Filters the final calendar payload before returning.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $payload The complete calendar payload.
		 * @param WP_REST_Request $data    The REST request object.
		 */
		$payload = apply_filters( 'stolmc_service_tracker_calendar_payload', $payload, $data );

		return $this->rest_response( $payload, 200 );
	}
}
