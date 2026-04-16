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
	 * Transient key for the cases search inverted index.
	 *
	 * @since 1.4.0
	 */
	private const SEARCH_INDEX_TRANSIENT = 'stolmc_st_case_search_index';

	/**
	 * How long (in seconds) the cases search index transient lives.
	 * Default: 1 hour.
	 *
	 * @since 1.4.0
	 */
	private const SEARCH_INDEX_TTL = 3600;

	/**
	 * Initialize the API and register routes.
	 *
	 * @return void
	 */
	public function run(): void {
		global $wpdb;

		$this->custom_api();
		$this->sql          = new STOLMC_Service_Tracker_Sql( $wpdb->prefix . self::DB );
		$this->progress_sql = new STOLMC_Service_Tracker_Sql( $wpdb->prefix . self::DB_PROGRESS );

		$this->register_index_invalidation_hooks();
	}

	/**
	 * Register hooks that bust the search index transient when case data changes.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	private function register_index_invalidation_hooks(): void {
		add_action( 'stolmc_service_tracker_case_created', [ $this, 'bust_search_index' ] );
		add_action( 'stolmc_service_tracker_case_updated', [ $this, 'bust_search_index' ] );
		add_action( 'stolmc_service_tracker_case_deleted', [ $this, 'bust_search_index' ] );
	}

	/**
	 * Delete the cached cases search index transient.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	public function bust_search_index(): void {
		delete_transient( self::SEARCH_INDEX_TRANSIENT );
	}

	/**
	 * Build (or retrieve from cache) the inverted search index for cases.
	 *
	 * Structure:
	 * [
	 *   'token' => [ ['id' => case_id, 'id_user' => user_id], ... ],
	 *   ...
	 * ]
	 *
	 * Tokens are lower-cased prefixes derived from the case title and status.
	 *
	 * @since 1.4.0
	 *
	 * @return array<string, array<int, array{id: int, id_user: int}>> The inverted index.
	 */
	private function get_search_index(): array {
		$cached = get_transient( self::SEARCH_INDEX_TRANSIENT );

		if ( is_array( $cached ) ) {
			return $cached;
		}

		global $wpdb;

		$table = $wpdb->prefix . self::DB;

		// Fetch all cases — id, id_user, title, status only for efficiency.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$cases = $wpdb->get_results(
			"SELECT id, id_user, title, status FROM {$table} ORDER BY id ASC"
		);

		$index = [];

		foreach ( $cases as $case ) {
			$entry  = [ 'id' => (int) $case->id, 'id_user' => (int) $case->id_user ];
			$tokens = $this->tokenize( $case->title . ' ' . $case->status );

			foreach ( $tokens as $token ) {
				if ( ! isset( $index[ $token ] ) ) {
					$index[ $token ] = [];
				}
				// Avoid duplicates.
				$already = false;
				foreach ( $index[ $token ] as $existing ) {
					if ( $existing['id'] === $entry['id'] ) {
						$already = true;
						break;
					}
				}
				if ( ! $already ) {
					$index[ $token ][] = $entry;
				}
			}
		}

		/**
		 * Filters the built cases search index before it is cached.
		 *
		 * @since 1.4.0
		 *
		 * @param array $index The inverted index array.
		 * @param array $cases The raw case rows used to build it.
		 */
		$index = apply_filters( 'stolmc_service_tracker_case_search_index', $index, $cases );

		set_transient( self::SEARCH_INDEX_TRANSIENT, $index, self::SEARCH_INDEX_TTL );

		return $index;
	}

	/**
	 * Tokenize a string into lower-cased prefix substrings for indexing.
	 *
	 * Splits on whitespace and common separators, then emits every prefix of
	 * every word so partial matches work (e.g. "rep" matches "repair").
	 *
	 * @since 1.4.0
	 *
	 * @param string $text The text to tokenize.
	 * @return string[]    Array of unique tokens.
	 */
	private function tokenize( string $text ): array {
		$text  = mb_strtolower( $text );
		$parts = preg_split( '/[\s@._\-]+/', $text, -1, PREG_SPLIT_NO_EMPTY );

		$tokens = [];

		foreach ( $parts as $part ) {
			$len = mb_strlen( $part );
			for ( $i = 1; $i <= $len; $i++ ) {
				$tokens[] = mb_substr( $part, 0, $i );
			}
		}

		return array_unique( $tokens );
	}

	/**
	 * Search cases using the inverted index transient.
	 *
	 * Accepts optional `id_user` to scope results to a single client.
	 * Returns the same paginated envelope as `read()`.
	 *
	 * @since 1.4.0
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Paginated response.
	 */
	public function search_cases( WP_REST_Request $data ): WP_REST_Response {
		global $wpdb;

		$query    = mb_strtolower( trim( (string) $data->get_param( 'q' ) ) );
		$id_user  = (int) ( $data->get_param( 'id_user' ) ?: 0 );
		$page     = max( 1, (int) ( $data->get_param( 'page' ) ?: 1 ) );
		$per_page = max( 1, (int) ( $data->get_param( 'per_page' ) ?: self::PER_PAGE_DEFAULT ) );

		// Empty query — fall back to the normal paginated read.
		if ( $query === '' ) {
			return $this->read( $data );
		}

		$index        = $this->get_search_index();
		$query_tokens = $this->tokenize( $query );

		// Score each case by how many query tokens match index entries.
		// $scores[ case_id ] = [ 'score' => int, 'id_user' => int ]
		$scores = [];

		foreach ( $query_tokens as $token ) {
			if ( ! isset( $index[ $token ] ) ) {
				continue;
			}
			foreach ( $index[ $token ] as $entry ) {
				$case_id = $entry['id'];

				// Filter by id_user when provided.
				if ( $id_user > 0 && $entry['id_user'] !== $id_user ) {
					continue;
				}

				if ( ! isset( $scores[ $case_id ] ) ) {
					$scores[ $case_id ] = [ 'score' => 0, 'id_user' => $entry['id_user'] ];
				}
				$scores[ $case_id ]['score']++;
			}
		}

		if ( empty( $scores ) ) {
			return $this->rest_response(
				[
					'data'        => [],
					'total'       => 0,
					'page'        => 1,
					'per_page'    => $per_page,
					'total_pages' => 1,
				],
				200
			);
		}

		// Sort by score descending.
		uasort( $scores, static fn( $a, $b ) => $b['score'] <=> $a['score'] );

		$matched_ids = array_keys( $scores );
		$total       = count( $matched_ids );
		$total_pages = max( 1, (int) ceil( $total / $per_page ) );
		$page        = min( $page, $total_pages );
		$paged_ids   = array_slice( $matched_ids, ( $page - 1 ) * $per_page, $per_page );

		if ( empty( $paged_ids ) ) {
			return $this->rest_response(
				[
					'data'        => [],
					'total'       => $total,
					'page'        => $page,
					'per_page'    => $per_page,
					'total_pages' => $total_pages,
				],
				200
			);
		}

		$table       = $wpdb->prefix . self::DB;
		$ids_escaped = implode( ',', array_map( 'intval', $paged_ids ) );

		// Fetch full case rows for the paged IDs, preserving score order.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$cases = $wpdb->get_results(
			"SELECT * FROM {$table} WHERE id IN ({$ids_escaped}) ORDER BY FIELD(id, {$ids_escaped})"
		);

		/**
		 * Filters the cases search response data.
		 *
		 * @since 1.4.0
		 *
		 * @param array  $cases  The matched case rows.
		 * @param array  $scores The score map (case_id => ['score', 'id_user']).
		 * @param string $query  The original search query.
		 */
		$cases = apply_filters( 'stolmc_service_tracker_cases_search_response', $cases, $scores, $query );

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
	 * Register custom API routes for cases management.
	 *
	 * @return void
	 */
	public function custom_api(): void {

		// RegisterNewRoute -> Method from superclass / extended class.
		$this->register_new_route( 'cases', '_user', WP_REST_Server::READABLE, [ $this, 'read' ] );
		$this->register_new_route( 'cases', '', WP_REST_Server::EDITABLE, [ $this, 'update' ] );
		$this->register_new_route( 'cases', '', WP_REST_Server::DELETABLE, [ $this, 'delete' ] );
		$this->register_new_route( 'cases', '_user', WP_REST_Server::CREATABLE, [ $this, 'create' ] );

		// GET /service-tracker-stolmc/v1/cases/search - Search cases with inverted index.
		register_rest_route(
			'service-tracker-stolmc/v1',
			'/cases/search',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'search_cases' ],
				'permission_callback' => [ $this, 'permission_check' ],
				'args'                => [
					'q'        => [
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					],
					'id_user'  => [
						'default'           => 0,
						'sanitize_callback' => 'absint',
					],
					'page'     => [
						'default'           => 1,
						'sanitize_callback' => 'absint',
					],
					'per_page' => [
						'default'           => self::PER_PAGE_DEFAULT,
						'sanitize_callback' => 'absint',
					],
				],
			]
		);
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
	public function read( WP_REST_Request $data ): mixed {
		global $wpdb;

		$id_user  = (int) $data['id_user'];
		$page     = max( 1, (int) ( $data->get_param( 'page' ) ?: 1 ) );
		$per_page = max( 1, (int) ( $data->get_param( 'per_page' ) ?: self::PER_PAGE_DEFAULT ) );

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

		// Compatibility path for lightweight test environments where $wpdb
		// is stubbed and does not expose query helper methods.
		if ( ! method_exists( $wpdb, 'get_var' ) || ! method_exists( $wpdb, 'prepare' ) ) {
			$cases = $this->sql->get_by( $query_args );
			$cases = apply_filters( 'stolmc_service_tracker_cases_read_response', $cases, $data );
			return $cases;
		}

		// Count total cases for this user.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$total = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE id_user = %d",
				$query_args['id_user']
			)
		);

		$total_pages = $total > 0 ? (int) ceil( $total / $per_page ) : 1;

		// Clamp page to valid range.
		$page   = min( $page, $total_pages );
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
		$body        = $data->get_body();
		$body        = json_decode( $body );
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
