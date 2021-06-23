<?php
namespace ServiceTracker\includes;

use ServiceTracker\includes\Service_Tracker_Sql;
use \WP_REST_Server;
use \WP_REST_Request;
use \WP_REST_Response;

class Service_Tracker_Api {

	/**
	 * This variable refers to either one of the three tables the plugin uses
	 *
	 * @var String -> 'cases', 'progress' or 'upload'
	 */
	public $api_type;

	/**
	 * This variable identifies a dynamic value that matches the unique user or case id
	 *
	 * @var String -> 'user' or 'case'
	 */
	public $api_argument;

	/**
	 * Database name
	 *
	 * @var String -> 'cases' or 'progress'
	 */
	public $db_name;

	/**
	 * Constructor for the Api custom routes endpoints
	 *
	 * @param String 'cases', 'progress' or 'upload' -> this must match the table name
	 * @param String 'user', 'case' -> dynamic value, it relates to the unique id of an user or a case
	 */
	public function __construct( $type, $required_argument ) {
		$this->api_type = $type;

		$this->db_name = $type;

		$this->api_argument = $required_argument;
	}

	public function custom_api() {

		// Routes for the API CRUD
		$this->register_new_route( '', 'api_argument', WP_REST_Server::READABLE, array( $this, 'read' ) );

		$this->register_new_route( '', '', WP_REST_Server::EDITABLE, array( $this, 'update' ) );

		$this->register_new_route( '', '', WP_REST_Server::DELETABLE, array( $this, 'delete' ) );

		$this->register_new_route( '', 'api_argument', WP_REST_Server::CREATABLE, array( $this, 'create' ) );

		// Route for the Toggle of the statuses of the cases
		$this->register_new_route( 'cases-status', '', WP_REST_Server::CREATABLE, array( $this, 'toggle_status' ) );
	}

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
			return new WP_REST_Response( 'Sorry, invalid credentials' );
		}
	}

	public function register_new_route( $api_type, $api_argument, $method, $callback ) {

		if ( $api_type === '' ) {
			$api_type = $this->api_type;
		}

		if ( $api_argument === 'api_argument' ) {
			$api_argument = $this->api_argument;
		}

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

	public function read( $data ) {

		$this->security_check( $data );

		$sql = new Service_Tracker_Sql( 'servicetracker_' . $this->db_name . '' );

		if ( $this->db_name === 'cases' ) {
			$response = $sql->get_by( array( 'id_user' => $data['id_user'] ) );
			return $response;
		}

		if ( $this->db_name === 'progress' ) {
			$response = $sql->get_by( array( 'id_case' => $data['id_case'] ) );
			return $response;
		}

	}

	public function create( WP_REST_Request $data ) {

		$this->security_check( $data );

		$body = $data->get_body();
		$body = json_decode( $body );

		$sql = new Service_Tracker_Sql( 'servicetracker_' . $this->db_name . '' );

		if ( $this->db_name === 'cases' ) {

			$id_user = $body->id_user;
			$title   = $body->title;

			return $sql->insert(
				array(
					'id_user' => $id_user,
					'title'   => $title,
					'status'  => 'open',
				)
			);
		}

		if ( $this->db_name === 'progress' ) {

			$id_user = $body->id_user;
			$id_case = $body->id_case;
			$text    = $body->text;

			return $sql->insert(
				array(
					'id_user' => $id_user,
					'id_case' => $id_case,
					'text'    => $text,
				)
			);
		}
	}

	public function update( $data ) {

		$this->security_check( $data );

		$body = $data->get_body();
		$body = json_decode( $body );

		$sql = new Service_Tracker_Sql( 'servicetracker_' . $this->db_name . '' );

		if ( $this->db_name === 'cases' ) {
			$title    = $body->title;
			$response = $sql->update(
				array( 'title' => $title ),
				array( 'id' => $data['id'] )
			);
		}

		if ( $this->db_name === 'progress' ) {
			$text     = $body->text;
			$response = $sql->update(
				array( 'text' => $text ),
				array( 'id' => $data['id'] )
			);
		}

	}

	public function delete( $data ) {
		$this->security_check( $data );

		$sql = new Service_Tracker_Sql( 'servicetracker_' . $this->db_name . '' );

		if ( $this->db_name === 'cases' ) {
			$delete = $sql->delete( array( 'id' => $data['id'] ) );
		}

		if ( $this->db_name === 'progress' ) {
			$delete = $sql->delete( array( 'id' => $data['id'] ) );
		}

	}

	public function toggle_status( $data ) {
		$this->security_check( $data );

		if ( $this->db_name === 'progress' ) {
			return;
		}

		$sql      = new Service_Tracker_Sql( 'servicetracker_cases' );
		$response = $sql->get_by( array( 'id' => $data['id'] ) );
		$response = (array) $response[0];

		if ( $response['status'] === 'open' ) {
			$toggle = $sql->update(
				array( 'status' => 'close' ),
				array( 'id' => $data['id'] )
			);

			return $toggle;
		}

		if ( $response['status'] === 'close' ) {
			$toggle = $sql->update(
				array( 'status' => 'open' ),
				array( 'id' => $data['id'] )
			);

			return $toggle;
		}
	}

}
