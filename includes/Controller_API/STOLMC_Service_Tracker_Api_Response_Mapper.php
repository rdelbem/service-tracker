<?php

namespace STOLMC_Service_Tracker\includes\Controller_API;

use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Service_Result_Dto;
use WP_REST_Response;

/**
 * API Response Mapper for converting service results to REST responses.
 *
 * This class provides methods to map service result DTOs to appropriate
 * REST API responses while preserving external API contracts.
 */
class STOLMC_Service_Tracker_Api_Response_Mapper {

	/**
	 * Convert service result to default REST response.
	 *
	 * Standard shape: {success, data, error_code, message}
	 *
	 * @param STOLMC_Service_Tracker_Service_Result_Dto $result Service result.
	 *
	 * @return WP_REST_Response
	 */
	public static function to_default_response( STOLMC_Service_Tracker_Service_Result_Dto $result ): WP_REST_Response {
		$response_data = [
			'success'    => $result->success,
			'data'       => $result->data,
			'error_code' => $result->error_code,
			'message'    => $result->message,
		];

		return new WP_REST_Response( $response_data, $result->http_status );
	}

	/**
	 * Convert service result to passthrough REST response.
	 *
	 * For endpoints that already have legacy envelopes in result->data.
	 * The entire result->data is passed through as the response.
	 *
	 * @param STOLMC_Service_Tracker_Service_Result_Dto $result Service result.
	 *
	 * @return WP_REST_Response
	 */
	public static function to_passthrough_response( STOLMC_Service_Tracker_Service_Result_Dto $result ): WP_REST_Response {
		$response_data = $result->data;

		// Ensure response_data is an array for REST response.
		if ( ! is_array( $response_data ) ) {
			$response_data = (array) $response_data;
		}

		return new WP_REST_Response( $response_data, $result->http_status );
	}

	/**
	 * Convert service result to legacy message response.
	 *
	 * For legacy {success, message, ...} style responses.
	 *
	 * @param STOLMC_Service_Tracker_Service_Result_Dto $result Service result.
	 * @param array                                     $extra  Extra data to include in response.
	 *
	 * @return WP_REST_Response
	 */
	public static function to_legacy_message_response(
		STOLMC_Service_Tracker_Service_Result_Dto $result,
		array $extra = []
	): WP_REST_Response {
		$response_data = array_merge(
			[
				'success' => $result->success,
				'message' => $result->message,
			],
			$extra
		);

		// Include error_code if present.
		if ( null !== $result->error_code ) {
			$response_data['error_code'] = $result->error_code;
		}

		// Include data if present and not already in extra.
		if ( null !== $result->data && ! isset( $extra['data'] ) ) {
			$response_data['data'] = $result->data;
		}

		return new WP_REST_Response( $response_data, $result->http_status );
	}

	/**
	 * Convert service result to paginated response.
	 *
	 * For endpoints that return paginated data with metadata.
	 *
	 * @param STOLMC_Service_Tracker_Service_Result_Dto $result Service result.
	 * @param array                                     $pagination_meta Pagination metadata.
	 *
	 * @return WP_REST_Response
	 */
	public static function to_paginated_response(
		STOLMC_Service_Tracker_Service_Result_Dto $result,
		array $pagination_meta = []
	): WP_REST_Response {
		$response_data = array_merge(
			[
				'data'       => $result->data,
				'success'    => $result->success,
				'message'    => $result->message,
				'error_code' => $result->error_code,
			],
			$pagination_meta
		);

		return new WP_REST_Response( $response_data, $result->http_status );
	}
}
