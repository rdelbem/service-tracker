<?php
namespace ServiceTracker\includes;

use ServiceTracker\includes\Service_Tracker_Api_Contract;
use ServiceTracker\includes\Service_Tracker_Sql;
use ServiceTracker\includes\Service_Tracker_Api;
use ServiceTracker\includes\Service_Tracker_Mail;
use \WP_REST_Server;
use \WP_REST_Request;
use \WP_REST_Response;

// The database name used for this class -> servicetracker_progress

/**
 * This class will resolve api calls intended to manipulate the progress table.
 * It extends the API class that serves as a model.
 */
class Service_Tracker_Api_Progress extends Service_Tracker_Api implements Service_Tracker_Api_Contract {

	private $sql;

	public function run() {
		$this->custom_api();
		$this->sql = new Service_Tracker_Sql( 'servicetracker_progress' );

	}

	public function custom_api() {

		// register_new_route -> method from superclass / extended class

		$this->register_new_route( 'progress', '_case', WP_REST_Server::READABLE, array( $this, 'read' ) );

		$this->register_new_route( 'progress', '', WP_REST_Server::EDITABLE, array( $this, 'update' ) );

		$this->register_new_route( 'progress', '', WP_REST_Server::DELETABLE, array( $this, 'delete' ) );

		$this->register_new_route( 'progress', '_case', WP_REST_Server::CREATABLE, array( $this, 'create' ) );
	}

	public function read( WP_REST_Request $data ) {
		$this->security_check( $data );

		$response = $this->sql->get_by( array( 'id_case' => $data['id_case'] ) );
		return $response;
	}

	public function create( WP_REST_Request $data ) {
		$this->security_check( $data );

		$body = $data->get_body();
		$body = json_decode( $body );

		$id_user = $body->id_user;
		$id_case = $body->id_case;
		$text    = $body->text;

		// An email will be sent to the customer with the progress info
		$send_mail = new Service_Tracker_Mail(
			$id_user,
			__( 'New status!', 'service-tracker' ),
			__( 'You got a new status: ', 'service-tracker' ) . $text
		);

		return $this->sql->insert(
			array(
				'id_user' => $id_user,
				'id_case' => $id_case,
				'text'    => $text,
			)
		);
	}

	public function update( WP_REST_Request $data ) {
		$this->security_check( $data );

		$body = $data->get_body();
		$body = json_decode( $body );

		$text     = $body->text;
		$response = $this->sql->update(
			array( 'text' => $text ),
			array( 'id' => $data['id'] )
		);
	}

	public function delete( WP_REST_Request $data ) {
		$this->security_check( $data );

		$delete = $this->sql->delete( array( 'id' => $data['id'] ) );
	}

}
