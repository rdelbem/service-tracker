<?php
namespace STOLMC_Service_Tracker\includes\API;

use WP_Error;
use WP_REST_Response;
use WP_REST_Request;

/**
 * Base API class for Service Tracker REST endpoints.
 *
 * Provides common functionality for all API classes including
 * user verification, security checks, and route registration.
 */
class STOLMC_Service_Tracker_Api {

	/**
	 * Central permission check for all routes.
	 *
	 * @param WP_REST_Request $request REST request.
	 *
	 * @return bool|WP_Error
	 */
	public function permission_check( WP_REST_Request $request ): bool|WP_Error {
		$current_user_id = get_current_user_id();

		$user_can = apply_filters(
			'stolmc_service_tracker_api_user_can',
			current_user_can( 'publish_posts' ),
			$current_user_id
		);

		if ( ! $user_can ) {
			return new WP_Error(
				'stolmc_service_tracker_forbidden',
				__( 'Sorry, you are not allowed to access this endpoint.', 'service-tracker-stolmc' ),
				[ 'status' => 403 ]
			);
		}

		$headers = $request->get_headers();
		$nonce   = $headers['x_wp_nonce'][0] ?? '';

		if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			$error = new WP_Error(
				'stolmc_service_tracker_invalid_nonce',
				__( 'Sorry, invalid credentials.', 'service-tracker-stolmc' ),
				[ 'status' => 401 ]
			);

			return apply_filters(
				'stolmc_service_tracker_api_permission_check',
				$error,
				$request
			);
		}

		return true;
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
			'permission_callback' => [ $this, 'permission_check' ],
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

	/**
	 * Create a REST response.
	 *
	 * @param array<string, mixed> $data The response data.
	 * @param int                  $status The HTTP status code.
	 *
	 * @return WP_REST_Response
	 */
	public function rest_response( array $data, int $status ): WP_REST_Response {
		return new WP_REST_Response( $data, $status );
	}
}
