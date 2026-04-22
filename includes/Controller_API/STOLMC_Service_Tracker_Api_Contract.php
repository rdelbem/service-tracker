<?php
namespace STOLMC_Service_Tracker\includes\Controller_API;

use WP_REST_Request;
use WP_REST_Response;

/**
 * This is the required contract/interface used in order
 * to implement a full CRUD REST API end point.
 */
interface STOLMC_Service_Tracker_Api_Contract {

	/**
	 * The method run is used to start the application.
	 *
	 * It is necessary because it is added to the app with
	 * the WordPress hook add_action().
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function run(): void;

	/**
	 * This method registers the end point, it is done
	 * by calling the extended class method register_new_route().
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function custom_api(): void;

	/**
	 * It verifies the request, then reads a table.
	 *
	 * @since    1.0.0
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function read( WP_REST_Request $data ): WP_REST_Response;

	/**
	 * It verifies the request, then creates a new entry on a table.
	 *
	 * @since    1.0.0
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function create( WP_REST_Request $data ): WP_REST_Response;

	/**
	 * It verifies the request, then updates a certain entry on a table.
	 *
	 * @since    1.0.0
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function update( WP_REST_Request $data ): WP_REST_Response;

	/**
	 * It verifies the request, then it deletes a certain entry on a table.
	 *
	 * @since    1.0.0
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function delete( WP_REST_Request $data ): WP_REST_Response;
}
