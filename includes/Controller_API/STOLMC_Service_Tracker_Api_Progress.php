<?php
namespace STOLMC_Service_Tracker\includes\Controller_API;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Progress_Service;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Service_Result_Dto;

/**
 * This class will resolve API calls intended to manipulate the progress table.
 *
 * It extends the API class that serves as a model.
 *
 * ENDPOINT => wp-json/service-tracker/v1/progress/[id]
 */
class STOLMC_Service_Tracker_Api_Progress extends STOLMC_Service_Tracker_Api implements STOLMC_Service_Tracker_Api_Contract {

	/**
	 * Progress Service instance.
	 *
	 * @var STOLMC_Service_Tracker_Progress_Service
	 */
	private $progress_service;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->progress_service = new STOLMC_Service_Tracker_Progress_Service();
	}

	/**
	 * Initialize the API and register routes.
	 *
	 * @return void
	 */
	public function run(): void {
		$this->custom_api();
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
		$this->register_route( '/progress/upload', WP_REST_Server::CREATABLE, [ $this, 'upload_file' ] );

		// Register user attachments route.
		$this->register_route(
			'/progress/user-attachments/(?P<id_user>\d+)',
			WP_REST_Server::READABLE,
			[ $this, 'read_user_attachments' ]
		);
	}

	/**
	 * Read progress entries for a specific case.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Array of progress entries.
	 */
	public function read( WP_REST_Request $data ): WP_REST_Response {
		$case_id = (int) $data['id_case'];

		$result = $this->progress_service->get_progress_for_case( $case_id );

		if ( ! $result->success ) {
			return STOLMC_Service_Tracker_Api_Response_Mapper::to_default_response( $result );
		}

		return new WP_REST_Response( $result->data, $result->http_status );
	}

	/**
	 * Create a new progress entry.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Response indicating success or failure.
	 */
	public function create( WP_REST_Request $data ): WP_REST_Response {
		$body = $data->get_body();
		$body = json_decode( $body, true );

		if ( ! is_array( $body ) ) {
			return new WP_REST_Response(
				[
					'success' => false,
					'message' => 'Invalid JSON data',
				],
				400
			);
		}

		$result = $this->progress_service->create_progress( $body );

		return STOLMC_Service_Tracker_Api_Response_Mapper::to_legacy_message_response( $result );
	}

	/**
	 * Update an existing progress entry.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Update result message.
	 */
	public function update( WP_REST_Request $data ): WP_REST_Response {
		$progress_id = (int) $data['id'];
		$body = $data->get_body();
		$body = json_decode( $body, true );

		if ( ! is_array( $body ) ) {
			return new WP_REST_Response(
				[
					'success' => false,
					'message' => 'Invalid JSON data',
				],
				400
			);
		}

		$result = $this->progress_service->update_progress( $progress_id, $body );

		return STOLMC_Service_Tracker_Api_Response_Mapper::to_legacy_message_response( $result );
	}

	/**
	 * Delete a progress entry.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function delete( WP_REST_Request $data ): WP_REST_Response {
		$progress_id = (int) $data['id'];

		$result = $this->progress_service->delete_progress( $progress_id );

		return STOLMC_Service_Tracker_Api_Response_Mapper::to_legacy_message_response( $result );
	}

	/**
	 * Upload a file attachment for a progress entry.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function upload_file( WP_REST_Request $data ): WP_REST_Response {
		$files = $data->get_file_params();
		$body = $data->get_body_params();

		// New flow (React app): upload one or many files first, then persist
		// attachment metadata in the progress create request.
		if ( isset( $files['files'] ) ) {
			$normalized_files = $this->normalize_uploaded_files( $files['files'] );

			if ( empty( $normalized_files ) ) {
				return new WP_REST_Response(
					[
						'success' => false,
						'message' => 'No file was uploaded',
					],
					400
				);
			}

			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}

			$uploaded_files = [];
			$allowed_types  = [ 'image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/plain' ];
			$max_size       = 5 * 1024 * 1024; // 5MB

			foreach ( $normalized_files as $file ) {
				if ( isset( $file['error'] ) && (int) $file['error'] !== UPLOAD_ERR_OK ) {
					return new WP_REST_Response(
						[
							'success' => false,
							'message' => 'File upload error',
						],
						400
					);
				}

				if ( ! isset( $file['type'] ) || ! in_array( $file['type'], $allowed_types, true ) ) {
					return new WP_REST_Response(
						[
							'success' => false,
							'message' => 'Invalid file type. Allowed types: JPEG, PNG, GIF, PDF, TXT',
						],
						400
					);
				}

				if ( isset( $file['size'] ) && (int) $file['size'] > $max_size ) {
					return new WP_REST_Response(
						[
							'success' => false,
							'message' => 'File is too large. Maximum size is 5MB',
						],
						400
					);
				}

				$uploaded = wp_handle_upload(
					$file,
					[
						'test_form' => false,
					]
				);

				if ( ! is_array( $uploaded ) || isset( $uploaded['error'] ) ) {
					return new WP_REST_Response(
						[
							'success' => false,
							'message' => is_array( $uploaded ) && isset( $uploaded['error'] ) ? (string) $uploaded['error'] : 'Failed to upload file',
						],
						500
					);
				}

				$uploaded_files[] = [
					'url'  => (string) ( $uploaded['url'] ?? '' ),
					'type' => (string) ( $file['type'] ?? '' ),
					'name' => (string) ( $file['name'] ?? '' ),
					'size' => (int) ( $file['size'] ?? 0 ),
				];
			}

			return new WP_REST_Response(
				[
					'success' => true,
					'message' => 'Files uploaded successfully',
					'files'   => $uploaded_files,
				],
				201
			);
		}

		// Legacy flow: upload a file directly to an existing progress entry.
		if ( ! isset( $files['file'] ) || ! isset( $body['progress_id'] ) ) {
			return new WP_REST_Response(
				[
					'success' => false,
					'message' => 'Missing file or progress_id',
				],
				400
			);
		}

		$file_data = $files['file'];
		$progress_id = (int) $body['progress_id'];

		$result = $this->progress_service->upload_file( $file_data, $progress_id );

		return STOLMC_Service_Tracker_Api_Response_Mapper::to_legacy_message_response( $result );
	}

	/**
	 * Normalize file payload from single/multi upload structures.
	 *
	 * @param mixed $files_payload Raw `files` entry from get_file_params().
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function normalize_uploaded_files( $files_payload ): array {
		if ( ! is_array( $files_payload ) ) {
			return [];
		}

		// Multiple files format:
		// [ 'name' => [..], 'type' => [..], 'tmp_name' => [..], 'error' => [..], 'size' => [..] ].
		if ( isset( $files_payload['name'] ) && is_array( $files_payload['name'] ) ) {
			$normalized = [];
			$count      = count( $files_payload['name'] );

			for ( $i = 0; $i < $count; $i++ ) {
				$normalized[] = [
					'name'     => $files_payload['name'][ $i ] ?? '',
					'type'     => $files_payload['type'][ $i ] ?? '',
					'tmp_name' => $files_payload['tmp_name'][ $i ] ?? '',
					'error'    => $files_payload['error'][ $i ] ?? UPLOAD_ERR_NO_FILE,
					'size'     => $files_payload['size'][ $i ] ?? 0,
				];
			}

			return $normalized;
		}

		// Single file under "files".
		if ( isset( $files_payload['name'] ) ) {
			return [ $files_payload ];
		}

		return [];
	}

	/**
	 * Read user attachments.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function read_user_attachments( WP_REST_Request $data ): WP_REST_Response {
		$user_id = (int) $data['id_user'];

		$result = $this->progress_service->get_user_attachments( $user_id );

		if ( ! $result->success ) {
			return STOLMC_Service_Tracker_Api_Response_Mapper::to_default_response( $result );
		}

		return new WP_REST_Response( $result->data, $result->http_status );
	}
}
