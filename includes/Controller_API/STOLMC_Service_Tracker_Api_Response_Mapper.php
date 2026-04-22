<?php
namespace STOLMC_Service_Tracker\includes\Controller_API;

use WP_REST_Response;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Service_Result_Dto;

/**
 * Response mapper for consistent API response formatting.
 *
 * Provides strategies for different legacy response formats:
 * - passthrough: raw data without envelope
 * - paginated: paginated envelope with data, total, page, per_page, total_pages
 * - legacy_message: success/message envelope for mutations
 * - default: full envelope with success, data, error_code, message
 *
 * @since 1.0.0
 */
class STOLMC_Service_Tracker_Api_Response_Mapper {

	/**
	 * Create a passthrough response (raw data without envelope).
	 *
	 * Used for endpoints that historically return raw arrays:
	 * - Calendar.get_calendar
	 * - Analytics.get_analytics
	 * - Progress.read
	 * - Progress.read_user_attachments
	 * - Users.read_staff
	 *
	 * @param array<string, mixed> $data   The response data.
	 * @param int                  $status HTTP status code.
	 *
	 * @return WP_REST_Response
	 */
	public static function to_passthrough_response( array $data, int $status = 200 ): WP_REST_Response {
		return new WP_REST_Response( $data, $status );
	}

	/**
	 * Create a paginated response with legacy envelope.
	 *
	 * Used for paginated list endpoints:
	 * - Cases.read
	 * - Cases.search_cases
	 * - Users.read
	 * - Users.search_users
	 *
	 * @param array<string, mixed> $data      The paginated data items.
	 * @param int                  $total     Total number of items.
	 * @param int                  $page      Current page number.
	 * @param int                  $per_page  Items per page.
	 * @param int                  $status    HTTP status code.
	 *
	 * @return WP_REST_Response
	 */
	public static function to_paginated_response( array $data, int $total, int $page, int $per_page, int $status = 200 ): WP_REST_Response {
		$total_pages = $per_page > 0 ? (int) ceil( $total / $per_page ) : 0;

		$response = [
			'data'        => $data,
			'total'       => $total,
			'page'        => $page,
			'per_page'    => $per_page,
			'total_pages' => $total_pages,
		];

		return new WP_REST_Response( $response, $status );
	}

	/**
	 * Create a legacy message response for mutations.
	 *
	 * Used for create/update/delete/toggle/upload operations:
	 * - Cases.create/update/delete
	 * - Users.create/update/delete
	 * - Progress.create/update/delete/upload_file
	 * - Toggle.toggle
	 *
	 * @param bool        $success    Whether the operation succeeded.
	 * @param string      $message    Response message.
	 * @param mixed|null  $data       Optional additional data.
	 * @param string|null $error_code Optional error code.
	 * @param int         $status     HTTP status code.
	 *
	 * @return WP_REST_Response
	 */
	public static function to_legacy_message_response( bool $success, string $message, mixed $data = null, ?string $error_code = null, int $status = 200 ): WP_REST_Response {
		$response = [
			'success' => $success,
			'message' => $message,
		];

		if ( null !== $data ) {
			$response['data'] = $data;
		}

		if ( null !== $error_code ) {
			$response['error_code'] = $error_code;
		}

		return new WP_REST_Response( $response, $status );
	}

	/**
	 * Create a default envelope response.
	 *
	 * Used for endpoints that historically use full envelope:
	 * - Default error responses
	 * - Endpoints requiring success, data, error_code, message structure
	 *
	 * @param mixed       $data       The response data.
	 * @param bool        $success    Whether the operation succeeded.
	 * @param string|null $message    Optional message.
	 * @param string|null $error_code Optional error code.
	 * @param int         $status     HTTP status code.
	 *
	 * @return WP_REST_Response
	 */
	public static function to_default_response( mixed $data, bool $success = true, ?string $message = null, ?string $error_code = null, int $status = 200 ): WP_REST_Response {
		$response = [
			'success' => $success,
			'data'    => $data,
		];

		if ( null !== $message ) {
			$response['message'] = $message;
		}

		if ( null !== $error_code ) {
			$response['error_code'] = $error_code;
		}

		return new WP_REST_Response( $response, $status );
	}

	/**
	 * Map a Service Result DTO to appropriate REST response.
	 *
	 * This method intelligently routes the DTO to the appropriate
	 * response strategy based on the data structure and context.
	 *
	 * @param STOLMC_Service_Tracker_Service_Result_Dto $result The service result DTO.
	 *
	 * @return WP_REST_Response
	 */
	public static function from_service_result( STOLMC_Service_Tracker_Service_Result_Dto $result ): WP_REST_Response {
		if ( ! $result->success ) {
			return self::to_default_response(
				$result->data,
				false,
				$result->message,
				$result->error_code,
				$result->http_status
			);
		}

		// Check if data has pagination structure
		if ( is_array( $result->data ) && isset( $result->data['data'], $result->data['total'], $result->data['page'], $result->data['per_page'] ) ) {
			return self::to_paginated_response(
				$result->data['data'],
				$result->data['total'],
				$result->data['page'],
				$result->data['per_page'],
				$result->http_status
			);
		}

		// For mutation operations (create/update/delete), use legacy message format
		// We'll need to determine this from context or data structure
		// For now, use default response for successful operations
		return self::to_default_response(
			$result->data,
			true,
			$result->message,
			$result->error_code,
			$result->http_status
		);
	}

	/**
	 * Create a response from service result with legacy message format.
	 *
	 * Specifically for mutation operations that require the legacy
	 * success/message format.
	 *
	 * @param STOLMC_Service_Tracker_Service_Result_Dto $result The service result DTO.
	 *
	 * @return WP_REST_Response
	 */
	public static function from_service_result_legacy( STOLMC_Service_Tracker_Service_Result_Dto $result ): WP_REST_Response {
		return self::to_legacy_message_response(
			$result->success,
			$result->message ?? ( $result->success ? 'Operation completed successfully' : 'Operation failed' ),
			$result->data,
			$result->error_code,
			$result->http_status
		);
	}

	/**
	 * Create a response from service result with passthrough format.
	 *
	 * For endpoints that need raw data without envelope.
	 *
	 * @param STOLMC_Service_Tracker_Service_Result_Dto $result The service result DTO.
	 *
	 * @return WP_REST_Response
	 */
	public static function from_service_result_passthrough( STOLMC_Service_Tracker_Service_Result_Dto $result ): WP_REST_Response {
		if ( ! $result->success ) {
			return self::to_default_response(
				$result->data,
				false,
				$result->message,
				$result->error_code,
				$result->http_status
			);
		}

		return self::to_passthrough_response(
			$result->data,
			$result->http_status
		);
	}
}