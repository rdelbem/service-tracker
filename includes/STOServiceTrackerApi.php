<?php
namespace ServiceTracker\includes;

use \WP_REST_Response;

class STOServiceTrackerApi
{

	public function userVerification()
	{
		return current_user_can('publish_posts');
	}

	public function securityCheck($data)
	{
		if (empty($data)) {
			return;
		}

		$headers = $data->get_headers();
		$nonce = $headers['x_wp_nonce'][0];

		if (!wp_verify_nonce($nonce, 'wp_rest')) {
			return new WP_REST_Response(__('Sorry, invalid credentials', 'service-tracker'));
		}
	}

	public function registerNewRoute($api_type, $api_argument, $method, $callback)
	{
		register_rest_route(
			'service-tracker/v1',
			'/' . $api_type . '/(?P<id' . $api_argument . '>\d+)',
			array(
				'methods' => $method,
				'callback' => $callback,
				'permission_callback' => array($this, 'userVerification'),
			)
		);
	}

}