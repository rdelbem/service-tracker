<?php
namespace STOLMC_Service_Tracker\includes\API;

use WP_REST_Response;

/**
 * Base API class for Service Tracker REST endpoints.
 *
 * Provides common functionality for all API classes including
 * user verification, security checks, and route registration.
 */
class STOLMC_Service_Tracker_Api {

	/**
	 * Verify the current user has permission to access the API.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return bool True if user can publish posts, false otherwise.
	 */
	public function user_verification(): bool {
		$current_user_id = get_current_user_id();

		/**
		 * Filters whether the current user has permission to access the API.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $result          Whether the user can publish posts.
		 * @param int  $current_user_id The current user ID.
		 */
		return apply_filters( 'stolmc_service_tracker_api_user_can', current_user_can( 'publish_posts' ), $current_user_id );
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
	 * @return WP_REST_Response|null Returns error response if nonce is invalid.
	 */
	public function security_check( mixed $data ): ?WP_REST_Response {
		if ( empty( $data ) ) {
			return null;
		}

		$headers = $data->get_headers();
		$nonce   = $headers['x_wp_nonce'][0];

		if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			$error_response = new WP_REST_Response( __( 'Sorry, invalid credentials', 'service-tracker-stolmc' ) );

			/**
			 * Filters the security check error response.
			 *
			 * @since 1.0.0
			 *
			 * @param WP_REST_Response|null $error_response The error response.
			 * @param mixed                 $data           The REST request object.
			 */
			return apply_filters( 'stolmc_service_tracker_api_security_check', $error_response, $data );
		}

		return null;
	}

	/**
	 * Register a new REST route.
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param string               $api_type     The API endpoint type (e.g., 'cases', 'progress').
	 * @param string               $api_argument The API argument pattern (e.g., '', '_user', '_case').
	 * @param string               $method       The HTTP methods allowed (e.g., WP_REST_Server::READABLE).
	 * @param array{object, string} $callback     The callback function to execute.
	 *
	 * @return void
	 */
	public function register_new_route( string $api_type, string $api_argument, string $method, array $callback ): void {
		$route_args = [
			'methods'             => $method,
			'callback'            => $callback,
			'permission_callback' => [ $this, 'user_verification' ],
		];

		/**
		 * Filters the REST route arguments before registration.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $route_args   The route arguments.
		 * @param string $api_type     The API endpoint type.
		 * @param string $api_argument The API argument pattern.
		 */
		$route_args = apply_filters( 'stolmc_service_tracker_api_route_args', $route_args, $api_type, $api_argument );

		register_rest_route(
			'service-tracker-stolmc/v1',
			'/' . $api_type . '/(?P<id' . $api_argument . '>\d+)',
			$route_args
		);

		/**
		 * Fires after a REST route has been registered.
		 *
		 * @since 1.0.0
		 *
		 * @param string $api_type     The API endpoint type.
		 * @param string $api_argument The API argument pattern.
		 * @param string $method       The HTTP methods allowed.
		 * @param array  $callback     The callback function.
		 */
		do_action( 'stolmc_service_tracker_api_route_registered', $api_type, $api_argument, $method, $callback );
	}
}
