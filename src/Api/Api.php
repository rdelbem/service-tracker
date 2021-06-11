<?php
namespace ServiceTracker\Api;

use ServiceTracker\Sql\Sql;
use \WP_REST_Server;

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


	public $nonce;

	/**
	 * Constructor for the Api custom routes endpoints
	 *
	 * @param String 'cases', 'progress' or 'upload' -> this must match the table name
	 * @param String 'user', 'case' -> dynamic value, it relates to the unique id of an user or a case
	 */
	public function __construct( $type, $required_argument, $nonce ) {
		$this->api_type = $type;

		$this->db_name = $type;

		$this->api_argument = $required_argument;

		$this->nonce = $nonce;
	}

	public function register_api() {
		add_action( 'rest_api_init', array( $this, 'custom_api' ) );
	}

	public function custom_api() {
		register_rest_route(
			'service-tracker/v1',
			'/' . $this->api_type . '/(?P<id_' . $this->api_argument . '>\d+)',
			array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $this, 'read' ),
			),
			array(
				'methods'  => WP_REST_Server::CREATABLE,
				'callback' => array( $this, 'create' ),
			),
			array(
				'methods'  => WP_REST_Server::EDITABLE,
				'callback' => array( $this, 'update' ),
			),
			array(
				'methods'  => WP_REST_Server::DELETABLE,
				'callback' => array( $this, 'delete' ),
			)
		);
	}

	function create( $data ) {

		! is_array( $data ) ? $data_array = array( $data ) : $data_array = $data;

		$sql = new Sql( 'servicetracker_' . $this->db_name . '' );

		if ( $this->db_name === 'cases' ) {
			return 123;
			// return $sql->insert( $data_array );
		}

		if ( $this->db_name === 'progress' ) {
			return $sql->insert( $data_array );
		}

	}

	public function read( $data ) {

		$sql = new Sql( 'servicetracker_' . $this->db_name . '' );

		if ( $this->db_name === 'cases' ) {
			return $sql->get_by( array( 'id_user' => $data['id_user'] ) );
		}

		if ( $this->db_name === 'progress' ) {
			return $sql->get_by( array( 'id_case' => $data['id_case'] ) );
		}

	}


	function update( $data ) {

	}

	function delete( $data ) {

	}

}
