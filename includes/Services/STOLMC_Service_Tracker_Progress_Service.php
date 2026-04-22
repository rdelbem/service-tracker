<?php
namespace STOLMC_Service_Tracker\includes\Services;

use Exception;

/**
 * Service class for progress-related business logic.
 *
 * Handles file upload validation and processing for progress entries.
 *
 * @since 1.0.0
 */
class STOLMC_Service_Tracker_Progress_Service {

	/**
	 * Upload files and return file data.
	 *
	 * @param array $files   The files array (typically $_FILES).
	 * @param int   $id_case The case ID.
	 * @param int   $id_user The user ID.
	 *
	 * @return array Array with uploaded files data.
	 * @throws Exception If upload fails or validation fails.
	 */
	public function upload_files( array $files, int $id_case, int $id_user ): array {
		// Check if files were uploaded.
		if ( empty( $files ) ) {
			throw new Exception( 'No files uploaded', 400 );
		}

		if ( ! $id_case || ! $id_user ) {
			throw new Exception( 'Missing id_case or id_user parameter', 400 );
		}

		// Handle file upload using WordPress media handling.
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$upload_overrides = [
			'test_form'        => false,
			'test_size'        => true,
			'test_upload_size' => true,
		];

		$uploaded_files = [];
		$upload_errors  = [];

		// Handle multiple files.
		foreach ( $files as $file_key => $file_array ) {
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
						$error_msg       = isset( $movefile['error'] ) ? $movefile['error'] : 'Unknown error';
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
				$movefile           = wp_handle_upload( $file_array, $upload_overrides );

				if ( $movefile && ! isset( $movefile['error'] ) ) {
					$uploaded_files[] = [
						'url'  => $movefile['url'],
						'type' => $file_array['type'],
						'name' => $file_array['name'],
						'size' => $file_array['size'],
					];
				} else {
					$error_msg       = isset( $movefile['error'] ) ? $movefile['error'] : 'Unknown error';
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

			throw new Exception( $error_message, 500 );
		}

		return $uploaded_files;
	}
}