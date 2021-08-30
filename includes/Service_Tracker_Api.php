<?php
namespace ServiceTracker\includes;

use ServiceTracker\includes\Service_Tracker_Sql;
use ServiceTracker\includes\Service_Tracker_Mail;
use ServiceTracker\includes\Service_Tracker_Api_Cases;
use ServiceTracker\includes\Service_Tracker_Api_Progress;
use \WP_REST_Server;
use \WP_REST_Request;
use \WP_REST_Response;

class Service_Tracker_Api {

	public function user_verification() {
		return current_user_can( 'publish_posts' );
	}

	public function security_check( $data ) {
		if ( empty( $data ) ) {
			return;
		}

		$headers = $data->get_headers();
		$nonce   = $headers['x_wp_nonce'][0];

		if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new WP_REST_Response( __( 'Sorry, invalid credentials', 'service-tracker' ) );
		}
	}

	public function register_new_route( $api_type, $api_argument, $method, $callback ) {
		register_rest_route(
			'service-tracker/v1',
			'/' . $api_type . '/(?P<id' . $api_argument . '>\d+)',
			array(
				'methods'             => $method,
				'callback'            => $callback,
				'permission_callback' => array( $this, 'user_verification' ),
			)
		);
	}

}
