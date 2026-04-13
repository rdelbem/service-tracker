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
	 * Initialize the API and register routes.
	 *
	 * @return void
	 */
	public function run(): void {
		$this->custom_api();
	}

	/**
	 * Register custom API routes for user management.
	 *
	 * @return void
	 */
	public function custom_api(): void {

		// GET /service-tracker-stolmc/v1/users - Get all customer users.
		register_rest_route(
			'service-tracker-stolmc/v1',
			'/users',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_users' ],
				'permission_callback' => [ $this, 'permission_check' ],
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
	 * Get all customer users.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Response with user data.
	 */
	public function get_users( WP_REST_Request $data ): WP_REST_Response {
		// Get all users with customer role.
		$args = [
			'role'    => 'customer',
			'orderby' => 'display_name',
			'order'   => 'ASC',
		];

		/**
		 * Filters the query arguments for fetching customer users.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args The user query arguments.
		 */
		$args = apply_filters( 'stolmc_service_tracker_get_users_args', $args );

		$users      = get_users( $args );
		$user_data  = [];

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
		 * @param WP_User[] $users    The WP_User objects.
		 */
		$user_data = apply_filters( 'stolmc_service_tracker_users_response', $user_data, $users );

		return $this->rest_response( $user_data, 200 );
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
