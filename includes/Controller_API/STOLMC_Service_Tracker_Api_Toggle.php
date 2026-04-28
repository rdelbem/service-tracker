<?php
namespace STOLMC_Service_Tracker\includes\Controller_API;

use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Toggle_Service;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Service_Factory;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Dto_Factory;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Validation_Exception;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * API class for toggling case statuses.
 */
class STOLMC_Service_Tracker_Api_Toggle extends STOLMC_Service_Tracker_Api {

	/**
	 * Toggle Service instance for case status transitions.
	 *
	 * @var STOLMC_Service_Tracker_Toggle_Service
	 */
	private $toggle_service;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->toggle_service = STOLMC_Service_Tracker_Service_Factory::create_toggle_service();
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
	 * Register custom API routes for case status toggling.
	 *
	 * @return void
	 */
	public function custom_api(): void {

		// Route for toggling cases statuses.
		$this->register_new_route( 'cases-status', '', WP_REST_Server::CREATABLE, [ $this, 'toggle_status' ] );
	}

	/**
	 * Toggle case status between open and closed.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function toggle_status( WP_REST_Request $data ): WP_REST_Response {
		try {
			$toggle_dto = STOLMC_Service_Tracker_Dto_Factory::create_toggle_request_dto( $data );
		} catch ( STOLMC_Validation_Exception $exception ) {
			return STOLMC_Service_Tracker_Api_Response_Mapper::to_default_response(
				[],
				false,
				$exception->getMessage(),
				'invalid_payload',
				400
			);
		}

		$result = $this->toggle_service->toggle_case_status( $toggle_dto );

		return STOLMC_Service_Tracker_Api_Response_Mapper::from_service_result( $result );
	}
}
