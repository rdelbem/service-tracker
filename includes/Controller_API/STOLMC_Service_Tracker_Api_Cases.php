<?php
namespace STOLMC_Service_Tracker\includes\Controller_API;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Cases_Service;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Service_Factory;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Dto_Factory;
use STOLMC_Service_Tracker\includes\DTO\Validation_Exception;

/**
 * This class will resolve API calls intended to manipulate the cases table.
 *
 * It extends the API class that serves as a model.
 *
 * ENDPOINT => wp-json/service-tracker/v1/cases/[user_id]
 */
class STOLMC_Service_Tracker_Api_Cases extends STOLMC_Service_Tracker_Api implements STOLMC_Service_Tracker_Api_Contract {

	/**
	 * Cases Service instance.
	 *
	 * @var STOLMC_Service_Tracker_Cases_Service
	 */
	private $cases_service;

	/**
	 * Number of cases returned per page by default.
	 *
	 * @since 1.3.0
	 */
	private const PER_PAGE_DEFAULT = 6;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->cases_service = STOLMC_Service_Tracker_Service_Factory::create_cases_service();
	}

	/**
	 * Initialize the API and register routes.
	 *
	 * @return void
	 */
	public function run(): void {
		$this->custom_api();

		$this->register_index_invalidation_hooks();
	}

	/**
	 * Register hooks that bust the search index transient when case data changes.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	private function register_index_invalidation_hooks(): void {
		add_action( 'stolmc_service_tracker_case_created', [ $this, 'bust_search_index' ] );
		add_action( 'stolmc_service_tracker_case_updated', [ $this, 'bust_search_index' ] );
		add_action( 'stolmc_service_tracker_case_deleted', [ $this, 'bust_search_index' ] );
	}

	/**
	 * Delete the cached cases search index transient.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	public function bust_search_index(): void {
		$this->cases_service->bust_search_index();
	}

	/**
	 * Register custom API routes for cases management.
	 *
	 * @return void
	 */
	public function custom_api(): void {

		// RegisterNewRoute -> Method from superclass / extended class.
		$this->register_new_route( 'cases', '_user', WP_REST_Server::READABLE, [ $this, 'read' ] );
		$this->register_new_route( 'cases', '', WP_REST_Server::EDITABLE, [ $this, 'update' ] );
		$this->register_new_route( 'cases', '', WP_REST_Server::DELETABLE, [ $this, 'delete' ] );
		$this->register_new_route( 'cases', '_user', WP_REST_Server::CREATABLE, [ $this, 'create' ] );

		// GET /service-tracker-stolmc/v1/cases/search - Search cases with inverted index.
		$this->register_route(
			'/cases/search',
			WP_REST_Server::READABLE,
			[ $this, 'search_cases' ],
			[
				'q'        => [
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'id_user'  => [
					'default'           => 0,
					'sanitize_callback' => 'absint',
				],
				'page'     => [
					'default'           => 1,
					'sanitize_callback' => 'absint',
				],
				'per_page' => [
					'default'           => self::PER_PAGE_DEFAULT,
					'sanitize_callback' => 'absint',
				],
			]
		);
	}

	/**
	 * Read cases for a specific user, with pagination.
	 *
	 * Accepts `page` (1-based) and `per_page` query parameters.
	 * Returns a paginated envelope: { data, total, page, per_page, total_pages }.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Paginated response.
	 */
	public function read( WP_REST_Request $data ): WP_REST_Response {
		try {
			$read_dto = STOLMC_Service_Tracker_Dto_Factory::create_cases_read_query_dto( $data );
		} catch ( Validation_Exception $exception ) {
			return STOLMC_Service_Tracker_Api_Response_Mapper::to_default_response(
				[],
				false,
				$exception->getMessage(),
				'invalid_payload',
				400
			);
		}

		$result = $this->cases_service->get_cases_for_user( $read_dto );

		return STOLMC_Service_Tracker_Api_Response_Mapper::from_service_result( $result );
	}

	/**
	 * Create a new case entry.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Response indicating success or failure.
	 */
	public function create( WP_REST_Request $data ): WP_REST_Response {
		try {
			$create_dto = STOLMC_Service_Tracker_Dto_Factory::create_case_create_dto( $data );
		} catch ( Validation_Exception $exception ) {
			return STOLMC_Service_Tracker_Api_Response_Mapper::to_default_response(
				[],
				false,
				$exception->getMessage(),
				'invalid_json',
				400
			);
		}

		$result = $this->cases_service->create_case( $create_dto );

		return STOLMC_Service_Tracker_Api_Response_Mapper::from_service_result( $result );
	}

	/**
	 * Update an existing case entry.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Update result message.
	 */
	public function update( WP_REST_Request $data ): WP_REST_Response {
		try {
			$update_dto = STOLMC_Service_Tracker_Dto_Factory::create_case_update_dto( $data );
		} catch ( Validation_Exception $exception ) {
			return STOLMC_Service_Tracker_Api_Response_Mapper::to_default_response(
				[],
				false,
				$exception->getMessage(),
				'invalid_json',
				400
			);
		}

		$result = $this->cases_service->update_case( $update_dto );

		return STOLMC_Service_Tracker_Api_Response_Mapper::from_service_result( $result );
	}

	/**
	 * Delete a case entry and its associated progress records.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function delete( WP_REST_Request $data ): WP_REST_Response {
		try {
			$delete_dto = STOLMC_Service_Tracker_Dto_Factory::create_case_delete_dto( $data );
		} catch ( Validation_Exception $exception ) {
			return STOLMC_Service_Tracker_Api_Response_Mapper::to_default_response(
				[],
				false,
				$exception->getMessage(),
				'invalid_payload',
				400
			);
		}

		$result = $this->cases_service->delete_case( $delete_dto );

		return STOLMC_Service_Tracker_Api_Response_Mapper::from_service_result( $result );
	}

	/**
	 * Search cases using the inverted index transient.
	 *
	 * Accepts optional `id_user` to scope results to a single client.
	 * Returns the same paginated envelope as `read()`.
	 *
	 * @since 1.4.0
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Paginated response.
	 */
	public function search_cases( WP_REST_Request $data ): WP_REST_Response {
		$search_dto = STOLMC_Service_Tracker_Dto_Factory::create_cases_search_query_dto( $data );
		$result     = $this->cases_service->search_cases( $search_dto );

		return STOLMC_Service_Tracker_Api_Response_Mapper::from_service_result( $result );
	}
}
