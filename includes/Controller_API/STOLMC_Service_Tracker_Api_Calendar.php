<?php
namespace STOLMC_Service_Tracker\includes\Controller_API;

use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Calendar_Service;
use STOLMC_Service_Tracker\includes\Controller_API\STOLMC_Service_Tracker_Api_Response_Mapper;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use STOLMC_Service_Tracker\includes\DB\CalendarIndex;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Calendar_Payload_Dto;
use STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Calendar_Repository;

/**
 * Calendar API class for aggregating case and progress data.
 *
 * Provides a dedicated endpoint for calendar views that aggregates
 * cases with date ranges and progress entries as date markers.
 *
 * ENDPOINT => wp-json/service-tracker-stolmc/v1/calendar
 */
class STOLMC_Service_Tracker_Api_Calendar extends STOLMC_Service_Tracker_Api implements STOLMC_Service_Tracker_Api_Contract {

	/**
	 * Calendar Service instance.
	 *
	 * @var STOLMC_Service_Tracker_Calendar_Service
	 */
	private $calendar_service;

	/**
	 * Initialize the API and register routes.
	 *
	 * @return void
	 */
	public function run(): void {
		$this->custom_api();
		$this->calendar_service = new STOLMC_Service_Tracker_Calendar_Service();
	}

	/**
	 * Register custom API routes for calendar aggregation.
	 *
	 * @return void
	 */
	public function custom_api(): void {
		$this->register_route( '/calendar', WP_REST_Server::READABLE, [ $this, 'get_calendar' ] );
	}

	/**
	 * Read method (required by contract, not used for calendar).
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Method not allowed response.
	 */
	public function read( WP_REST_Request $data ): WP_REST_Response {
		return $this->get_calendar( $data );
	}

	/**
	 * Create method (not applicable for calendar).
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Method not allowed response.
	 */
	public function create( WP_REST_Request $data ): WP_REST_Response {
		return $this->rest_response(
			[
				'success' => false,
				'message' => 'Method not allowed',
			],
			405
		);
	}

	/**
	 * Update method (not applicable for calendar).
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Method not allowed response.
	 */
	public function update( WP_REST_Request $data ): WP_REST_Response {
		return $this->rest_response(
			[
				'success' => false,
				'message' => 'Method not allowed',
			],
			405
		);
	}

	/**
	 * Delete method (not applicable for calendar).
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Method not allowed response.
	 */
	public function delete( WP_REST_Request $data ): WP_REST_Response {
		return $this->rest_response(
			[
				'success' => false,
				'message' => 'Method not allowed',
			],
			405
		);
	}

	/**
	 * Get calendar data aggregated from cases and progress.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Calendar data with cases and progress entries.
	 */
	public function get_calendar( WP_REST_Request $data ): WP_REST_Response {
		$start = $data->get_param( 'start' );
		$end = $data->get_param( 'end' );
		$id_user = $data->get_param( 'id_user' );
		$status = $data->get_param( 'status' );

		// Get calendar data from service.
		$service_result = $this->calendar_service->get_calendar_data( $start, $end, $id_user, $status );

		// Calendar endpoint historically returns raw payload (cases/progress/date_index).
		return STOLMC_Service_Tracker_Api_Response_Mapper::from_service_result_passthrough( $service_result );
	}
}
