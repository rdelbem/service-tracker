<?php
namespace STOLMC_Service_Tracker\includes\API;

use WP_REST_Request;

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
	 * @return mixed
	 */
	public function read( WP_REST_Request $data ): mixed;

	/**
	 * It verifies the request, then creates a new entry on a table.
	 *
	 * @since    1.0.0
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return mixed
	 */
	public function create( WP_REST_Request $data ): mixed;

	/**
	 * It verifies the request, then updates a certain entry on a table.
	 *
	 * @since    1.0.0
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return mixed
	 */
	public function update( WP_REST_Request $data ): mixed;

	/**
	 * It verifies the request, then it deletes a certain entry on a table.
	 *
	 * @since    1.0.0
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return mixed
	 */
	public function delete( WP_REST_Request $data ): mixed;
}
