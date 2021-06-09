<?php
namespace ServiceTracker\Api;

use ServiceTracker\Sql\Sql;

class Api {

	public function register_cases() {
		add_action(
			'rest_api_init',
			function() {
				register_rest_route(
					'service-tracker/v1',
					'/cases',
					array(
						'methods'  => 'GET',
						'callback' => array( $this, 'get_cases' ),
					)
				);
			}
		);
	}

	function get_cases() {
		$get_all = new Sql( 'servicetracker_cases' );

		return array( 'message' => $get_all->get_all() );
	}
}
