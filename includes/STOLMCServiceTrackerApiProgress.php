<?php
namespace STOLMCServiceTracker\includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use Rdelbem\WPMailerClass\WPMailerClass;
use STOLMCServiceTracker\includes\STOLMCServiceTrackerApi;
use STOLMCServiceTracker\includes\STOLMCServiceTrackerApiContract;
use STOLMCServiceTracker\includes\STOLMCServiceTrackerSql;

/**
 * This class will resolve API calls intended to manipulate the progress table.
 *
 * It extends the API class that serves as a model.
 *
 * ENDPOINT => wp-json/service-tracker/v1/progress/[id]
 */
class STOLMCServiceTrackerApiProgress extends STOLMCServiceTrackerApi implements STOLMCServiceTrackerApiContract {

	/**
	 * SQL helper instance for database operations.
	 *
	 * @var STOLMCServiceTrackerSql
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
	public function run() {
		global $wpdb;

		$this->custom_api();
		$this->sql = new STOLMCServiceTrackerSql( $wpdb->prefix . self::DB );
	}

	/**
	 * Register custom API routes for progress management.
	 *
	 * @return void
	 */
	public function custom_api() {

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
	 * @return array|null Array of progress entries or null on failure.
	 */
	public function read( WP_REST_Request $data ) {
		$this->security_check( $data );

		$response = $this->sql->get_by( [ 'id_case' => $data['id_case'] ] );
		return $response;
	}

	/**
	 * Create a new progress entry and send email notification.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return string Insert result message.
	 */
	public function create( WP_REST_Request $data ) {
		$this->security_check( $data );

		$body = $data->get_json_params();

		$id_user = $body['id_user'];
		$id_case = $body['id_case'];
		$text    = $body['text'];

		// An email will be sent to the customer with the progress info.
		$send_mail = new WPMailerClass(
			$id_user,
			__( 'New status!', 'service-tracker-stolmc' ),
			__( 'You got a new status: ', 'service-tracker-stolmc' ) . $text
		);
		$send_mail->sendEmail();

		return $this->sql->insert(
			[
				'id_user' => $id_user,
				'id_case' => $id_case,
				'text'    => $text,
			]
		);
	}

	/**
	 * Update an existing progress entry.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Response indicating success or failure.
	 */
	public function update( WP_REST_Request $data ) {
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

			if ( ! $this->sql ) {
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

			$response = $this->sql->update(
				[ 'text' => $text ],
				[ 'id' => $id ]
			);

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
	public function delete( WP_REST_Request $data ) {
		$this->security_check( $data );
		$id     = $data->get_param( 'id' );
		$delete = $this->sql->delete( [ 'id' => $id ] );
		return new WP_REST_Response( [ 'success' => true ] );
	}
}
