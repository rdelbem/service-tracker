<?php

namespace STOLMC_Service_Tracker\includes\Application;

use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Service_Result_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Calendar_Query_Dto;
use STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Calendar_Repository;

/**
 * Calendar Service for business logic operations on calendar data.
 *
 * This service encapsulates all business logic for calendar operations
 * and returns uniform Service Result DTOs.
 */
class STOLMC_Service_Tracker_Calendar_Service {

	/**
	 * Calendar Repository instance.
	 *
	 * @var STOLMC_Service_Tracker_Calendar_Repository
	 */
	private $calendar_repository;

	/**
	 * Constructor.
	 *
	 * @param STOLMC_Service_Tracker_Calendar_Repository|null $calendar_repository Calendar repository.
	 */
	public function __construct( ?STOLMC_Service_Tracker_Calendar_Repository $calendar_repository = null ) {
		$this->calendar_repository = $calendar_repository ?? new STOLMC_Service_Tracker_Calendar_Repository();
	}

	/**
	 * Get calendar data aggregated from cases and progress.
	 *
	 * @param STOLMC_Service_Tracker_Calendar_Query_Dto $query_dto Query DTO.
	 *
	 * @return STOLMC_Service_Tracker_Service_Result_Dto Service result.
	 */
	public function get_calendar_data( STOLMC_Service_Tracker_Calendar_Query_Dto $query_dto ): STOLMC_Service_Tracker_Service_Result_Dto {
		try {
			$start   = $query_dto->start;
			$end     = $query_dto->end;
			$user_id = $query_dto->user_id;
			$status  = $query_dto->status;

			// Build query args for filtering.
			$query_args = [];
			if ( ! empty( $user_id ) ) {
				$query_args['id_user'] = $user_id;
			}

			if ( ! empty( $status ) ) {
				$query_args['status'] = $status;
			}

			/**
			 * Filters the calendar query arguments before fetching cases.
			 * Only real database column names should be included.
			 *
			 * @since 1.0.0
			 *
			 * @param array $query_args The query arguments.
			 * @param string $start Start date.
			 * @param string $end End date.
			 */
			$query_args = apply_filters( 'stolmc_service_tracker_calendar_query_args', $query_args, $start, $end );

			// Extract id_user and status from filtered query args.
			$filtered_user_id = $query_args['id_user'] ?? null;
			$filtered_status = $query_args['status'] ?? null;

			// Get calendar data from Repository.
			$calendar_payload = $this->calendar_repository->find_calendar_data(
				$start,
				$end,
				$filtered_user_id,
				$filtered_status
			);

			$calendar_data = $calendar_payload->to_array();

			/**
			 * Filters the calendar cases response.
			 *
			 * @since 1.0.0
			 *
			 * @param array $calendar_cases The calendar cases data.
			 * @param string $start Start date.
			 * @param string $end End date.
			 * @param int|null $user_id User ID.
			 * @param string|null $status Status.
			 */
			$calendar_data['cases'] = apply_filters( 'stolmc_service_tracker_calendar_cases_response', $calendar_data['cases'], $start, $end, $user_id, $status );

			/**
			 * Filters the calendar progress response.
			 *
			 * @since 1.0.0
			 *
			 * @param array $calendar_progress The calendar progress data.
			 * @param string $start Start date.
			 * @param string $end End date.
			 * @param int|null $user_id User ID.
			 * @param string|null $status Status.
			 */
			$calendar_data['progress'] = apply_filters( 'stolmc_service_tracker_calendar_progress_response', $calendar_data['progress'], $start, $end, $user_id, $status );

			/**
			 * Filters the final calendar payload before returning.
			 *
			 * @since 1.0.0
			 *
			 * @param array $payload The complete calendar payload.
			 * @param string $start Start date.
			 * @param string $end End date.
			 * @param int|null $user_id User ID.
			 * @param string|null $status Status.
			 */
			$calendar_data = apply_filters( 'stolmc_service_tracker_calendar_payload', $calendar_data, $start, $end, $user_id, $status );

			return STOLMC_Service_Tracker_Service_Result_Dto::ok( $calendar_data, 200 );
		} catch ( \Exception $e ) {
			return STOLMC_Service_Tracker_Service_Result_Dto::fail(
				'calendar_data_error',
				'Failed to get calendar data: ' . $e->getMessage(),
				500
			);
		}
	}
}
