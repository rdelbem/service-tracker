<?php
namespace STOLMC_Service_Tracker\includes\Controller_API;

/**
 * Base contract for API controllers.
 *
 * This contract defines only route lifecycle methods and does not force CRUD
 * handlers for non-CRUD endpoints.
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
}
