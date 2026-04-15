<?php
namespace STOLMC_Service_Tracker\includes\API;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Sql;

/**
 * This class will resolve API calls intended to manipulate the cases table.
 *
 * It extends the API class that serves as a model.
 *
 * ENDPOINT => wp-json/service-tracker/v1/cases/[user_id]
 */
class STOLMC_Service_Tracker_Api_Cases extends STOLMC_Service_Tracker_Api implements STOLMC_Service_Tracker_Api_Contract {

	/**
	 * SQL helper instance for cases table operations.
	 *
	 * @var STOLMC_Service_Tracker_Sql
	 */
	private $sql;

	/**
	 * SQL helper instance for progress table operations.
	 *
	 * @var STOLMC_Service_Tracker_Sql
	 */
	private $progress_sql;

	/**
	 * Database table name constant for cases.
	 */
	private const DB = 'servicetracker_cases';

	/**
	 * Database table name constant for progress.
	 */
	private const DB_PROGRESS = 'servicetracker_progress';

	/**
	 * Number of cases returned per page by default.
	 *
	 * @since 1.3.0
	 */
	private const PER_PAGE_DEFAULT = 6;

	/**
	 * Initialize the API and register routes.
	 *
	 * @return void
	 */
	public function run(): void {
		global $wpdb;

		$this->custom_api();
		$this->sql = new STOLMC_Service_Tracker_Sql( $wpdb->prefix . self::DB );

		$this->progress_sql = new STOLMC_Service_Tracker_Sql( $wpdb->prefix . self::DB_PROGRESS );
	}

	/**
	 * Register custom API routes for cases management.
	 *
	 * @return void
	 */
	public function custom_api(): void {

		// RegisterNewRoute -> Method from superclass / extended class.
		$this->register_new_route( 'cases', '_user', WP_REST_Server::READABLE, [ $this, 'read' ], [
			'page'     => [
				'default'           => 1,
				'sanitize_callback' => 'absint',
			],
			'per_page' => [
				'default'           => self::PER_PAGE_DEFAULT,
				'sanitize_callback' => 'absint',
			],
		] );
		$this->register_new_route( 'cases', '', WP_REST_Server::EDITABLE, [ $this, 'update' ] );
		$this->register_new_route( 'cases', '', WP_REST_Server::DELETABLE, [ $this, 'delete' ] );
		$this->register_new_route( 'cases', '_user', WP_REST_Server::CREATABLE, [ $this, 'create' ] );
	}

	/**
	 * Read cases for a specific user, with pagination.
	 *
	 * Accepts `page` (1-based) and `per_page` query parameters.
	 * Returns a paginated envelope: { data, total, page, per_page, total_pages }.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Paginated response.
	 */
	public function read( WP_REST_Request $data ): WP_REST_Response {
		global $wpdb;

		$id_user  = (int) $data['id_user'];
		$page     = max( 1, (int) $data->get_param( 'page' ) );
		$per_page = max( 1, (int) $data->get_param( 'per_page' ) );

		if ( $per_page === 0 ) {
			$per_page = self::PER_PAGE_DEFAULT;
		}

		$table = $wpdb->prefix . self::DB;

		/**
		 * Filters the query parameters for reading cases.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $query_args The query parameters.
		 * @param WP_REST_Request $data       The REST request object.
		 */
		$query_args = apply_filters(
			'stolmc_service_tracker_cases_read_query_args',
			[ 'id_user' => $id_user ],
			$data
		);

		// Count total cases for this user.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$total = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE id_user = %d",
				$query_args['id_user']
			)
		);

		$total_pages = (int) ceil( $total / $per_page );

		// Clamp page to valid range.
		$page   = min( $page, max( 1, $total_pages ) );
		$offset = ( $page - 1 ) * $per_page;

		// Fetch paginated cases.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$cases = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id_user = %d ORDER BY created_at DESC LIMIT %d OFFSET %d",
				$query_args['id_user'],
				$per_page,
				$offset
			)
		);

		/**
		 * Filters the cases read response data.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $cases The cases data.
		 * @param WP_REST_Request $data  The REST request object.
		 */
		$cases = apply_filters( 'stolmc_service_tracker_cases_read_response', $cases, $data );

		return $this->rest_response(
			[
				'data'        => $cases,
				'total'       => $total,
				'page'        => $page,
				'per_page'    => $per_page,
				'total_pages' => $total_pages,
			],
			200
		);
	}

	/**
	 * Create a new case entry.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Response indicating success or failure.
	 */
	public function create( WP_REST_Request $data ): WP_REST_Response {
		$body = $data->get_body();
		$body = json_decode( $body );
		$id_user     = $body->id_user;
		$title       = $body->title;
		$status      = isset( $body->status ) ? $body->status : 'open';
		$description = isset( $body->description ) ? $body->description : '';
		$start_at    = isset( $body->start_at ) ? $body->start_at : null;
		$due_at      = isset( $body->due_at ) ? $body->due_at : null;
		$owner_id    = isset( $body->owner_id ) ? $body->owner_id : null;

		// Validate date range if both are provided.
		if ( ! empty( $start_at ) && ! empty( $due_at ) && $start_at > $due_at ) {
			return $this->rest_response(
				[
					'success' => false,
					'message' => 'start_at must be before or equal to due_at',
				],
				400
			);
		}

		$case_data = [
			'id_user'     => $id_user,
			'title'       => $title,
			'status'      => $status,
			'description' => $description,
			'start_at'    => $start_at,
			'due_at'      => $due_at,
			'owner_id'    => $owner_id,
		];

		/**
		 * Filters the case data before insertion.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $case_data The case data to insert.
		 * @param WP_REST_Request $data      The REST request object.
		 */
		$case_data = apply_filters( 'stolmc_service_tracker_case_create_data', $case_data, $data );

		global $wpdb;
		$inserted = $this->sql->insert( $case_data );

		if ( $wpdb->insert_id ) {
			/**
			 * Fires after a case has been created.
			 *
			 * @since 1.0.0
			 *
			 * @param int             $case_id   The ID of the created case.
			 * @param array           $case_data The case data.
			 * @param WP_REST_Request $data      The REST request object.
			 */
			do_action( 'stolmc_service_tracker_case_created', $wpdb->insert_id, $case_data, $data );

			return $this->rest_response(
				[
					'success' => true,
					'id'      => $wpdb->insert_id,
					'message' => 'Case created successfully',
				],
				201
			);
		}

		/**
		 * Fires when a case creation fails.
		 *
		 * @since 1.0.0
		 *
		 * @param string|false $inserted  The error message.
		 * @param array        $case_data The case data that failed.
		 */
		do_action( 'stolmc_service_tracker_case_create_failed', $inserted, $case_data );

		return $this->rest_response(
			[
				'success' => false,
				'message' => 'Failed to create case',
				'error'   => $inserted,
			],
			500
		);
	}

	/**
	 * Update an existing case entry.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return int|false|null Update result message.
	 */
	public function update( WP_REST_Request $data ): int|false|null {
		$body = $data->get_body();
		$body = json_decode( $body );

		$update_data = [];

		if ( isset( $body->title ) ) {
			$update_data['title'] = $body->title;
		}

		if ( isset( $body->status ) ) {
			$update_data['status'] = $body->status;
		}

		if ( isset( $body->description ) ) {
			$update_data['description'] = $body->description;
		}

		if ( isset( $body->start_at ) ) {
			$update_data['start_at'] = $body->start_at;
		}

		if ( isset( $body->due_at ) ) {
			$update_data['due_at'] = $body->due_at;
		}

		if ( property_exists( $body, 'owner_id' ) ) {
			$update_data['owner_id'] = $body->owner_id;
		}

		// Validate date range if both are provided.
		if ( isset( $update_data['start_at'] ) && isset( $update_data['due_at'] )
			&& $update_data['start_at'] > $update_data['due_at'] ) {
			return null;
		}

		$condition = [ 'id' => $data['id'] ];

		/**
		 * Filters the update data before the SQL operation.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $update_data The data to update.
		 * @param array           $condition   The WHERE condition.
		 * @param WP_REST_Request $data        The REST request object.
		 */
		$update_data = apply_filters( 'stolmc_service_tracker_case_update_data', $update_data, $condition, $data );

		$response = $this->sql->update( $update_data, $condition );

		/**
		 * Fires after a case has been updated.
		 *
		 * @since 1.0.0
		 *
		 * @param int|false|null  $response    The update result.
		 * @param array           $update_data The data that was updated.
		 * @param array           $condition   The WHERE condition.
		 * @param WP_REST_Request $data        The REST request object.
		 */
		do_action( 'stolmc_service_tracker_case_updated', $response, $update_data, $condition, $data );

		return $response;
	}

	/**
	 * Delete a case entry and its associated progress records.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return mixed
	 */
	public function delete( WP_REST_Request $data ): mixed {
		$case_id = $data['id'];

		/**
		 * Fires before a case is deleted.
		 *
		 * @since 1.0.0
		 *
		 * @param int             $case_id The ID of the case to delete.
		 * @param WP_REST_Request $data    The REST request object.
		 */
		do_action( 'stolmc_service_tracker_case_before_delete', $case_id, $data );

		$delete          = $this->sql->delete( [ 'id' => $case_id ] );
		$delete_progress = $this->progress_sql->delete( [ 'id_case' => $case_id ] );

		/**
		 * Fires after a case has been deleted.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed           $delete          The case delete result.
		 * @param mixed           $delete_progress The progress delete result.
		 * @param int             $case_id         The ID of the deleted case.
		 * @param WP_REST_Request $data            The REST request object.
		 */
		do_action( 'stolmc_service_tracker_case_deleted', $delete, $delete_progress, $case_id, $data );

		return [
			'case_delete'     => $delete,
			'progress_delete' => $delete_progress,
		];
	}
}
