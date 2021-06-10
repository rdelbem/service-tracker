<?php
namespace ServiceTracker\Api;

use ServiceTracker\Sql\Sql;

class Api {

	public $api_type;

	public $api_argument;

	function __construct( $type, $required_argument ) {
		$this->api_type = $type;

		$this->api_argument = $required_argument;
	}

	public function register_api() {
		add_action( 'rest_api_init', array( $this, 'custom_api' ) );
	}

	public function custom_api() {
		register_rest_route(
			'service-tracker/v1',
			'/' . $this->api_type . '/(?P<id_' . $this->api_argument . '>\d+)',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'get_from_db' ),
			),
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'post_' . $this->api_type . '' ),
			)
		);
	}

	public function get_from_db( $data ) {
		$db_name = $this->api_type;

		$sql = new Sql( 'servicetracker_' . $db_name . '' );

		if ( $db_name === 'cases' ) {
			return $sql->get_by( array( 'id_user' => $data['id_user'] ) );
		}

		if ( $db_name === 'progress' ) {
			return $sql->get_by( array( 'id_case' => $data['id_case'] ) );
		}

	}


	function post_cases( $data ) {
		return 123;
	}

	function post_progress( $data ) {

	}


}
