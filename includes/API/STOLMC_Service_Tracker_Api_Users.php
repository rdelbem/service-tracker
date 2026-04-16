<?php
namespace STOLMC_Service_Tracker\includes\API;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_User;

/**
 * This class handles user-related REST API operations.
 *
 * Specifically, creating new customer users from the admin interface.
 *
 * ENDPOINT => wp-json/service-tracker-stolmc/v1/users
 */
class STOLMC_Service_Tracker_Api_Users extends STOLMC_Service_Tracker_Api {

	/**
	 * Number of users returned per page by default.
	 *
	 * @since 1.3.0
	 */
	private const PER_PAGE_DEFAULT = 6;

	/**
	 * Transient key for the user search inverted index.
	 *
	 * @since 1.4.0
	 */
	private const SEARCH_INDEX_TRANSIENT = 'stolmc_st_user_search_index';

	/**
	 * How long (in seconds) the search index transient lives.
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
		$this->custom_api();
		$this->register_index_invalidation_hooks();
	}

	/**
	 * Register hooks that bust the search index transient when user data changes.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	private function register_index_invalidation_hooks(): void {
		// Bust on any user profile save or registration.
		add_action( 'user_register', [ $this, 'bust_search_index' ] );
		add_action( 'profile_update', [ $this, 'bust_search_index' ] );
		add_action( 'deleted_user', [ $this, 'bust_search_index' ] );

		// Also bust when our own create endpoint fires.
		add_action( 'stolmc_service_tracker_user_created', [ $this, 'bust_search_index' ] );
	}

	/**
	 * Delete the cached search index transient.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	public function bust_search_index(): void {
		delete_transient( self::SEARCH_INDEX_TRANSIENT );
	}

	/**
	 * Build (or retrieve from cache) the inverted search index.
	 *
	 * Structure:
	 * [
	 *   'token' => [ user_id, user_id, ... ],
	 *   ...
	 * ]
	 *
	 * Tokens are lower-cased substrings derived from display_name and user_email.
	 *
	 * @since 1.4.0
	 *
	 * @return array<string, int[]> The inverted index.
	 */
	private function get_search_index(): array {
		$cached = get_transient( self::SEARCH_INDEX_TRANSIENT );

		if ( is_array( $cached ) ) {
			return $cached;
		}

		// Fetch all customer users — no pagination, we need the full set for indexing.
		$users = get_users(
			[
				'role'    => 'customer',
				'orderby' => 'display_name',
				'order'   => 'ASC',
				'fields'  => [ 'ID', 'display_name', 'user_email' ],
			]
		);

		$index = [];

		foreach ( $users as $user ) {
			$id     = (int) $user->ID;
			$tokens = $this->tokenize( $user->display_name . ' ' . $user->user_email );

			foreach ( $tokens as $token ) {
				if ( ! isset( $index[ $token ] ) ) {
					$index[ $token ] = [];
				}
				if ( ! in_array( $id, $index[ $token ], true ) ) {
					$index[ $token ][] = $id;
				}
			}
		}

		/**
		 * Filters the built search index before it is cached.
		 *
		 * @since 1.4.0
		 *
		 * @param array $index The inverted index array.
		 * @param array $users The raw WP_User objects used to build it.
		 */
		$index = apply_filters( 'stolmc_service_tracker_user_search_index', $index, $users );

		set_transient( self::SEARCH_INDEX_TRANSIENT, $index, self::SEARCH_INDEX_TTL );

		return $index;
	}

	/**
	 * Tokenize a string into lower-cased substrings for indexing.
	 *
	 * Splits on whitespace and common separators, then also adds the full
	 * word so prefix searches work (e.g. "jo" matches "john").
	 *
	 * @since 1.4.0
	 *
	 * @param string $text The text to tokenize.
	 * @return string[]    Array of unique tokens.
	 */
	private function tokenize( string $text ): array {
		$text   = mb_strtolower( $text );
		$parts  = preg_split( '/[\s@._\-]+/', $text, -1, PREG_SPLIT_NO_EMPTY );
		$tokens = [];

		foreach ( $parts as $part ) {
			// Add every prefix of the word so partial matches work.
			$len = mb_strlen( $part );
			for ( $i = 1; $i <= $len; $i++ ) {
				$tokens[] = mb_substr( $part, 0, $i );
			}
		}

		return array_unique( $tokens );
	}

	/**
	 * Search customer users using the inverted index transient.
	 *
	 * @since 1.4.0
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Paginated response matching the standard envelope.
	 */
	public function search_users( WP_REST_Request $data ): WP_REST_Response {
		$query    = mb_strtolower( trim( (string) $data->get_param( 'q' ) ) );
		$page     = max( 1, (int) ( $data->get_param( 'page' ) ?: 1 ) );
		$per_page = max( 1, (int) ( $data->get_param( 'per_page' ) ?: self::PER_PAGE_DEFAULT ) );

		// Empty query — fall back to the normal paginated list.
		if ( $query === '' ) {
			return $this->get_users( $data );
		}

		$index        = $this->get_search_index();
		$query_tokens = $this->tokenize( $query );

		// Score each user by how many query tokens match index entries.
		$scores = [];

		foreach ( $query_tokens as $token ) {
			if ( isset( $index[ $token ] ) ) {
				foreach ( $index[ $token ] as $user_id ) {
					$scores[ $user_id ] = ( $scores[ $user_id ] ?? 0 ) + 1;
				}
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

		// Sort by score descending so best matches come first.
		arsort( $scores );
		$matched_ids = array_keys( $scores );

		$total       = count( $matched_ids );
		$total_pages = max( 1, (int) ceil( $total / $per_page ) );
		$page        = min( $page, $total_pages );
		$paged_ids   = array_slice( $matched_ids, ( $page - 1 ) * $per_page, $per_page );

		// Fetch full user objects for the paged IDs.
		$users = get_users(
			[
				'include' => $paged_ids,
				'orderby' => 'include', // Preserve score order.
			]
		);

		$user_data = [];

		foreach ( $users as $user ) {
			$user_data[] = [
				'id'         => $user->ID,
				'name'       => $user->display_name,
				'email'      => $user->user_email,
				'role'       => 'customer',
				'phone'      => get_user_meta( $user->ID, 'phone', true ),
				'cellphone'  => get_user_meta( $user->ID, 'cellphone', true ),
				'created_at' => $user->user_registered,
			];
		}

		/**
		 * Filters the search results before they are returned.
		 *
		 * @since 1.4.0
		 *
		 * @param array $user_data   The matched user data.
		 * @param array $scores      The score map (user_id => score).
		 * @param string $query      The original search query.
		 */
		$user_data = apply_filters( 'stolmc_service_tracker_user_search_response', $user_data, $scores, $query );

		return $this->rest_response(
			[
				'data'        => $user_data,
				'total'       => $total,
				'page'        => $page,
				'per_page'    => $per_page,
				'total_pages' => $total_pages,
			],
			200
		);
	}

	/**
	 * Register custom API routes for user management.
	 *
	 * @return void
	 */
	public function custom_api(): void {

		// GET /service-tracker-stolmc/v1/users - Get all customer users (paginated).
		register_rest_route(
			'service-tracker-stolmc/v1',
			'/users',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_users' ],
				'permission_callback' => [ $this, 'permission_check' ],
				'args'                => [
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

		// POST /service-tracker-stolmc/v1/users - Create a new customer user.
		register_rest_route(
			'service-tracker-stolmc/v1',
			'/users',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'create' ],
				'permission_callback' => [ $this, 'permission_check' ],
			]
		);

		// GET /service-tracker-stolmc/v1/users/search - Search customer users.
		register_rest_route(
			'service-tracker-stolmc/v1',
			'/users/search',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'search_users' ],
				'permission_callback' => [ $this, 'permission_check' ],
				'args'                => [
					'q'        => [
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
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

		// GET /service-tracker-stolmc/v1/users/staff - Get all staff/admin users.
		register_rest_route(
			'service-tracker-stolmc/v1',
			'/users/staff',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_staff_users' ],
				'permission_callback' => [ $this, 'permission_check' ],
			]
		);
	}

	/**
	 * Get paginated customer users.
	 *
	 * Accepts `page` (1-based) and `per_page` query parameters.
	 * Returns a paginated envelope so the frontend can render page controls.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Response with paginated user data.
	 */
	public function get_users( WP_REST_Request $data ): WP_REST_Response {
		$page     = max( 1, (int) ( $data->get_param( 'page' ) ?: 1 ) );
		$per_page = max( 1, (int) ( $data->get_param( 'per_page' ) ?: self::PER_PAGE_DEFAULT ) );

		// Count query — fetch only IDs to get the total efficiently.
		$count_args = [
			'role'    => 'customer',
			'fields'  => 'ids',
			'orderby' => 'display_name',
			'order'   => 'ASC',
		];

		/**
		 * Filters the query arguments used for counting customer users.
		 *
		 * @since 1.3.0
		 *
		 * @param array $count_args The user query arguments for counting.
		 */
		$count_args = apply_filters( 'stolmc_service_tracker_get_users_count_args', $count_args );

		$all_ids     = get_users( $count_args );
		$total       = count( $all_ids );
		$total_pages = $total > 0 ? (int) ceil( $total / $per_page ) : 1;

		// Clamp page to valid range.
		$page = min( $page, max( 1, $total_pages ) );

		$args = [
			'role'    => 'customer',
			'orderby' => 'display_name',
			'order'   => 'ASC',
			'number'  => $per_page,
			'offset'  => ( $page - 1 ) * $per_page,
		];

		/**
		 * Filters the query arguments for fetching customer users.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args The user query arguments.
		 */
		$args = apply_filters( 'stolmc_service_tracker_get_users_args', $args );

		$users     = get_users( $args );
		$user_data = [];

		foreach ( $users as $user ) {
			$user_data[] = [
				'id'         => $user->ID,
				'name'       => $user->display_name,
				'email'      => $user->user_email,
				'role'       => 'customer',
				'phone'      => get_user_meta( $user->ID, 'phone', true ),
				'cellphone'  => get_user_meta( $user->ID, 'cellphone', true ),
				'created_at' => $user->user_registered,
			];
		}

		/**
		 * Filters the users response data.
		 *
		 * @since 1.0.0
		 *
		 * @param array     $user_data The user data array.
		 * @param WP_User[] $users     The WP_User objects.
		 */
		$user_data = apply_filters( 'stolmc_service_tracker_users_response', $user_data, $users );

		return $this->rest_response(
			[
				'data'        => $user_data,
				'total'       => $total,
				'page'        => $page,
				'per_page'    => $per_page,
				'total_pages' => $total_pages,
			],
			200
		);
	}

	/**
	 * Create a new customer user.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Response indicating success or failure.
	 */
	public function create( WP_REST_Request $data ): WP_REST_Response {
		$body = $data->get_body();
		$body = json_decode( $body );

		// Validate required fields.
		if ( empty( $body->name ) || empty( $body->email ) ) {
			return new WP_REST_Response(
				[
					'success' => false,
					'message' => 'Name and email are required',
				],
				400
			);
		}

		// Check if user with this email already exists.
		$existing_user = get_user_by( 'email', $body->email );
		if ( $existing_user ) {
			return $this->rest_response(
				[
					'success' => false,
					'message' => 'A user with this email already exists',
				],
				409
			);
		}

		// Generate a random password.
		$password = wp_generate_password( 12, true, true );

		/**
		 * Filters the generated password before user creation.
		 *
		 * @since 1.0.0
		 *
		 * @param string $password The generated password.
		 */
		$password = apply_filters( 'stolmc_service_tracker_user_password', $password );

		// Prepare user data.
		$user_data = [
			'user_login'   => sanitize_user( $body->name ),
			'user_email'   => sanitize_email( $body->email ),
			'display_name' => sanitize_text_field( $body->name ),
			'user_pass'    => $password,
			'role'         => 'customer',
			'first_name'   => sanitize_text_field( $body->name ),
		];

		/**
		 * Filters the user data before creation.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $user_data The user data to insert.
		 * @param object $body      The raw request body.
		 * @param string $password  The user password.
		 */
		$user_data = apply_filters( 'stolmc_service_tracker_user_create_data', $user_data, $body, $password );

		// Create the user.
		$user_id = wp_insert_user( $user_data );

		if ( is_wp_error( $user_id ) ) {
			return $this->rest_response(
				[
					'success' => false,
					'message' => $user_id->get_error_message(),
				],
				500
			);
		}

		// Store phone as user meta if provided.
		$meta_data = [];
		if ( ! empty( $body->phone ) ) {
			update_user_meta( $user_id, 'phone', sanitize_text_field( $body->phone ) );
			$meta_data['phone'] = sanitize_text_field( $body->phone );
		}

		// Store cellphone as user meta if provided.
		if ( ! empty( $body->cellphone ) ) {
			update_user_meta( $user_id, 'cellphone', sanitize_text_field( $body->cellphone ) );
			$meta_data['cellphone'] = sanitize_text_field( $body->cellphone );
		}

		/**
		 * Fires after a user has been created.
		 *
		 * @since 1.0.0
		 *
		 * @param int    $user_id   The ID of the created user.
		 * @param array  $user_data The user data that was inserted.
		 * @param object $body      The raw request body.
		 * @param string $password  The user password.
		 */
		do_action( 'stolmc_service_tracker_user_created', $user_id, $user_data, $body, $password );

		if ( ! empty( $meta_data ) ) {
			/**
			 * Fires after user meta data has been updated.
			 *
			 * @since 1.0.0
			 *
			 * @param int   $user_id   The ID of the created user.
			 * @param array $meta_data The meta data that was saved.
			 */
			do_action( 'stolmc_service_tracker_user_created_with_meta', $user_id, $meta_data );
		}

		// Return the created user data.
		$user = get_user_by( 'id', $user_id );

		if ( false === $user ) {
			return $this->rest_response(
				[
					'success' => false,
					'message' => 'User created but could not retrieve data',
				],
				500
			);
		}

		return $this->rest_response(
			[
				'success' => true,
				'message' => 'User created successfully',
				'user'    => [
					'id'         => $user->ID,
					'name'       => $user->display_name,
					'email'      => $user->user_email,
					'role'       => 'customer',
					'phone'      => get_user_meta( $user_id, 'phone', true ),
					'cellphone'  => get_user_meta( $user_id, 'cellphone', true ),
					'created_at' => $user->user_registered,
				],
			],
			201
		);
	}

	/**
	 * Get all staff and admin users.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Response with user data.
	 */
	public function get_staff_users( WP_REST_Request $data ): WP_REST_Response {
		// Get users with staff or administrator role.
		$args = [
			'role__in' => [ 'staff', 'administrator' ],
			'orderby'  => 'display_name',
			'order'    => 'ASC',
		];

		/**
		 * Filters the query arguments for fetching staff users.
		 *
		 * @since 1.2.0
		 *
		 * @param array $args The user query arguments.
		 */
		$args = apply_filters( 'stolmc_service_tracker_get_staff_users_args', $args );

		$users     = get_users( $args );
		$user_data = [];

		foreach ( $users as $user ) {
			$user_data[] = [
				'id'         => $user->ID,
				'name'       => $user->display_name,
				'email'      => $user->user_email,
				'role'       => $user->roles[0] ?? 'staff',
				'created_at' => $user->user_registered,
			];
		}

		/**
		 * Filters the staff users response data.
		 *
		 * @since 1.2.0
		 *
		 * @param array $user_data The users data.
		 */
		$user_data = apply_filters( 'stolmc_service_tracker_get_staff_users_response', $user_data );

		return $this->rest_response( $user_data, 200 );
	}
}
