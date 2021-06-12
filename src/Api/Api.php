<?php
namespace ServiceTracker\Api;

use ServiceTracker\Sql\Sql;
use \WP_REST_Server;
use \WP_REST_Request;

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
				'permission_callback' => function () {
					return current_user_can( 'publish_posts' );
				},
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
				'permission_callback' => function () {
					return current_user_can( 'publish_posts' );
				},
			)
		);
	}

	function create( $data ) {
		/*
			  if ( ! check_ajax_referer( 'wp_rest', 'X-WP-Nonce', false ) ) {
			return 'Sorry, you are not authorized!';
		} */

		if ( empty( $data ) ) {
			return;
		}

		try {

			$sql = new Sql( 'servicetracker_' . $this->db_name . '' );

			if ( $this->db_name === 'cases' ) {
				return $sql->insert(
					array(
						'id_user' => 12,
						'title'   => 'olá',
					)
				);
			}

			if ( $this->db_name === 'progress' ) {
				return $sql->insert( $data );
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
