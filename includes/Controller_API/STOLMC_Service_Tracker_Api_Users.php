<?php
namespace STOLMC_Service_Tracker\includes\Controller_API;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Users_Service;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Service_Result_Dto;

/**
 * This class handles user-related REST API operations.
 *
 * Specifically, creating new customer users from the admin interface.
 *
 * ENDPOINT => wp-json/service-tracker-stolmc/v1/users
 */
class STOLMC_Service_Tracker_Api_Users extends STOLMC_Service_Tracker_Api {

	/**
	 * Users Service instance.
	 *
	 * @var STOLMC_Service_Tracker_Users_Service
	 */
	private $users_service;

	/**
	 * Number of users returned per page by default.
	 *
	 * @since 1.3.0
	 */
	private const PER_PAGE_DEFAULT = 6;

	/**
	 * Transient key for the user search inverted index.
	 *
	 * @since 1.4.0
	 */
	private const SEARCH_INDEX_TRANSIENT = 'stolmc_st_user_search_index';

	/**
	 * How long (in seconds) the search index transient lives.
	 * Default: 1 hour.
	 *
	 * @since 1.4.0
	 */
	private const SEARCH_INDEX_TTL = 3600;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->users_service = new STOLMC_Service_Tracker_Users_Service();
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
	 * Register hooks that bust the search index transient when user data changes.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	private function register_index_invalidation_hooks(): void {
		add_action( 'stolmc_service_tracker_user_created', [ $this, 'bust_search_index' ] );
		add_action( 'stolmc_service_tracker_user_updated', [ $this, 'bust_search_index' ] );
		add_action( 'stolmc_service_tracker_user_deleted', [ $this, 'bust_search_index' ] );
	}

	/**
	 * Delete the cached user search index transient.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	 */
	public function bust_search_index(): void {
		$this->users_service->bust_search_index();
	}

	/**
	 * Register custom API routes for users management.
	 *
	 * @return void
	 */
	public function custom_api(): void {

		// GET /users - List users with pagination (no ID required)
		$this->register_route(
			'/users',
			WP_REST_Server::READABLE,
			[ $this, 'read' ],
			[
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

		// RegisterNewRoute -> Method from superclass / extended class.
		// These routes require ID parameter
		$this->register_new_route( 'users', '', WP_REST_Server::EDITABLE, [ $this, 'update' ] );
		$this->register_new_route( 'users', '', WP_REST_Server::DELETABLE, [ $this, 'delete' ] );
		$this->register_new_route( 'users', '', WP_REST_Server::CREATABLE, [ $this, 'create' ] );

		// GET /service-tracker-stolmc/v1/users/search - Search users with inverted index.
		$this->register_route(
			'/users/search',
			WP_REST_Server::READABLE,
			[ $this, 'search_users' ],
			[
				'q'        => [
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
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

		// GET /service-tracker-stolmc/v1/users/staff - List staff/admin users.
		$this->register_route(
			'/users/staff',
			WP_REST_Server::READABLE,
			[ $this, 'read_staff' ]
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
	 * Read users with pagination.
	 *
	 * Accepts `page` (1-based) and `per_page` query parameters.
	 * Returns a paginated envelope: { data, total, page, per_page, total_pages }.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Paginated response.
	 */
	public function read( WP_REST_Request $data ): WP_REST_Response {
		$page_param     = $data->get_param( 'page' );
		$page           = max( 1, (int) ( $page_param ? $page_param : 1 ) );
		$per_page_param = $data->get_param( 'per_page' );
		$per_page       = max( 1, (int) ( $per_page_param ? $per_page_param : self::PER_PAGE_DEFAULT ) );

		$result = $this->users_service->get_users( $page, $per_page );

		return STOLMC_Service_Tracker_Api_Response_Mapper::from_service_result( $result );
	}

	/**
	 * Create a new user entry.
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

		$result = $this->users_service->create_user( $body );

		return STOLMC_Service_Tracker_Api_Response_Mapper::from_service_result_legacy( $result );
	}

	/**
	 * Update an existing user entry.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Update result message.
	 */
	public function update( WP_REST_Request $data ): WP_REST_Response {
		$user_id = (int) $data['id'];
		$body = $this->validate_json_body( $data );
		if ( $body instanceof WP_REST_Response ) {
			return $body;
		}

		$result = $this->users_service->update_user( $user_id, $body );

		return STOLMC_Service_Tracker_Api_Response_Mapper::from_service_result_legacy( $result );
	}

	/**
	 * Delete a user entry.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function delete( WP_REST_Request $data ): WP_REST_Response {
		$user_id = (int) $data['id'];

		$result = $this->users_service->delete_user( $user_id );

		return STOLMC_Service_Tracker_Api_Response_Mapper::from_service_result_legacy( $result );
	}

	/**
	 * Search users using the inverted index transient.
	 *
	 * Returns a paginated envelope: { data, total, page, per_page, total_pages }.
	 *
	 * @since 1.4.0
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Paginated response.
	 */
	public function search_users( WP_REST_Request $data ): WP_REST_Response {
		$query          = trim( (string) $data->get_param( 'q' ) );
		$page_param     = $data->get_param( 'page' );
		$page           = max( 1, (int) ( $page_param ? $page_param : 1 ) );
		$per_page_param = $data->get_param( 'per_page' );
		$per_page       = max( 1, (int) ( $per_page_param ? $per_page_param : self::PER_PAGE_DEFAULT ) );

		// Empty query — fall back to normal paginated read.
		if ( '' === $query ) {
			return $this->read( $data );
		}

		$result = $this->users_service->search_users( $query, $page, $per_page );

		return STOLMC_Service_Tracker_Api_Response_Mapper::from_service_result( $result );
	}

	/**
	 * Read staff/admin users.
	 *
	 * Returns a plain array for legacy frontend compatibility.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function read_staff( WP_REST_Request $data ): WP_REST_Response {
		$result = $this->users_service->get_staff_users();

		return STOLMC_Service_Tracker_Api_Response_Mapper::from_service_result_passthrough( $result );
	}
}
