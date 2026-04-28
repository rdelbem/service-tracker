<?php
namespace STOLMC_Service_Tracker\includes\Controller_API;

use WP_REST_Response;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Service_Result_Dto;

/**
 * Response mapper for canonical v2 API responses.
 *
 * Envelope shape:
 * - success: bool
 * - data: mixed
 * - error_code: string|null
 * - message: string|null
 * - meta: array<string,mixed>
 */
class STOLMC_Service_Tracker_Api_Response_Mapper {

	/**
	 * Build canonical envelope response.
	 *
	 * @param mixed                $data       Response payload.
	 * @param bool                 $success    Operation status.
	 * @param string|null          $message    Human message.
	 * @param string|null          $error_code Error code for failures.
	 * @param int                  $status     HTTP status code.
	 * @param array<string, mixed> $meta       Metadata payload.
	 *
	 * @return WP_REST_Response
	 */
	public static function to_default_response(
		mixed $data,
		bool $success = true,
		?string $message = null,
		?string $error_code = null,
		int $status = 200,
		array $meta = []
	): WP_REST_Response {
		$response = [
			'success'    => $success,
			'data'       => $data,
			'error_code' => $error_code,
			'message'    => $message,
			'meta'       => $meta,
		];

		return new WP_REST_Response( $response, $status );
	}

	/**
	 * Map service result DTO to canonical envelope.
	 *
	 * @param STOLMC_Service_Tracker_Service_Result_Dto $result Service result DTO.
	 *
	 * @return WP_REST_Response
	 */
	public static function from_service_result( STOLMC_Service_Tracker_Service_Result_Dto $result ): WP_REST_Response {
		$data       = $result->data;
		$meta       = [];
		$message    = $result->message;
		$error_code = $result->error_code;

		// Normalize paginated payloads under meta.pagination.
		if ( is_array( $data ) && isset( $data['data'], $data['total'], $data['page'], $data['per_page'] ) ) {
			$meta['pagination'] = [
				'total'       => (int) $data['total'],
				'page'        => (int) $data['page'],
				'per_page'    => (int) $data['per_page'],
				'total_pages' => (int) ( $data['total_pages'] ?? ( (int) $data['per_page'] > 0 ? (int) ceil( (int) $data['total'] / (int) $data['per_page'] ) : 0 ) ),
			];
			$data = $data['data'];
		}

		if ( ! $result->success && ( null === $error_code || '' === trim( $error_code ) ) ) {
			$error_code = 'unknown_error';
		}

		if ( ! $result->success && ( null === $message || '' === trim( $message ) ) ) {
			$message = 'Operation failed';
		}

		return self::to_default_response(
			$data,
			$result->success,
			$message,
			$error_code,
			$result->http_status,
			$meta
		);
	}
}
