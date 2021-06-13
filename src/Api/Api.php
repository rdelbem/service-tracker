<?php
namespace ServiceTracker\Api;

use ServiceTracker\Sql\Sql;
use \WP_REST_Server;
use \WP_REST_Request;
use \WP_REST_Response;

// ver isso https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/#permissions-callback

class Api {

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

	public function register_api() {
		add_action( 'rest_api_init', array( $this, 'custom_api' ) );
	}

	public function custom_api() {
		register_rest_route(
			'service-tracker/v1',
			'/' . $this->api_type . '/(?P<id_' . $this->api_argument . '>\d+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'read' ),
				'permission_callback' => array( $this, 'user_verification' ),
			)/*
			,
			array(
			'methods'  => WP_REST_Server::EDITABLE,
			'callback' => array( $this, 'update' ),
			),
			array(
			'methods'  => WP_REST_Server::DELETABLE,
			'callback' => array( $this, 'delete' ),
			) */
		);

		register_rest_route(
			'service-tracker/v1',
			'/' . $this->api_type . '/(?P<id_' . $this->api_argument . '>\d+)',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create' ),
				'permission_callback' => array( $this, 'user_verification' ),
			)
		);
	}

	function user_verification() {
		return current_user_can( 'publish_posts' );
	}

	function create( WP_REST_Request $data ) {

		if ( empty( $data ) ) {
			return;
		}

		$headers = $data->get_headers();
		$nonce   = $headers['x_wp_nonce'][0];

		if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new WP_REST_Response( 'Sorry, invalid credentials', 422 );
		}

		$body          = $data->get_body();
		$json_to_array = json_decode( $body );
		$id_user       = $json_to_array->id_user;
		$title         = $json_to_array->title;

		try {

			$sql = new Sql( 'servicetracker_' . $this->db_name . '' );

			if ( $this->db_name === 'cases' ) {
				return $sql->insert(
					array(
						'id_user' => $id_user,
						'title'   => $title,
					)
				);
			}

			//verificar isso
			if ( $this->db_name === 'progress' ) {
				return $sql->insert(
					array(
						'id_user' => $id_user,
						'title'   => $title,
					)
				);
			}
		} catch ( Exception $error ) {
			return $error;
		}

	}

	public function read( $data ) {
		if ( empty( $data ) ) {
			return;
		}

		$sql = new Sql( 'servicetracker_' . $this->db_name . '' );

		if ( $this->db_name === 'cases' ) {
			try {
				$response = $sql->get_by( array( 'id_user' => $data['id_user'] ) );
				return $response;
			} catch ( Exception $error ) {
				return 'Unfortunetlly, an error ocurred: ' . $error;
			}
		}

		if ( $this->db_name === 'progress' ) {
			try {
				$response = $sql->get_by( array( 'id_case' => $data['id_case'] ) );
				return $response;
			} catch ( Exception $error ) {
				return 'Unfortunetlly, an error ocurred: ' . $error;
			}
		}

	}


	function update( $data ) {

	}

	function delete( $data ) {

	}

}
