<?php

namespace STOLMC_Service_Tracker\includes\DTO;

use WP_REST_Request;

class STOLMC_Service_Tracker_Dto_Factory {

	public static function create_users_query_dto( WP_REST_Request $request ): STOLMC_Service_Tracker_Users_Query_Dto {
		return new STOLMC_Service_Tracker_Users_Query_Dto(
			(int) ( $request->get_param( 'page' ) ?? 1 ),
			(int) ( $request->get_param( 'per_page' ) ?? 6 ),
			(string) ( $request->get_param( 'q' ) ?? '' )
		);
	}

	public static function create_user_create_dto( WP_REST_Request $request ): STOLMC_Service_Tracker_User_Create_Dto {
		return new STOLMC_Service_Tracker_User_Create_Dto( self::decode_json_body( $request ) );
	}

	public static function create_user_update_dto( WP_REST_Request $request ): STOLMC_Service_Tracker_User_Update_Dto {
		return new STOLMC_Service_Tracker_User_Update_Dto(
			(int) ( $request['id'] ?? 0 ),
			self::decode_json_body( $request )
		);
	}

	public static function create_user_delete_dto( WP_REST_Request $request ): STOLMC_Service_Tracker_User_Delete_Dto {
		return new STOLMC_Service_Tracker_User_Delete_Dto( (int) ( $request['id'] ?? 0 ) );
	}

	public static function create_progress_case_query_dto( WP_REST_Request $request ): STOLMC_Service_Tracker_Progress_Case_Query_Dto {
		return new STOLMC_Service_Tracker_Progress_Case_Query_Dto( (int) ( $request['id_case'] ?? 0 ) );
	}

	public static function create_progress_create_dto( WP_REST_Request $request ): STOLMC_Service_Tracker_Progress_Create_Dto {
		return new STOLMC_Service_Tracker_Progress_Create_Dto( self::decode_json_body( $request ) );
	}

	public static function create_progress_update_dto( WP_REST_Request $request ): STOLMC_Service_Tracker_Progress_Update_Dto {
		return new STOLMC_Service_Tracker_Progress_Update_Dto(
			(int) ( $request['id'] ?? 0 ),
			self::decode_json_body( $request )
		);
	}

	public static function create_progress_delete_dto( WP_REST_Request $request ): STOLMC_Service_Tracker_Progress_Delete_Dto {
		return new STOLMC_Service_Tracker_Progress_Delete_Dto( (int) ( $request['id'] ?? 0 ) );
	}

	public static function create_progress_upload_request_dto( WP_REST_Request $request ): STOLMC_Service_Tracker_Progress_Upload_Request_Dto {
		$files = $request->get_file_params();
		$body  = $request->get_body_params();

		return new STOLMC_Service_Tracker_Progress_Upload_Request_Dto(
			$files,
			$body
		);
	}

	public static function create_progress_user_attachments_query_dto( WP_REST_Request $request ): STOLMC_Service_Tracker_Progress_User_Attachments_Query_Dto {
		return new STOLMC_Service_Tracker_Progress_User_Attachments_Query_Dto( (int) ( $request['id_user'] ?? 0 ) );
	}

	public static function create_calendar_query_dto( WP_REST_Request $request ): STOLMC_Service_Tracker_Calendar_Query_Dto {
		$user_id_param = $request->get_param( 'id_user' );
		$user_id       = null;
		if ( null !== $user_id_param && '' !== (string) $user_id_param ) {
			$user_id = (int) $user_id_param;
			if ( $user_id <= 0 ) {
				$user_id = null;
			}
		}

		$status_param = $request->get_param( 'status' );
		$status       = null;
		if ( null !== $status_param ) {
			$status = (string) $status_param;
		}

		return new STOLMC_Service_Tracker_Calendar_Query_Dto(
			(string) ( $request->get_param( 'start' ) ?? '' ),
			(string) ( $request->get_param( 'end' ) ?? '' ),
			$user_id,
			$status
		);
	}

	public static function create_toggle_request_dto( WP_REST_Request $request ): STOLMC_Service_Tracker_Toggle_Request_Dto {
		$body = self::decode_json_body( $request );

		return new STOLMC_Service_Tracker_Toggle_Request_Dto( (int) ( $body['id'] ?? 0 ) );
	}

	public static function create_analytics_query_dto( WP_REST_Request $request ): STOLMC_Service_Tracker_Analytics_Query_Dto {
		$start_param = $request->get_param( 'start' );
		$end_param   = $request->get_param( 'end' );

		return new STOLMC_Service_Tracker_Analytics_Query_Dto(
			null !== $start_param ? (string) $start_param : null,
			null !== $end_param ? (string) $end_param : null
		);
	}

	public static function create_cases_read_query_dto( WP_REST_Request $request ): STOLMC_Service_Tracker_Cases_Read_Query_Dto {
		return new STOLMC_Service_Tracker_Cases_Read_Query_Dto(
			(int) ( $request['id_user'] ?? 0 ),
			(int) ( $request->get_param( 'page' ) ?? 1 ),
			(int) ( $request->get_param( 'per_page' ) ?? 6 )
		);
	}

	public static function create_cases_search_query_dto( WP_REST_Request $request ): STOLMC_Service_Tracker_Cases_Search_Query_Dto {
		return new STOLMC_Service_Tracker_Cases_Search_Query_Dto(
			(string) ( $request->get_param( 'q' ) ?? '' ),
			(int) ( $request->get_param( 'id_user' ) ?? 0 ),
			(int) ( $request->get_param( 'page' ) ?? 1 ),
			(int) ( $request->get_param( 'per_page' ) ?? 6 )
		);
	}

	public static function create_case_create_dto( WP_REST_Request $request ): STOLMC_Service_Tracker_Case_Create_Dto {
		return new STOLMC_Service_Tracker_Case_Create_Dto( self::decode_json_body( $request ) );
	}

	public static function create_case_update_dto( WP_REST_Request $request ): STOLMC_Service_Tracker_Case_Update_Dto {
		return new STOLMC_Service_Tracker_Case_Update_Dto(
			(int) ( $request['id'] ?? 0 ),
			self::decode_json_body( $request )
		);
	}

	public static function create_case_delete_dto( WP_REST_Request $request ): STOLMC_Service_Tracker_Case_Delete_Dto {
		return new STOLMC_Service_Tracker_Case_Delete_Dto( (int) ( $request['id'] ?? 0 ) );
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function decode_json_body( WP_REST_Request $request ): array {
		$body = $request->get_body();
		if ( '' === trim( $body ) ) {
			throw new STOLMC_Validation_Exception( 'Invalid JSON data' );
		}

		$decoded = json_decode( $body, true );
		if ( ! is_array( $decoded ) ) {
			throw new STOLMC_Validation_Exception( 'Invalid JSON data' );
		}

		return $decoded;
	}
}
