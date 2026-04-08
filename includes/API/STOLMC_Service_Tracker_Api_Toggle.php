<?php
namespace STOLMC_Service_Tracker\includes\API;

use STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Sql;
use WP_REST_Request;
use WP_REST_Server;

/**
 * API class for toggling case statuses.
 */
class STOLMC_Service_Tracker_Api_Toggle extends STOLMC_Service_Tracker_Api {

	/**
	 * SQL helper instance for cases table operations.
	 *
	 * @var STOLMC_Service_Tracker_Sql
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
	public function run(): void {
		global $wpdb;

		$this->custom_api();
		$this->sql = new STOLMC_Service_Tracker_Sql( $wpdb->prefix . self::DB );
	}

	/**
	 * Register custom API routes for case status toggling.
	 *
	 * @return void
	 */
	public function custom_api(): void {

		// Route for toggling cases statuses.
		$this->register_new_route( 'cases-status', '', WP_REST_Server::CREATABLE, [ $this, 'toggle_status' ] );
	}

	/**
	 * Get translated messages for closed state.
	 *
	 * @return array<int, string> Translation messages for closed state.
	 */
	private function get_closed_messages(): array {
		$messages = [
			__( 'Your case was closed!', 'service-tracker-stolmc' ),
			__( 'is now closed!', 'service-tracker-stolmc' ),
		];

		/**
		 * Filters the closed status email messages.
		 *
		 * @since 1.0.0
		 *
		 * @param array $messages Translation messages for closed state.
		 */
		return apply_filters( 'stolmc_service_tracker_closed_status_messages', $messages );
	}

	/**
	 * Get translated messages for opened state.
	 *
	 * @return array<int, string> Translation messages for opened state.
	 */
	private function get_opened_messages(): array {
		$messages = [
			__( 'Your case was opened!', 'service-tracker-stolmc' ),
			__( 'is now opened!', 'service-tracker-stolmc' ),
		];

		/**
		 * Filters the opened status email messages.
		 *
		 * @since 1.0.0
		 *
		 * @param array $messages Translation messages for opened state.
		 */
		return apply_filters( 'stolmc_service_tracker_opened_status_messages', $messages );
	}

	/**
	 * Send email notification about case status change.
	 *
	 * @param int              $id_user      User ID to send email to.
	 * @param string           $title        Case title.
	 * @param array<int, string> $case_state_msg Translation messages for the state change.
	 *
	 * @return void
	 */
	public function sendEmail( int $id_user, string $title, array $case_state_msg ): void {
		/**
		 * Filters the toggle email data before sending.
		 *
		 * @since 1.0.0
		 *
		 * @param array $email_data {
		 *     Email data array.
		 *
		 *     @type int    $id_user      User ID to send email to.
		 *     @type string $subject      The email subject.
		 *     @type string $message      The email message.
		 *     @type string $title        Case title.
		 *     @type array  $case_state_msg Translation messages for the state change.
		 * }
		 */
		$email_data = apply_filters(
			'stolmc_service_tracker_toggle_email_data',
			[
				'id_user'      => $id_user,
				'subject'      => $case_state_msg[0],
				'message'      => $title . ' - ' . $case_state_msg[1],
				'title'        => $title,
				'case_state_msg' => $case_state_msg,
			]
		);

		// Get user email from WordPress user.
		$user = get_user_by( 'id', $email_data['id_user'] );
		if ( false !== $user ) {
			wp_mail( $user->user_email, $email_data['subject'], $email_data['message'] );
		}
	}

	/**
	 * Toggle case status between open and closed.
	 *
	 * A case status progress always has a state, which indicates whether it is
	 * opened, still in progress, or closed (it has been concluded).
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return int|false|null Update result.
	 */
	public function toggle_status( WP_REST_Request $data ): int|false|null {

		$this->security_check( $data );

		$response = $this->sql->get_by( [ 'id' => $data['id'] ] );

		if ( ! is_array( $response ) || ! isset( $response[0] ) ) {
			return false;
		}

		$response = (array) $response[0];
		$id_user = $response['id_user'];
		$title   = $response['title'];

		/**
		 * Filters the case data before toggle decision.
		 *
		 * @since 1.0.0
		 *
		 * @param array $response The case data.
		 * @param int   $case_id  The case ID.
		 */
		$response = apply_filters( 'stolmc_service_tracker_toggle_case_data', $response, $data['id'] );

		if ( 'open' === $response['status'] ) {
			/**
			 * Fires before a case is closed.
			 *
			 * @since 1.0.0
			 *
			 * @param int   $case_id  The ID of the case.
			 * @param array $response The case data.
			 * @param WP_REST_Request $data The REST request object.
			 */
			do_action( 'stolmc_service_tracker_case_before_closing', $data['id'], $response, $data );

			$toggle = $this->sql->update(
				[ 'status' => 'close' ],
				[ 'id' => $data['id'] ]
			);

			$this->sendEmail( $id_user, $title, $this->get_closed_messages() );

			/**
			 * Fires after a case has been closed.
			 *
			 * @since 1.0.0
			 *
			 * @param int|false|null $toggle  The update result.
			 * @param int            $id_user The user ID.
			 * @param string         $title   The case title.
			 * @param WP_REST_Request $data   The REST request object.
			 */
			do_action( 'stolmc_service_tracker_case_closed', $toggle, $id_user, $title, $data );

			return $toggle;
		}

		if ( 'close' === $response['status'] ) {
			/**
			 * Fires before a case is reopened.
			 *
			 * @since 1.0.0
			 *
			 * @param int   $case_id  The ID of the case.
			 * @param array $response The case data.
			 * @param WP_REST_Request $data The REST request object.
			 */
			do_action( 'stolmc_service_tracker_case_before_reopening', $data['id'], $response, $data );

			$toggle = $this->sql->update(
				[ 'status' => 'open' ],
				[ 'id' => $data['id'] ]
			);

			$this->sendEmail( $id_user, $title, $this->get_opened_messages() );

			/**
			 * Fires after a case has been reopened.
			 *
			 * @since 1.0.0
			 *
			 * @param int|false|null $toggle  The update result.
			 * @param int            $id_user The user ID.
			 * @param string         $title   The case title.
			 * @param WP_REST_Request $data   The REST request object.
			 */
			do_action( 'stolmc_service_tracker_case_reopened', $toggle, $id_user, $title, $data );

			return $toggle;
		}

		return null;
	}
}
