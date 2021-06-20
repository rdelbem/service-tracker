<?php
namespace ServiceTracker\Api;

use ServiceTracker\Sql\Sql;
use \WP_REST_Server;
use \WP_REST_Request;
use \WP_REST_Response;

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
			return new WP_REST_Response( 'Sorry, invalid credentials', 422 );
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

	public function create( WP_REST_Request $data ) {

		$this->security_check( $data );

		$body = $data->get_body();
		$body = json_decode( $body );

		try {

			$sql = new Sql( 'servicetracker_' . $this->db_name . '' );

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
		} catch ( Exception $error ) {
			return $error;
		}

	}

	public function read( $data ) {

		$this->security_check( $data );

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

	public function update( $data ) {

		$this->security_check( $data );

		$body = $data->get_body();
		$body = json_decode( $body );

		$sql = new Sql( 'servicetracker_' . $this->db_name . '' );

		if ( $this->db_name === 'cases' ) {

			$title = $body->title;

			try {

				$response = $sql->update(
					array(
						'title' => $title,
					),
					array(
						'id' => $data['id'],
					)
				);

				if ( $response === 0 ) {
					return 'Error: couldn\'t update title at ' . $data['id'];
				}

				if ( $response === 1 ) {
					return 'Success: title updated at ' . $data['id'];
				}
			} catch ( Exception $error ) {
				return 'Unfortunetlly, an error ocurred: ' . $error;
			}
		}

		if ( $this->db_name === 'progress' ) {

			$text = $body->text;

			try {

				$response = $sql->update(
					array(
						'text' => $text,
					),
					array(
						'id' => $data['id'],
					)
				);

				if ( $response === 0 ) {
					return 'Error: couldn\'t update title at ' . $data['id'];
				}

				if ( $response === 1 ) {
					return 'Success: title updated at ' . $data['id'];
				}
			} catch ( Exception $error ) {
				return 'Unfortunetlly, an error ocurred: ' . $error;
			}
		}

	}

	public function delete( $data ) {
		$this->security_check( $data );

		$sql = new Sql( 'servicetracker_' . $this->db_name . '' );

		if ( $this->db_name === 'cases' ) {
			try {
				$delete = $sql->delete( array( 'id' => $data['id'] ) );

				if ( $delete === 0 ) {
					return 'Error: No entry was found with id ' . $data['id'];
				} elseif ( $delete === 1 ) {
					return 'Success: deleted entry with id ' . $data['id'];
				}
			} catch ( \Throwable $th ) {
				return $th;
			}
		}

		if ( $this->db_name === 'progress' ) {
			try {
				$delete = $sql->delete( array( 'id' => $data['id'] ) );

				if ( $delete === 0 ) {
					return 'Error: No entry was found with id ' . $data['id'];
				} elseif ( $delete === 1 ) {
					return 'Success: deleted entry with id ' . $data['id'];
				}
			} catch ( \Throwable $th ) {
				return $th;
			}
		}

	}

	public function toggle_status( $data ) {
		$this->security_check( $data );

		if ( $this->db_name === 'progress' ) {
			return;
		}

		$sql      = new Sql( 'servicetracker_cases' );
		$response = $sql->get_by( array( 'id' => $data['id'] ) );
		$response = (array) $response[0];

		try {
			if ( $response['status'] === 'open' ) {
				$toggle = $sql->update(
					array(
						'status' => 'close',
					),
					array(
						'id' => $data['id'],
					)
				);

				return $toggle;
			}

			if ( $response['status'] === 'close' ) {
				$toggle = $sql->update(
					array(
						'status' => 'open',
					),
					array(
						'id' => $data['id'],
					)
				);

				return $toggle;
			}
		} catch ( \Throwable $th ) {
			return $th;
		}

	}

}
