<?php
namespace ServiceTracker\includes;

use ServiceTracker\includes\Service_Tracker_Sql;
use ServiceTracker\includes\Service_Tracker_Api;
use ServiceTracker\includes\Service_Tracker_Mail;
use \WP_REST_Server;
use \WP_REST_Request;

class Service_Tracker_Api_Toggle extends Service_Tracker_Api {

	public $sql;

	/**
	 * The messages that will be sent over email
	 *
	 * @var array
	 */
	public $closed;

	/**
	 * The messages that will be sent over email
	 *
	 * @var array
	 */
	public $opened;

	public function __construct() {
		$this->closed = array( __( 'Your case was closed!', 'service-tracker' ), __( 'is now closed!', 'service-tracker' ) );
		$this->opened = array( __( 'Your case was opened!', 'service-tracker' ), __( 'is now opened!', 'service-tracker' ) );
	}

	public function run() {
		$this->custom_api();
		$this->sql = new Service_Tracker_Sql( 'servicetracker_cases' );
	}

	public function custom_api() {
		// Route for toggleling cases statuses
		$this->register_new_route( 'cases-status', '', WP_REST_Server::CREATABLE, array( $this, 'toggle_status' ) );
	}

	public function send_email( $id_user, $title, $case_state_msg ) {
		$send_mail = new Service_Tracker_Mail(
			$id_user,
			$case_state_msg[0],
			$title . ' - ' . $case_state_msg[1]
		);
	}

	/**
	 * A case status progress always has a state, which indicates wether it is
	 * opened, still in progress, or closed, it has been concluded.
	 *
	 * @param WP_REST_Request $data
	 * @return void
	 */
	public function toggle_status( WP_REST_Request $data ) {

		$this->security_check( $data );

		$response = $this->sql->get_by( array( 'id' => $data['id'] ) );
		$response = (array) $response[0];
		$id_user  = $response['id_user'];
		$title    = $response['title'];

		if ( $response['status'] === 'open' ) {
			$toggle = $this->sql->update(
				array( 'status' => 'close' ),
				array( 'id' => $data['id'] )
			);

			$this->send_email( $id_user, $title, $this->closed );

			return $toggle;
		}

		if ( $response['status'] === 'close' ) {
			$toggle = $this->sql->update(
				array( 'status' => 'open' ),
				array( 'id' => $data['id'] )
			);

			$this->send_email( $id_user, $title, $this->opened );

			return $toggle;
		}
	}
}
