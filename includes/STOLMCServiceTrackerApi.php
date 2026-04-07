<?php
namespace STOLMCServiceTracker\includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use WP_REST_Response;

/**
 * Base API class for Service Tracker REST endpoints.
 *
 * Provides common functionality for all API classes including
 * user verification, security checks, and route registration.
 */
class STOLMCServiceTrackerApi {

	/**
	 * Verify the current user has permission to access the API.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return bool True if user can publish posts, false otherwise.
	 */
	public function user_verification() {
		return current_user_can( 'publish_posts' );
	}

	/**
	 * Perform security check on the REST request.
	 *
	 * Verifies the nonce in the request headers.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param mixed $data The REST request object or data to check.
	 *
	 * @return WP_REST_Response|void Returns error response if nonce is invalid.
	 */
	public function security_check( $data ) {
		if ( empty( $data ) ) {
			return;
		}

		$headers = $data->get_headers();
		$nonce   = $headers['x_wp_nonce'][0];

		if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new WP_REST_Response( __( 'Sorry, invalid credentials', 'service-tracker-stolmc' ) );
		}
	}

	/**
	 * Register a new REST route.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param string $api_type     The API endpoint type (e.g., 'cases', 'progress').
	 * @param string $api_argument The API argument pattern (e.g., '', '_user', '_case').
	 * @param string $method       The HTTP methods allowed (e.g., WP_REST_Server::READABLE).
	 * @param array  $callback     The callback function to execute.
	 *
	 * @return void
	 */
	public function register_new_route( $api_type, $api_argument, $method, $callback ) {
		register_rest_route(
			'service-tracker-stolmc/v1',
			'/' . $api_type . '/(?P<id' . $api_argument . '>\d+)',
			[
				'methods'             => $method,
				'callback'            => $callback,
				'permission_callback' => [ $this, 'user_verification' ],
			]
		);
	}
}
