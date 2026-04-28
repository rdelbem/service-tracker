<?php
namespace STOLMC_Service_Tracker\includes\Controller_API;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Progress_Service;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Service_Factory;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Dto_Factory;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Validation_Exception;

/**
 * This class will resolve API calls intended to manipulate the progress table.
 *
 * It extends the API class that serves as a model.
 *
 * ENDPOINT => wp-json/service-tracker/v1/progress/[id]
 */
class STOLMC_Service_Tracker_Api_Progress extends STOLMC_Service_Tracker_Api implements STOLMC_Service_Tracker_Api_Contract {

	/**
	 * Progress Service instance.
	 *
	 * @var STOLMC_Service_Tracker_Progress_Service
	 */
	private $progress_service;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->progress_service = STOLMC_Service_Tracker_Service_Factory::create_progress_service();
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
	 * Register custom API routes for progress management.
	 *
	 * @return void
	 */
	public function custom_api(): void {

		// RegisterNewRoute -> Method from superclass / extended class.
		$this->register_new_route( 'progress', '_case', WP_REST_Server::READABLE, [ $this, 'read' ] );
		$this->register_new_route( 'progress', '', WP_REST_Server::EDITABLE, [ $this, 'update' ] );
		$this->register_new_route( 'progress', '', WP_REST_Server::DELETABLE, [ $this, 'delete' ] );
		$this->register_new_route( 'progress', '_case', WP_REST_Server::CREATABLE, [ $this, 'create' ] );

		// Register upload route with custom pattern.
		$this->register_route( '/progress/upload', WP_REST_Server::CREATABLE, [ $this, 'upload_file' ] );

		// Register user attachments route.
		$this->register_route(
			'/progress/user-attachments/(?P<id_user>\d+)',
			WP_REST_Server::READABLE,
			[ $this, 'read_user_attachments' ]
		);
	}

	/**
	 * Read progress entries for a specific case.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Array of progress entries.
	 */
	public function read( WP_REST_Request $data ): WP_REST_Response {
		try {
			$query_dto = STOLMC_Service_Tracker_Dto_Factory::create_progress_case_query_dto( $data );
		} catch ( STOLMC_Validation_Exception $exception ) {
			return STOLMC_Service_Tracker_Api_Response_Mapper::to_default_response(
				[],
				false,
				$exception->getMessage(),
				'invalid_payload',
				400
			);
		}

		$result = $this->progress_service->get_progress_for_case( $query_dto );

		return STOLMC_Service_Tracker_Api_Response_Mapper::from_service_result( $result );
	}

	/**
	 * Create a new progress entry.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Response indicating success or failure.
	 */
	public function create( WP_REST_Request $data ): WP_REST_Response {
		try {
			$create_dto = STOLMC_Service_Tracker_Dto_Factory::create_progress_create_dto( $data );
		} catch ( STOLMC_Validation_Exception $exception ) {
			return STOLMC_Service_Tracker_Api_Response_Mapper::to_default_response(
				[],
				false,
				$exception->getMessage(),
				'invalid_payload',
				400
			);
		}

		$result = $this->progress_service->create_progress( $create_dto );

		return STOLMC_Service_Tracker_Api_Response_Mapper::from_service_result( $result );
	}

	/**
	 * Update an existing progress entry.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Update result message.
	 */
	public function update( WP_REST_Request $data ): WP_REST_Response {
		try {
			$update_dto = STOLMC_Service_Tracker_Dto_Factory::create_progress_update_dto( $data );
		} catch ( STOLMC_Validation_Exception $exception ) {
			return STOLMC_Service_Tracker_Api_Response_Mapper::to_default_response(
				[],
				false,
				$exception->getMessage(),
				'invalid_payload',
				400
			);
		}

		$result = $this->progress_service->update_progress( $update_dto );

		return STOLMC_Service_Tracker_Api_Response_Mapper::from_service_result( $result );
	}

	/**
	 * Delete a progress entry.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function delete( WP_REST_Request $data ): WP_REST_Response {
		try {
			$delete_dto = STOLMC_Service_Tracker_Dto_Factory::create_progress_delete_dto( $data );
		} catch ( STOLMC_Validation_Exception $exception ) {
			return STOLMC_Service_Tracker_Api_Response_Mapper::to_default_response(
				[],
				false,
				$exception->getMessage(),
				'invalid_payload',
				400
			);
		}

		$result = $this->progress_service->delete_progress( $delete_dto );

		return STOLMC_Service_Tracker_Api_Response_Mapper::from_service_result( $result );
	}

	/**
	 * Upload a file attachment for a progress entry.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function upload_file( WP_REST_Request $data ): WP_REST_Response {
		$upload_dto = STOLMC_Service_Tracker_Dto_Factory::create_progress_upload_request_dto( $data );
		$result     = $this->progress_service->handle_upload_request( $upload_dto );

		return STOLMC_Service_Tracker_Api_Response_Mapper::from_service_result( $result );
	}

	/**
	 * Read user attachments.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function read_user_attachments( WP_REST_Request $data ): WP_REST_Response {
		try {
			$query_dto = STOLMC_Service_Tracker_Dto_Factory::create_progress_user_attachments_query_dto( $data );
		} catch ( STOLMC_Validation_Exception $exception ) {
			return STOLMC_Service_Tracker_Api_Response_Mapper::to_default_response(
				[],
				false,
				$exception->getMessage(),
				'invalid_payload',
				400
			);
		}

		$result = $this->progress_service->get_user_attachments( $query_dto );

		return STOLMC_Service_Tracker_Api_Response_Mapper::from_service_result( $result );
	}
}
