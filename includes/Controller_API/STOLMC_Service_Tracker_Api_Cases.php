<?php
namespace STOLMC_Service_Tracker\includes\Controller_API;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Cases_Service;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Service_Result_Dto;

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
	 * Transient key for the cases search inverted index.
	 *
	 * @since 1.4.0
	 */
	private const SEARCH_INDEX_TRANSIENT = 'stolmc_st_case_search_index';

	/**
	 * How long (in seconds) the cases search index transient lives.
	 * Default: 1 hour.
	 *
	 * @since 1.4.0
	 */
	private const SEARCH_INDEX_TTL = 3600;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->cases_service = new STOLMC_Service_Tracker_Cases_Service();
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
	 * Validate JSON request body.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return array|WP_REST_Response Parsed JSON array or error response.
	 */
	private function validate_json_body( WP_REST_Request $data ): array|WP_REST_Response {
		$body = $data->get_body();
		$body = json_decode( $body, true );

		if ( ! is_array( $body ) ) {
			return STOLMC_Service_Tracker_Api_Response_Mapper::to_default_response(
				[],
				false,
				'Invalid JSON data',
				'invalid_json',
				400
			);
		}

		return $body;
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
		$id_user        = (int) $data['id_user'];
		$page_param     = $data->get_param( 'page' );
		$page           = max( 1, (int) ( $page_param ? $page_param : 1 ) );
		$per_page_param = $data->get_param( 'per_page' );
		$per_page       = max( 1, (int) ( $per_page_param ? $per_page_param : self::PER_PAGE_DEFAULT ) );

		$result = $this->cases_service->get_cases_for_user( $id_user, $page, $per_page );

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
		$body = $this->validate_json_body( $data );
		if ( $body instanceof WP_REST_Response ) {
			return $body;
		}

		$result = $this->cases_service->create_case( $body );

		return STOLMC_Service_Tracker_Api_Response_Mapper::from_service_result_legacy( $result );
	}

	/**
	 * Update an existing case entry.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Update result message.
	 */
	public function update( WP_REST_Request $data ): WP_REST_Response {
		$case_id = (int) $data['id'];
		$body = $this->validate_json_body( $data );
		if ( $body instanceof WP_REST_Response ) {
			return $body;
		}

		$result = $this->cases_service->update_case( $case_id, $body );

		return STOLMC_Service_Tracker_Api_Response_Mapper::from_service_result_legacy( $result );
	}

	/**
	 * Delete a case entry and its associated progress records.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function delete( WP_REST_Request $data ): WP_REST_Response {
		$case_id = (int) $data['id'];

		$result = $this->cases_service->delete_case( $case_id );

		return STOLMC_Service_Tracker_Api_Response_Mapper::from_service_result_legacy( $result );
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
		$query          = trim( (string) $data->get_param( 'q' ) );
		$id_user        = (int) $data->get_param( 'id_user' );
		$page_param     = $data->get_param( 'page' );
		$page           = max( 1, (int) ( $page_param ? $page_param : 1 ) );
		$per_page_param = $data->get_param( 'per_page' );
		$per_page       = max( 1, (int) ( $per_page_param ? $per_page_param : self::PER_PAGE_DEFAULT ) );

		// Empty query — fall back to the normal paginated read.
		if ( '' === $query ) {
			return $this->read( $data );
		}

		$result = $this->cases_service->search_cases( $query, $id_user, $page, $per_page );

		return STOLMC_Service_Tracker_Api_Response_Mapper::from_service_result( $result );
	}
}
