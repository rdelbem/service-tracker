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

		// Register upload route with custom pattern.
		$route_args = [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'upload_file' ],
			'permission_callback' => [ $this, 'permission_check' ],
		];

		register_rest_route(
			'service-tracker-stolmc/v1',
			'/progress/upload',
			$route_args
		);
	}

	/**
	 * Read progress entries for a specific case.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return array<object>|object|null Array of progress entries or null on failure.
	 */
	public function read( WP_REST_Request $data ): array|object|null {
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

		// Decode attachments JSON for each progress entry.
		if ( is_array( $response ) ) {
			foreach ( $response as &$entry ) {
				if ( isset( $entry->attachments ) && is_string( $entry->attachments ) ) {
					$entry->attachments = json_decode( $entry->attachments, true );
				} elseif ( ! isset( $entry->attachments ) ) {
					$entry->attachments = null;
				}
			}
			unset( $entry );
		}

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
		$body = $data->get_json_params();

		$id_user = $body['id_user'];
		$id_case = $body['id_case'];
		$text    = $body['text'];
		$attachments = isset( $body['attachments'] ) ? $body['attachments'] : null;

		// Encode attachments as JSON string for database storage.
		$attachments_json = null;
		if ( $attachments && is_array( $attachments ) ) {
			$attachments_json = wp_json_encode( $attachments );
		}

		$progress_data = [
			'id_user'     => $id_user,
			'id_case'     => $id_case,
			'text'        => $text,
			'attachments' => $attachments_json,
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

		// Send email notification after progress is created.
		$progress_id = $wpdb->insert_id ? (int) $wpdb->insert_id : null;
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

			$sent = wp_mail( $user->user_email, $email_data['subject'], $email_data['message'] );

			/**
			 * Fires after the progress email attempt to log notification status.
			 *
			 * @since 1.2.0
			 *
			 * @param bool   $sent        Whether wp_mail() returned success.
			 * @param string $to          The email recipient.
			 * @param string $subject     The email subject.
			 * @param int    $id_user     The user ID.
			 * @param int    $id_case     The case ID.
			 * @param int    $progress_id The progress ID (if available).
			 */
			do_action( 'stolmc_service_tracker_progress_email_result', $sent, $user->user_email, $email_data['subject'], $id_user, $id_case, $progress_id );
		}

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
			$body = $data->get_json_params();

			if ( ! isset( $body['text'] ) ) {
				return $this->rest_response(
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
				return $this->rest_response(
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
				return $this->rest_response(
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

			return $this->rest_response(
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
			return $this->rest_response(
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
		$id = $data->get_param( 'id' );

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

		return $this->rest_response( [ 'success' => true ], 200 );
	}

	/**
	 * Upload a file and associate it with a progress entry.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Response with file data.
	 */
	public function upload_file( WP_REST_Request $data ): WP_REST_Response {
		try {
			// Check if files were uploaded.
			if ( empty( $_FILES ) ) {
				return $this->rest_response(
					[
						'success' => false,
						'message' => 'No files uploaded',
					],
					400
				);
			}

			$id_case = $data->get_param( 'id_case' );
			$id_user = $data->get_param( 'id_user' );

			if ( ! $id_case || ! $id_user ) {
				return $this->rest_response(
					[
						'success' => false,
						'message' => 'Missing id_case or id_user parameter',
					],
					400
				);
			}

			// Handle file upload using WordPress media handling.
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';

			$upload_overrides = [
				'test_form' => false,
				'test_size' => true,
				'test_upload_size' => true,
			];

			$uploaded_files = [];
			$upload_errors = [];

			// Handle multiple files.
			foreach ( $_FILES as $file_key => $file_array ) {
				// Handle array-style file uploads (multiple files).
				if ( is_array( $file_array['name'] ) ) {
					foreach ( $file_array['name'] as $i => $file_name ) {
						// Check for upload errors.
						if ( $file_array['error'][ $i ] !== UPLOAD_ERR_OK ) {
							$upload_errors[] = "File {$file_name}: Upload error {$file_array['error'][ $i ]}";
							continue;
						}

						$file = [
							'name'     => sanitize_file_name( $file_name ),
							'type'     => $file_array['type'][ $i ],
							'tmp_name' => $file_array['tmp_name'][ $i ],
							'error'    => $file_array['error'][ $i ],
							'size'     => $file_array['size'][ $i ],
						];

						$movefile = wp_handle_upload( $file, $upload_overrides );

						if ( $movefile && ! isset( $movefile['error'] ) ) {
							$uploaded_files[] = [
								'url'  => $movefile['url'],
								'type' => $file_array['type'][ $i ],
								'name' => $file_name,
								'size' => $file_array['size'][ $i ],
							];
						} else {
							$error_msg = isset( $movefile['error'] ) ? $movefile['error'] : 'Unknown error';
							$upload_errors[] = "File {$file_name}: {$error_msg}";
							// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
							error_log( 'Service Tracker Upload Error: ' . $error_msg );
						}
					}
				} else {
					// Single file upload.
					// Check for upload errors.
					if ( $file_array['error'] !== UPLOAD_ERR_OK ) {
						$upload_errors[] = "Upload error {$file_array['error']}";
						continue;
					}

					$file_array['name'] = sanitize_file_name( $file_array['name'] );
					$movefile = wp_handle_upload( $file_array, $upload_overrides );

					if ( $movefile && ! isset( $movefile['error'] ) ) {
						$uploaded_files[] = [
							'url'  => $movefile['url'],
							'type' => $file_array['type'],
							'name' => $file_array['name'],
							'size' => $file_array['size'],
						];
					} else {
						$error_msg = isset( $movefile['error'] ) ? $movefile['error'] : 'Unknown error';
						$upload_errors[] = $error_msg;
						// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
						error_log( 'Service Tracker Upload Error: ' . $error_msg );
					}
				}
			}

			if ( empty( $uploaded_files ) ) {
				$error_message = ! empty( $upload_errors )
					? 'Upload failed: ' . implode( '; ', $upload_errors )
					: 'Failed to upload files';

				return $this->rest_response(
					[
						'success' => false,
						'message' => $error_message,
					],
					500
				);
			}

			/**
			 * Filters the uploaded file data.
			 *
			 * @since 1.2.0
			 *
			 * @param array $uploaded_files The uploaded files data.
			 * @param WP_REST_Request $data The REST request object.
			 */
			return $this->rest_response(
				[
					'success' => true,
					'files'   => apply_filters( 'stolmc_service_tracker_upload_files_response', $uploaded_files, $data ),
				],
				200
			);

		} catch ( \Exception $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'Service Tracker Upload Error: ' . $e->getMessage() );
			return $this->rest_response(
				[
					'success' => false,
					'message' => $e->getMessage(),
				],
				500
			);
		}
	}
}
