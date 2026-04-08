<?php
namespace STOLMC_Service_Tracker\includes\API;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Sql;

/**
 * This class will resolve API calls intended to manipulate the progress table.
 *
 * It extends the API class that serves as a model.
 *
 * ENDPOINT => wp-json/service-tracker/v1/progress/[id]
 */
class STOLMC_Service_Tracker_Api_Progress extends STOLMC_Service_Tracker_Api implements STOLMC_Service_Tracker_Api_Contract {

	/**
	 * SQL helper instance for database operations.
	 *
	 * @var STOLMC_Service_Tracker_Sql
	 */
	private $sql;

	/**
	 * Database table name constant.
	 */
	private const DB = 'servicetracker_progress';

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
	 * Register custom API routes for progress management.
	 *
	 * @return void
	 */
	public function custom_api(): void {

		// RegisterNewRoute -> Method from superclass / extended class.

		$this->register_new_route( 'progress', '_case', WP_REST_Server::READABLE, [ $this, 'read' ] );

		$this->register_new_route( 'progress', '', WP_REST_Server::EDITABLE, [ $this, 'update' ] );

		$this->register_new_route( 'progress', '', WP_REST_Server::DELETABLE, [ $this, 'delete' ] );

		$this->register_new_route( 'progress', '_case', WP_REST_Server::CREATABLE, [ $this, 'create' ] );
	}

	/**
	 * Read progress entries for a specific case.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return array<object>|object|null Array of progress entries or null on failure.
	 */
	public function read( WP_REST_Request $data ): array|object|null {
		$this->security_check( $data );

		/**
		 * Filters the query parameters for reading progress entries.
		 *
		 * @since 1.0.0
		 *
		 * @param array $query_args The query parameters.
		 * @param WP_REST_Request $data The REST request object.
		 */
		$query_args = apply_filters( 'stolmc_service_tracker_progress_read_query_args', [ 'id_case' => $data['id_case'] ], $data );

		$response = $this->sql->get_by( $query_args );

		/**
		 * Filters the progress read response.
		 *
		 * @since 1.0.0
		 *
		 * @param array|object|null $response The progress data.
		 * @param WP_REST_Request   $data     The REST request object.
		 */
		return apply_filters( 'stolmc_service_tracker_progress_read_response', $response, $data );
	}

	/**
	 * Create a new progress entry and send email notification.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return string|false Insert result message.
	 */
	public function create( WP_REST_Request $data ): string|false {
		$this->security_check( $data );

		$body = $data->get_json_params();

		$id_user = $body['id_user'];
		$id_case = $body['id_case'];
		$text    = $body['text'];

		$progress_data = [
			'id_user' => $id_user,
			'id_case' => $id_case,
			'text'    => $text,
		];

		/**
		 * Filters the progress data before insertion.
		 *
		 * @since 1.0.0
		 *
		 * @param array           $progress_data The progress data to insert.
		 * @param WP_REST_Request $data          The REST request object.
		 */
		$progress_data = apply_filters( 'stolmc_service_tracker_progress_create_data', $progress_data, $data );

		// An email will be sent to the customer with the progress info.
		$subject = __( 'New status!', 'service-tracker-stolmc' );
		$message = __( 'You got a new status: ', 'service-tracker-stolmc' ) . $progress_data['text'];

		/**
		 * Filters the email data before sending.
		 *
		 * @since 1.0.0
		 *
		 * @param array $email_data {
		 *     Email data array.
		 *
		 *     @type int    $id_user The user ID to send email to.
		 *     @type string $subject The email subject.
		 *     @type string $message The email message.
		 * }
		 * @param array  $progress_data The progress data.
		 */
		$email_data = apply_filters(
			'stolmc_service_tracker_progress_email_data',
			[
				'id_user' => $id_user,
				'subject' => $subject,
				'message' => $message,
			],
			$progress_data
		);

		// Get user email from WordPress user.
		$user = get_user_by( 'id', $email_data['id_user'] );
		if ( false !== $user ) {
			/**
			 * Fires before the progress email is sent.
			 *
			 * @since 1.0.0
			 *
			 * @param string $to      The email recipient.
			 * @param string $subject The email subject.
			 * @param string $message The email message.
			 * @param int    $id_user The user ID.
			 */
			do_action( 'stolmc_service_tracker_progress_before_email_sent', $user->user_email, $email_data['subject'], $email_data['message'], $id_user );

			wp_mail( $user->user_email, $email_data['subject'], $email_data['message'] );
		}

		$result = $this->sql->insert( $progress_data );

		/**
		 * Fires after a progress entry has been created.
		 *
		 * @since 1.0.0
		 *
		 * @param string|false    $result        The insert result.
		 * @param array           $progress_data The progress data.
		 * @param WP_REST_Request $data          The REST request object.
		 */
		do_action( 'stolmc_service_tracker_progress_created', $result, $progress_data, $data );

		return $result;
	}

	/**
	 * Update an existing progress entry.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Response indicating success or failure.
	 */
	public function update( WP_REST_Request $data ): WP_REST_Response {
		try {
			$this->security_check( $data );

			$body = $data->get_json_params();

			if ( ! isset( $body['text'] ) ) {
				return new WP_REST_Response(
					[
						'success' => false,
						'message' => 'Missing text parameter',
					],
					400
				);
			}

			$text = $body['text'];
			$id   = $data->get_param( 'id' );

			if ( ! $id ) {
				return new WP_REST_Response(
					[
						'success' => false,
						'message' => 'Missing ID parameter',
					],
					400
				);
			}

			if ( ! $this->sql ) { // @phpstan-ignore booleanNot.alwaysFalse
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Fallback logging for critical errors.
				error_log( 'Service Tracker: SQL instance is null in update method' );
				return new WP_REST_Response(
					[
						'success' => false,
						'message' => 'SQL instance not initialized',
					],
					500
				);
			}

			$update_data = [ 'text' => $text ];
			$condition   = [ 'id' => $id ];

			/**
			 * Filters the progress update data before the SQL operation.
			 *
			 * @since 1.0.0
			 *
			 * @param array           $update_data The data to update.
			 * @param array           $condition   The WHERE condition.
			 * @param WP_REST_Request $data        The REST request object.
			 */
			$update_data = apply_filters( 'stolmc_service_tracker_progress_update_data', $update_data, $condition, $data );

			$response = $this->sql->update( $update_data, $condition );

			/**
			 * Fires after a progress entry has been updated.
			 *
			 * @since 1.0.0
			 *
			 * @param int|false       $response    The update result.
			 * @param array           $update_data The data that was updated.
			 * @param array           $condition   The WHERE condition.
			 * @param WP_REST_Request $data        The REST request object.
			 */
			do_action( 'stolmc_service_tracker_progress_updated', $response, $update_data, $condition, $data );

			return new WP_REST_Response(
				[
					'success' => true,
					'data'    => $response,
				],
				200
			);
		} catch ( \Exception $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Fallback logging for critical errors.
			error_log( 'Service Tracker Update Error: ' . $e->getMessage() );
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Fallback logging for critical errors.
			error_log( 'Service Tracker Update Stack: ' . $e->getTraceAsString() );
			return new WP_REST_Response(
				[
					'success' => false,
					'message' => $e->getMessage(),
				],
				500
			);
		}
	}

	/**
	 * Delete a progress entry.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Response indicating success.
	 */
	public function delete( WP_REST_Request $data ): WP_REST_Response {
		$this->security_check( $data );
		$id     = $data->get_param( 'id' );

		/**
		 * Fires before a progress entry is deleted.
		 *
		 * @since 1.0.0
		 *
		 * @param int             $id   The ID of the progress entry.
		 * @param WP_REST_Request $data The REST request object.
		 */
		do_action( 'stolmc_service_tracker_progress_before_delete', $id, $data );

		$delete = $this->sql->delete( [ 'id' => $id ] );

		/**
		 * Fires after a progress entry has been deleted.
		 *
		 * @since 1.0.0
		 *
		 * @param int|false       $delete The delete result.
		 * @param int             $id     The ID of the deleted entry.
		 * @param WP_REST_Request $data   The REST request object.
		 */
		do_action( 'stolmc_service_tracker_progress_deleted', $delete, $id, $data );

		return new WP_REST_Response( [ 'success' => true ] );
	}
}
