<?php
namespace STOLMCServiceTracker\includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use STOLMCServiceTracker\includes\STOLMCServiceTrackerApi;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * This class handles user-related REST API operations.
 *
 * Specifically, creating new customer users from the admin interface.
 *
 * ENDPOINT => wp-json/service-tracker-stolmc/v1/users
 */
class STOLMCServiceTrackerApiUsers extends STOLMCServiceTrackerApi {

	/**
	 * Initialize the API and register routes.
	 *
	 * @return void
	 */
	public function run() {
		$this->custom_api();
	}

	/**
	 * Register custom API routes for user management.
	 *
	 * @return void
	 */
	public function custom_api() {

		// GET /service-tracker-stolmc/v1/users - Get all customer users.
		register_rest_route(
			'service-tracker-stolmc/v1',
			'/users',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_users' ],
				'permission_callback' => [ $this, 'user_verification' ],
			]
		);

		// POST /service-tracker-stolmc/v1/users - Create a new customer user.
		register_rest_route(
			'service-tracker-stolmc/v1',
			'/users',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'create' ],
				'permission_callback' => [ $this, 'user_verification' ],
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
	public function get_users( WP_REST_Request $data ) {

		// Security check.
		$security_result = $this->security_check( $data );
		if ( $security_result instanceof WP_REST_Response ) {
			return $security_result;
		}

		// Get all users with customer role.
		$args = [
			'role'    => 'customer',
			'orderby' => 'display_name',
			'order'   => 'ASC',
		];

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

		return new WP_REST_Response( $user_data, 200 );
	}

	/**
	 * Create a new customer user.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Response indicating success or failure.
	 */
	public function create( WP_REST_Request $data ) {

		// Security check.
		$security_result = $this->security_check( $data );
		if ( $security_result instanceof WP_REST_Response ) {
			return $security_result;
		}

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
			return new WP_REST_Response(
				[
					'success' => false,
					'message' => 'A user with this email already exists',
				],
				409
			);
		}

		// Generate a random password.
		$password = wp_generate_password( 12, true, true );

		// Prepare user data.
		$user_data = [
			'user_login'   => sanitize_user( $body->name ),
			'user_email'   => sanitize_email( $body->email ),
			'display_name' => sanitize_text_field( $body->name ),
			'user_pass'    => $password,
			'role'         => 'customer',
			'first_name'   => sanitize_text_field( $body->name ),
		];

		// Optionally add phone if provided.
		if ( ! empty( $body->phone ) ) {
			$user_data['phone'] = sanitize_text_field( $body->phone );
		}

		// Create the user.
		$user_id = wp_insert_user( $user_data );

		if ( is_wp_error( $user_id ) ) {
			return new WP_REST_Response(
				[
					'success' => false,
					'message' => $user_id->get_error_message(),
				],
				500
			);
		}

		// Store phone as user meta if provided.
		if ( ! empty( $body->phone ) ) {
			update_user_meta( $user_id, 'phone', sanitize_text_field( $body->phone ) );
		}

		// Store cellphone as user meta if provided.
		if ( ! empty( $body->cellphone ) ) {
			update_user_meta( $user_id, 'cellphone', sanitize_text_field( $body->cellphone ) );
		}

		// Return the created user data.
		$user = get_user_by( 'id', $user_id );

		return new WP_REST_Response(
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
}
