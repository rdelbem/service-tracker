<?php

namespace STOLMC_Service_Tracker\includes\Controller_API;

use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Analytics_Service;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Service_Factory;
use STOLMC_Service_Tracker\includes\Controller_API\STOLMC_Service_Tracker_Api_Response_Mapper;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Dto_Factory;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Analytics API endpoint.
 *
 * Provides aggregated analytics data for the admin dashboard.
 *
 * @since    1.2.0
 * @package  STOLMC_Service_Tracker
 */
class STOLMC_Service_Tracker_Api_Analytics extends STOLMC_Service_Tracker_Api {

	/**
	 * Analytics Service instance.
	 *
	 * @var STOLMC_Service_Tracker_Analytics_Service
	 */
	private $analytics_service;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->analytics_service = STOLMC_Service_Tracker_Service_Factory::create_analytics_service();
	}

	/**
	 * Initialize the API and register routes.
	 *
	 * @return void
	 */
	public function run(): void {
		$this->register_route(
			'/analytics',
			WP_REST_Server::READABLE,
			[ $this, 'get_analytics' ],
			[
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);
	}

	/**
	 * Get aggregated analytics data.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function get_analytics( WP_REST_Request $request ): WP_REST_Response {
		$query_dto = STOLMC_Service_Tracker_Dto_Factory::create_analytics_query_dto( $request );

		// Get analytics data from service.
		$service_result = $this->analytics_service->get_analytics( $query_dto );

		// Analytics endpoint historically returns raw payload.
		return STOLMC_Service_Tracker_Api_Response_Mapper::from_service_result( $service_result );
	}
}
