<?php
namespace STOLMC_Service_Tracker\includes\Controller_API;

use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Toggle_Service;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Service_Result_Dto;
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
	 * Initialize the API and register routes.
	 *
	 * @return void
	 */
	public function run(): void {
		$this->custom_api();
		$this->toggle_service = new STOLMC_Service_Tracker_Toggle_Service();
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
		$body = $data->get_body();
		$body = json_decode( $body, true );

		if ( ! is_array( $body ) || ! isset( $body['id'] ) ) {
			return new WP_REST_Response(
				[
					'success' => false,
					'message' => 'Invalid JSON data or missing case ID',
				],
				400
			);
		}

		$case_id = (int) $body['id'];

		$result = $this->toggle_service->toggle_case_status( $case_id );

		return STOLMC_Service_Tracker_Api_Response_Mapper::to_legacy_message_response( $result );
	}
}
