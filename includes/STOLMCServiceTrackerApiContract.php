<?php
namespace STOLMCServiceTracker\includes;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use \WP_REST_Request;

/**
 * This is the required contract/interface used in order
 * to implement a full CRUD rest api end point.
 */
interface STOLMCServiceTrackerApiContract
{
	/**
	 * The method run is used to start the application.
	 * It is necessary because it is add to the app with
	 * the WordPress hook add_action
	 *
	 * @return void
	 */
	public function run();

	/**
	 * This method registers the end point, it is done
	 * by calling the extended class method registerNewRoute.
	 *
	 * @return void
	 */
	public function customApi();

	/**
	 * It verifys the request, then reads a table.
	 *
	 * @param WP_REST_Request $data
	 * @return void
	 */
	public function read(WP_REST_Request $data);

	/**
	 * It verifys the request, then creates an new entry on a table.
	 *
	 * @param WP_REST_Request $data
	 * @return void
	 */
	public function create(WP_REST_Request $data);

	/**
	 * It verifys the request, then updates a certain entry on a table.
	 *
	 * @param WP_REST_Request $data
	 * @return void
	 */
	public function update(WP_REST_Request $data);

	/**
	 * It verifys the request, then it deletes a certain entry on a table.
	 *
	 * @param WP_REST_Request $data
	 * @return void
	 */
	public function delete(WP_REST_Request $data);
}