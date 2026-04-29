<?php
namespace STOLMC_Service_Tracker\includes\Controller_API;

defined( 'ABSPATH' ) || exit;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Users_Service;
use STOLMC_Service_Tracker\includes\Application\STOLMC_Service_Tracker_Service_Factory;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Dto_Factory;

/**
 * This class handles user-related REST API operations.
 *
 * Provides read-only endpoints for listing and searching users.
 * User creation, update, and deletion are handled through
 * standard WordPress / WooCommerce flows.
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
	 * Constructor.
	 */
	public function __construct() {
		$this->users_service = STOLMC_Service_Tracker_Service_Factory::create_users_service();
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
	 * Only read-only endpoints are exposed. User creation, update, and deletion
	 * are handled through standard WordPress / WooCommerce flows.
	 *
	 * @return void
	 */
	public function custom_api(): void {

		// GET /users - List users with pagination (no ID required).
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
		$query_dto = STOLMC_Service_Tracker_Dto_Factory::create_users_query_dto( $data );
		$result    = $this->users_service->get_users( $query_dto );

		return STOLMC_Service_Tracker_Api_Response_Mapper::from_service_result( $result );
	}

	/**
	 * Search users using the inverted index transient.
	 *
	 * Returns canonical v2 envelope with pagination in meta.pagination.
	 *
	 * @since 1.4.0
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Paginated response.
	 */
	public function search_users( WP_REST_Request $data ): WP_REST_Response {
		$query_dto = STOLMC_Service_Tracker_Dto_Factory::create_users_query_dto( $data );
		$result    = $this->users_service->search_users( $query_dto );

		return STOLMC_Service_Tracker_Api_Response_Mapper::from_service_result( $result );
	}

	/**
	 * Read staff/admin users.
	 *
	 * Returns canonical v2 envelope.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function read_staff( WP_REST_Request $data ): WP_REST_Response {
		$result = $this->users_service->get_staff_users();

		return STOLMC_Service_Tracker_Api_Response_Mapper::from_service_result( $result );
	}
}
