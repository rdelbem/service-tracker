<?php
namespace STOLMC_Service_Tracker\includes\Controller_API;

use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Calendar_Service;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Service_Factory;
use STOLMC_Service_Tracker\includes\Controller_API\STOLMC_Service_Tracker_Api_Response_Mapper;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Dto_Factory;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Validation_Exception;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

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
	 * Constructor.
	 */
	public function __construct() {
		$this->calendar_service = STOLMC_Service_Tracker_Service_Factory::create_calendar_service();
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
	 * Register custom API routes for calendar aggregation.
	 *
	 * @return void
	 */
	public function custom_api(): void {
		$this->register_route( '/calendar', WP_REST_Server::READABLE, [ $this, 'get_calendar' ] );
	}

	/**
	 * Get calendar data aggregated from cases and progress.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Calendar data with cases and progress entries.
	 */
	public function get_calendar( WP_REST_Request $data ): WP_REST_Response {
		try {
			$query_dto = STOLMC_Service_Tracker_Dto_Factory::create_calendar_query_dto( $data );
		} catch ( STOLMC_Validation_Exception $exception ) {
			return STOLMC_Service_Tracker_Api_Response_Mapper::to_default_response(
				[],
				false,
				$exception->getMessage(),
				'invalid_payload',
				400
			);
		}

		// Get calendar data from service.
		$service_result = $this->calendar_service->get_calendar_data( $query_dto );

		// Calendar endpoint historically returns raw payload (cases/progress/date_index).
		return STOLMC_Service_Tracker_Api_Response_Mapper::from_service_result( $service_result );
	}
}
