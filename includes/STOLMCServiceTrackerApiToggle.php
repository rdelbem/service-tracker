<?php
namespace STOLMCServiceTracker\includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Rdelbem\WPMailerClass\WPMailerClass;
use STOLMCServiceTracker\includes\STOLMCServiceTrackerApi;
use STOLMCServiceTracker\includes\STOLMCServiceTrackerSql;
use WP_REST_Request;
use WP_REST_Server;

/**
 * API class for toggling case statuses.
 */
class STOLMCServiceTrackerApiToggle extends STOLMCServiceTrackerApi {

	/**
	 * SQL helper instance for cases table operations.
	 *
	 * @var STOLMCServiceTrackerSql
	 */
	private $sql;

	/**
	 * Database table name constant.
	 */
	private const DB = 'servicetracker_cases';

	/**
	 * Initialize the API and register routes.
	 *
	 * @return void
	 */
	public function run() {
		global $wpdb;

		$this->custom_api();
		$this->sql = new STOLMCServiceTrackerSql( $wpdb->prefix . self::DB );
	}

	/**
	 * Register custom API routes for case status toggling.
	 *
	 * @return void
	 */
	public function custom_api() {

		// Route for toggling cases statuses.
		$this->register_new_route( 'cases-status', '', WP_REST_Server::CREATABLE, [ $this, 'toggle_status' ] );
	}

	/**
	 * Get translated messages for closed state.
	 *
	 * @return array Translation messages for closed state.
	 */
	private function get_closed_messages() {
		return [
			__( 'Your case was closed!', 'service-tracker-stolmc' ),
			__( 'is now closed!', 'service-tracker-stolmc' ),
		];
	}

	/**
	 * Get translated messages for opened state.
	 *
	 * @return array Translation messages for opened state.
	 */
	private function get_opened_messages() {
		return [
			__( 'Your case was opened!', 'service-tracker-stolmc' ),
			__( 'is now opened!', 'service-tracker-stolmc' ),
		];
	}

	/**
	 * Send email notification about case status change.
	 *
	 * @param int    $id_user      User ID to send email to.
	 * @param string $title        Case title.
	 * @param array  $case_state_msg Translation messages for the state change.
	 *
	 * @return void
	 */
	public function sendEmail( $id_user, $title, $case_state_msg ) {
		$send_mail = new WPMailerClass(
			$id_user,
			$case_state_msg[0],
			$title . ' - ' . $case_state_msg[1]
		);
		$send_mail->sendEmail();
	}

	/**
	 * Toggle case status between open and closed.
	 *
	 * A case status progress always has a state, which indicates whether it is
	 * opened, still in progress, or closed (it has been concluded).
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return mixed Update result.
	 */
	public function toggle_status( WP_REST_Request $data ) {

		$this->security_check( $data );

		$response = $this->sql->get_by( [ 'id' => $data['id'] ] );
		$response = (array) $response[0];
		$id_user = $response['id_user'];
		$title   = $response['title'];

		if ( 'open' === $response['status'] ) {
			$toggle = $this->sql->update(
				[ 'status' => 'close' ],
				[ 'id' => $data['id'] ]
			);

			$this->sendEmail( $id_user, $title, $this->get_closed_messages() );

			return $toggle;
		}

		if ( 'close' === $response['status'] ) {
			$toggle = $this->sql->update(
				[ 'status' => 'open' ],
				[ 'id' => $data['id'] ]
			);

			$this->sendEmail( $id_user, $title, $this->get_opened_messages() );

			return $toggle;
		}
	}
}
