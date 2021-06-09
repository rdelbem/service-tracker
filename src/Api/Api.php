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
					'/cases/(?P<id_user>\d+)',
					array(
						'methods'  => 'GET',
						'callback' => array( $this, 'get_cases' ),
					)
				);
			}
		);
	}

	public function register_progress() {
		add_action(
			'rest_api_init',
			function() {
				register_rest_route(
					'service-tracker/v1',
					'/progress/(?P<id_case>\d+)',
					array(
						'methods'  => 'GET',
						'callback' => array( $this, 'get_progress' ),
					)
				);
			}
		);
	}

	function get_cases( $data ) {
		$sql = new Sql( 'servicetracker_cases' );

		return $sql->get_by( array( 'id_user' => $data['id_user'] ) );
	}

	function get_progress( $data ) {
		$sql = new Sql( 'servicetracker_progress' );

		return $sql->get_by( array( 'id_case' => $data['id_case'] ) );
	}


}
