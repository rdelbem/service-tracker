<?php
namespace ServiceTracker\includes;

use ServiceTracker\includes\Service_Tracker_Api_Contract;
use ServiceTracker\includes\Service_Tracker_Sql;
use ServiceTracker\includes\Service_Tracker_Api;
use \WP_REST_Server;
use \WP_REST_Request;
use \WP_REST_Response;

// The database name used for this class -> servicetracker_cases

/**
 * This class will resolve api calls intended to manipulate the cases table.
 * It extends the API class that serves as a model.
 *
 * ENDPOINT => wp-json/service-tracker/v1/progress/[case_id]
 */
class Service_Tracker_Api_Cases extends Service_Tracker_Api implements Service_Tracker_Api_Contract {

	private $sql;

	private const DB = 'servicetracker_cases';

	public function run() {
		$this->custom_api();
		$this->sql = new Service_Tracker_Sql( self::DB );
	}

	public function custom_api() {

		// register_new_route -> method from superclass / extended class

		$this->register_new_route( 'cases', '_user', WP_REST_Server::READABLE, array( $this, 'read' ) );

		$this->register_new_route( 'cases', '', WP_REST_Server::EDITABLE, array( $this, 'update' ) );

		$this->register_new_route( 'cases', '', WP_REST_Server::DELETABLE, array( $this, 'delete' ) );

		$this->register_new_route( 'cases', '_user', WP_REST_Server::CREATABLE, array( $this, 'create' ) );
	}

	public function read( WP_REST_Request $data ) {
		$this->security_check( $data );

		$response = $this->sql->get_by( array( 'id_user' => $data['id_user'] ) );
		return $response;
	}

	public function create( WP_REST_Request $data ) {
		$this->security_check( $data );

		$body    = $data->get_body();
		$body    = json_decode( $body );
		$id_user = $body->id_user;
		$title   = $body->title;

		return $this->sql->insert(
			array(
				'id_user' => $id_user,
				'title'   => $title,
				'status'  => 'open',
			)
		);
	}

	public function update( WP_REST_Request $data ) {
		$this->security_check( $data );

		$body = $data->get_body();
		$body = json_decode( $body );

		$title    = $body->title;
		$response = $this->sql->update(
			array( 'title' => $title ),
			array( 'id' => $data['id'] )
		);
	}

	public function delete( WP_REST_Request $data ) {
		$this->security_check( $data );

		$delete = $this->sql->delete( array( 'id' => $data['id'] ) );
	}

}
