<?php

namespace STOLMC_Service_Tracker\includes\Application;

use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Service_Result_Dto;
use STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Cases_Repository;
use STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Progress_Repository;

/**
 * Progress Service for business logic operations on progress entries.
 *
 * This service encapsulates all business logic for progress operations
 * and returns uniform Service Result DTOs.
 */
class STOLMC_Service_Tracker_Progress_Service {

	/**
	 * Cases Repository instance.
	 *
	 * @var STOLMC_Service_Tracker_Cases_Repository
	 */
	private $cases_repository;

	/**
	 * Progress Repository instance.
	 *
	 * @var STOLMC_Service_Tracker_Progress_Repository
	 */
	private $progress_repository;

	/**
	 * Constructor.
	 *
	 * @param STOLMC_Service_Tracker_Cases_Repository|null   $cases_repository   Cases repository.
	 * @param STOLMC_Service_Tracker_Progress_Repository|null $progress_repository Progress repository.
	 */
	public function __construct(
		?STOLMC_Service_Tracker_Cases_Repository $cases_repository = null,
		?STOLMC_Service_Tracker_Progress_Repository $progress_repository = null
	) {
		$this->cases_repository = $cases_repository ?? new STOLMC_Service_Tracker_Cases_Repository();
		$this->progress_repository = $progress_repository ?? new STOLMC_Service_Tracker_Progress_Repository();
	}

	/**
	 * Get progress entries for a specific case.
	 *
	 * @param int $case_id Case ID.
	 *
	 * @return STOLMC_Service_Tracker_Service_Result_Dto Service result.
	 */
	public function get_progress_for_case( int $case_id ): STOLMC_Service_Tracker_Service_Result_Dto {
		try {
			$response = $this->cases_repository->progress( $case_id )->find_all();

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
			 * @param int               $case_id  The case ID.
			 */
			$response = apply_filters( 'stolmc_service_tracker_progress_read_response', $response, $case_id );

			return STOLMC_Service_Tracker_Service_Result_Dto::ok( $response, 200 );
		} catch ( \Exception $e ) {
			return STOLMC_Service_Tracker_Service_Result_Dto::fail(
				'progress_read_error',
				'Failed to read progress entries: ' . $e->getMessage(),
				500
			);
		}
	}

	/**
	 * Get all attachments across all progress entries belonging to a user's cases.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return STOLMC_Service_Tracker_Service_Result_Dto Service result.
	 */
	public function get_user_attachments( int $user_id ): STOLMC_Service_Tracker_Service_Result_Dto {
		try {
			if ( ! $user_id ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'missing_user_id',
					'Missing user ID parameter',
					400
				);
			}

			$attachments = [];
			$cases = $this->cases_repository->find_by( [ 'id_user' => $user_id ] );

			if ( ! is_iterable( $cases ) ) {
				$data = [
					'success' => true,
					'data'    => [],
				];

				return STOLMC_Service_Tracker_Service_Result_Dto::ok( $data, 200 );
			}

			foreach ( $cases as $case ) {
				if ( ! isset( $case->id ) ) {
					continue;
				}

				$progress_rows = $this->cases_repository->progress( (int) $case->id )->find_all();
				if ( ! is_iterable( $progress_rows ) ) {
					continue;
				}

				foreach ( $progress_rows as $row ) {
					$row_attachments = $row->attachments ?? null;
					if ( ! is_string( $row_attachments ) || '' === $row_attachments || 'null' === $row_attachments ) {
						continue;
					}

					$decoded = json_decode( $row_attachments, true );
					if ( ! is_array( $decoded ) || empty( $decoded ) ) {
						continue;
					}

					foreach ( $decoded as $att ) {
						$attachments[] = [
							'url'         => $att['url'] ?? '',
							'type'        => $att['type'] ?? '',
							'name'        => $att['name'] ?? '',
							'size'        => $att['size'] ?? 0,
							'progress_id' => (int) ( $row->id ?? 0 ),
							'id_case'     => (int) ( $row->id_case ?? 0 ),
							'case_title'  => (string) ( $case->title ?? '' ),
							'created_at'  => (string) ( $row->created_at ?? '' ),
							'status_text' => (string) ( $row->text ?? '' ),
						];
					}
				}
			}

			usort(
				$attachments,
				static fn( array $left, array $right ): int => strcmp(
					(string) $right['created_at'],
					(string) $left['created_at']
				)
			);

			$data = [
				'success' => true,
				'data'    => $attachments,
			];

			return STOLMC_Service_Tracker_Service_Result_Dto::ok( $data, 200 );
		} catch ( \Exception $e ) {
			return STOLMC_Service_Tracker_Service_Result_Dto::fail(
				'attachments_read_error',
				'Failed to read user attachments: ' . $e->getMessage(),
				500
			);
		}
	}

	/**
	 * Create a new progress entry.
	 *
	 * @param array $progress_data Progress data.
	 *
	 * @return STOLMC_Service_Tracker_Service_Result_Dto Service result.
	 */
	public function create_progress( array $progress_data ): STOLMC_Service_Tracker_Service_Result_Dto {
		try {
			// Validate required fields.
			if ( ! isset( $progress_data['id_case'] ) || ! isset( $progress_data['text'] ) ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'missing_required_fields',
					'id_case and text are required fields',
					400
				);
			}

			// Set defaults.
			$progress_data['attachments'] = $progress_data['attachments'] ?? null;
			// The progress table has no `status` column. Ensure stray payload keys
			// do not generate SQL errors on insert.
			unset( $progress_data['status'] );

			/**
			 * Filters the progress data before insertion.
			 *
			 * @since 1.0.0
			 *
			 * @param array $progress_data The progress data to insert.
			 */
			$progress_data = apply_filters( 'stolmc_service_tracker_progress_create_data', $progress_data );

			// Ensure JSON column compatibility: attachments must be NULL or valid JSON text.
			$progress_data['attachments'] = $this->normalize_attachments_for_storage( $progress_data['attachments'] ?? null );

			$inserted = $this->cases_repository->progress( (int) $progress_data['id_case'] )->create( $progress_data );

			// Get the last insert ID from WordPress database
			global $wpdb;
			$insert_id = (int) $wpdb->insert_id;

			if ( $insert_id > 0 ) {
				/**
				 * Fires after a progress entry has been created.
				 *
				 * @since 1.0.0
				 *
				 * @param int   $progress_id   The ID of the created progress entry.
				 * @param array $progress_data The progress data.
				 */
				do_action( 'stolmc_service_tracker_progress_created', $insert_id, $progress_data );

				$data = [
					'success' => true,
					'id'      => $insert_id,
					'message' => 'Progress entry created successfully',
				];

				return STOLMC_Service_Tracker_Service_Result_Dto::ok( $data, 201 );
			}

			/**
			 * Fires when a progress creation fails.
			 *
			 * @since 1.0.0
			 *
			 * @param string|false $inserted      The error message.
			 * @param array        $progress_data The progress data that failed.
			 */
			do_action( 'stolmc_service_tracker_progress_create_failed', $inserted, $progress_data );

			return STOLMC_Service_Tracker_Service_Result_Dto::fail(
				'progress_creation_failed',
				'Failed to create progress entry: ' . ( is_string( $inserted ) ? $inserted : 'Unknown error' ),
				500
			);
		} catch ( \Exception $e ) {
			return STOLMC_Service_Tracker_Service_Result_Dto::fail(
				'progress_creation_error',
				'Failed to create progress entry: ' . $e->getMessage(),
				500
			);
		}
	}

	/**
	 * Normalize attachments payload for JSON column storage.
	 *
	 * @param mixed $attachments Raw attachments payload.
	 *
	 * @return string|null Valid JSON string or null.
	 */
	private function normalize_attachments_for_storage( $attachments ): ?string {
		if ( null === $attachments || '' === $attachments ) {
			return null;
		}

		if ( is_array( $attachments ) ) {
			$encoded = wp_json_encode( $attachments );

			return false !== $encoded ? $encoded : null;
		}

		if ( is_string( $attachments ) ) {
			$trimmed = trim( $attachments );
			if ( '' === $trimmed ) {
				return null;
			}

			json_decode( $trimmed, true );
			if ( JSON_ERROR_NONE === json_last_error() ) {
				return $trimmed;
			}

			return null;
		}

		return null;
	}

	/**
	 * Update an existing progress entry.
	 *
	 * @param int   $progress_id Progress entry ID.
	 * @param array $update_data Data to update.
	 *
	 * @return STOLMC_Service_Tracker_Service_Result_Dto Service result.
	 */
	public function update_progress( int $progress_id, array $update_data ): STOLMC_Service_Tracker_Service_Result_Dto {
		try {
			// First, get the progress entry to find its case_id
			$progress = $this->progress_repository->find_by_id( $progress_id );
			if ( ! $progress ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'progress_not_found',
					'Progress entry not found',
					404
				);
			}

			$case_id = (int) $progress->id_case;
			$condition = [ 'id' => $progress_id ];

			/**
			 * Filters the update data before the SQL operation.
			 *
			 * @since 1.0.0
			 *
			 * @param array $update_data The data to update.
			 * @param array $condition   The WHERE condition.
			 */
			$update_data = apply_filters( 'stolmc_service_tracker_progress_update_data', $update_data, $condition );

			// Use the Case Progress Repository to update
			$response = $this->cases_repository->progress( $case_id )->update_by_id( $progress_id, $update_data );

			if ( false === $response ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'progress_update_failed',
					'Failed to update progress entry',
					500
				);
			}

			/**
			 * Fires after a progress entry has been updated.
			 *
			 * @since 1.0.0
			 *
			 * @param int|false|null $response    The update result.
			 * @param array          $update_data The data that was updated.
			 * @param array          $condition   The WHERE condition.
			 */
			do_action( 'stolmc_service_tracker_progress_updated', $response, $update_data, $condition );

			$data = [
				'success' => true,
				'message' => 'Progress entry updated successfully',
				'affected_rows' => $response,
			];

			return STOLMC_Service_Tracker_Service_Result_Dto::ok( $data, 200 );
		} catch ( \Exception $e ) {
			return STOLMC_Service_Tracker_Service_Result_Dto::fail(
				'progress_update_error',
				'Failed to update progress entry: ' . $e->getMessage(),
				500
			);
		}
	}

	/**
	 * Delete a progress entry.
	 *
	 * @param int $progress_id Progress entry ID.
	 *
	 * @return STOLMC_Service_Tracker_Service_Result_Dto Service result.
	 */
	public function delete_progress( int $progress_id ): STOLMC_Service_Tracker_Service_Result_Dto {
		try {
			// First, get the progress entry to find its case_id
			$progress = $this->progress_repository->find_by_id( $progress_id );
			if ( ! $progress ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'progress_not_found',
					'Progress entry not found',
					404
				);
			}

			$case_id = (int) $progress->id_case;

			/**
			 * Fires before a progress entry is deleted.
			 *
			 * @since 1.0.0
			 *
			 * @param int $progress_id The ID of the progress entry to delete.
			 */
			do_action( 'stolmc_service_tracker_progress_before_delete', $progress_id );

			// Use the Case Progress Repository to delete
			$delete = $this->cases_repository->progress( $case_id )->delete_by_id( $progress_id );

			if ( false === $delete ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'progress_deletion_failed',
					'Failed to delete progress entry',
					500
				);
			}

			/**
			 * Fires after a progress entry has been deleted.
			 *
			 * @since 1.0.0
			 *
			 * @param mixed $delete      The delete result.
			 * @param int   $progress_id The ID of the deleted progress entry.
			 */
			do_action( 'stolmc_service_tracker_progress_deleted', $delete, $progress_id );

			$data = [
				'success' => true,
				'message' => 'Progress entry deleted successfully',
				'affected_rows' => $delete,
			];

			return STOLMC_Service_Tracker_Service_Result_Dto::ok( $data, 200 );
		} catch ( \Exception $e ) {
			return STOLMC_Service_Tracker_Service_Result_Dto::fail(
				'progress_deletion_error',
				'Failed to delete progress entry: ' . $e->getMessage(),
				500
			);
		}
	}

	/**
	 * Upload a file attachment for a progress entry.
	 *
	 * @param array $file_data File data from $_FILES.
	 * @param int   $progress_id Progress entry ID.
	 *
	 * @return STOLMC_Service_Tracker_Service_Result_Dto Service result.
	 */
	public function upload_file( array $file_data, int $progress_id ): STOLMC_Service_Tracker_Service_Result_Dto {
		try {
			// Check if file was uploaded
			if ( ! isset( $file_data['file'] ) || ! is_array( $file_data['file'] ) ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'no_file_uploaded',
					'No file was uploaded',
					400
				);
			}

			$file = $file_data['file'];

			// Check for upload errors
			if ( isset( $file['error'] ) && $file['error'] !== UPLOAD_ERR_OK ) {
				$error_message = $this->get_upload_error_message( $file['error'] );
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'upload_error',
					'File upload error: ' . $error_message,
					400
				);
			}

			// Validate file type and size
			$allowed_types = [ 'image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/plain' ];
			$max_size = 5 * 1024 * 1024; // 5MB

			if ( ! in_array( $file['type'], $allowed_types, true ) ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'invalid_file_type',
					'Invalid file type. Allowed types: JPEG, PNG, GIF, PDF, TXT',
					400
				);
			}

			if ( $file['size'] > $max_size ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'file_too_large',
					'File is too large. Maximum size is 5MB',
					400
				);
			}

			// Generate unique filename
			$upload_dir = wp_upload_dir();
			$file_extension = pathinfo( $file['name'], PATHINFO_EXTENSION );
			$unique_filename = wp_unique_filename( $upload_dir['path'], sanitize_file_name( $file['name'] ) );
			$destination = $upload_dir['path'] . '/' . $unique_filename;

			// Move uploaded file
			if ( ! move_uploaded_file( $file['tmp_name'], $destination ) ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'file_move_failed',
					'Failed to move uploaded file',
					500
				);
			}

			// Get the progress entry to update attachments
			$progress = $this->progress_repository->find_by_id( $progress_id );
			if ( ! $progress ) {
				// Clean up the uploaded file since progress entry doesn't exist
				unlink( $destination );
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'progress_not_found',
					'Progress entry not found',
					404
				);
			}

			// Prepare attachment data
			$attachment_url = $upload_dir['url'] . '/' . $unique_filename;
			$attachment_data = [
				'url'  => $attachment_url,
				'type' => $file['type'],
				'name' => $file['name'],
				'size' => $file['size'],
			];

			// Get current attachments
			$current_attachments = $progress->attachments ?? '[]';
			if ( is_string( $current_attachments ) ) {
				$current_attachments = json_decode( $current_attachments, true ) ?? [];
			}

			// Add new attachment
			$current_attachments[] = $attachment_data;

			// Update progress entry with new attachments
			$update_data = [
				'attachments' => wp_json_encode( $current_attachments ),
			];

			// Use update_by_id_for_case method
			$case_id = (int) $progress->id_case;
			$updated = $this->progress_repository->update_by_id_for_case( $progress_id, $case_id, $update_data );

			if ( false === $updated ) {
				// Clean up the uploaded file since update failed
				unlink( $destination );
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'attachment_update_failed',
					'Failed to update progress entry with attachment',
					500
				);
			}

			$data = [
				'success' => true,
				'message' => 'File uploaded successfully',
				'attachment' => $attachment_data,
			];

			return STOLMC_Service_Tracker_Service_Result_Dto::ok( $data, 201 );
		} catch ( \Exception $e ) {
			// Clean up any uploaded file if an exception occurs
			if ( isset( $destination ) && file_exists( $destination ) ) {
				unlink( $destination );
			}

			return STOLMC_Service_Tracker_Service_Result_Dto::fail(
				'file_upload_error',
				'Failed to upload file: ' . $e->getMessage(),
				500
			);
		}
	}

	/**
	 * Get upload error message from error code.
	 *
	 * @param int $error_code Upload error code.
	 *
	 * @return string Error message.
	 */
	private function get_upload_error_message( int $error_code ): string {
		switch ( $error_code ) {
			case UPLOAD_ERR_INI_SIZE:
				return 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
			case UPLOAD_ERR_FORM_SIZE:
				return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
			case UPLOAD_ERR_PARTIAL:
				return 'The uploaded file was only partially uploaded.';
			case UPLOAD_ERR_NO_FILE:
				return 'No file was uploaded.';
			case UPLOAD_ERR_NO_TMP_DIR:
				return 'Missing a temporary folder.';
			case UPLOAD_ERR_CANT_WRITE:
				return 'Failed to write file to disk.';
			case UPLOAD_ERR_EXTENSION:
				return 'A PHP extension stopped the file upload.';
			default:
				return 'Unknown upload error.';
		}
	}
}
