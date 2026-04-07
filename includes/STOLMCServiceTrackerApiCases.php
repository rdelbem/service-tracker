<?php
namespace STOLMCServiceTracker\includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use STOLMCServiceTracker\includes\STOLMCServiceTrackerApi;
use STOLMCServiceTracker\includes\STOLMCServiceTrackerApiContract;
use STOLMCServiceTracker\includes\STOLMCServiceTrackerSql;

/**
 * This class will resolve API calls intended to manipulate the cases table.
 *
 * It extends the API class that serves as a model.
 *
 * ENDPOINT => wp-json/service-tracker/v1/cases/[user_id]
 */
class STOLMCServiceTrackerApiCases extends STOLMCServiceTrackerApi implements STOLMCServiceTrackerApiContract {

	/**
	 * SQL helper instance for cases table operations.
	 *
	 * @var STOLMCServiceTrackerSql
	 */
	private $sql;

	/**
	 * SQL helper instance for progress table operations.
	 *
	 * @var STOLMCServiceTrackerSql
	 */
	private $progress_sql;

	/**
	 * Database table name constant for cases.
	 */
	private const DB = 'servicetracker_cases';

	/**
	 * Database table name constant for progress.
	 */
	private const DB_PROGRESS = 'servicetracker_progress';

	/**
	 * Initialize the API and register routes.
	 *
	 * @return void
	 */
	public function run() {
		global $wpdb;

		$this->custom_api();
		$this->sql = new STOLMCServiceTrackerSql( $wpdb->prefix . self::DB );

		$this->progress_sql = new STOLMCServiceTrackerSql( $wpdb->prefix . self::DB_PROGRESS );
	}

	/**
	 * Register custom API routes for cases management.
	 *
	 * @return void
	 */
	public function custom_api() {

		// RegisterNewRoute -> Method from superclass / extended class.
		$this->register_new_route( 'cases', '_user', WP_REST_Server::READABLE, [ $this, 'read' ] );
		$this->register_new_route( 'cases', '', WP_REST_Server::EDITABLE, [ $this, 'update' ] );
		$this->register_new_route( 'cases', '', WP_REST_Server::DELETABLE, [ $this, 'delete' ] );
		$this->register_new_route( 'cases', '_user', WP_REST_Server::CREATABLE, [ $this, 'create' ] );
	}

	/**
	 * Read cases for a specific user.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return array|null Array of cases or null on failure.
	 */
	public function read( WP_REST_Request $data ) {
		$this->security_check( $data );

		$response = $this->sql->get_by( [ 'id_user' => $data['id_user'] ] );
		return $response;
	}

	/**
	 * Create a new case entry.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return WP_REST_Response Response indicating success or failure.
	 */
	public function create( WP_REST_Request $data ) {
		$this->security_check( $data );

		$body = $data->get_body();
		$body = json_decode( $body );
		$id_user     = $body->id_user;
		$title       = $body->title;
		$status      = isset( $body->status ) ? $body->status : 'open';
		$description = isset( $body->description ) ? $body->description : '';

		global $wpdb;
		$inserted = $this->sql->insert(
			[
				'id_user'     => $id_user,
				'title'       => $title,
				'status'      => $status,
				'description' => $description,
			]
		);

		if ( $wpdb->insert_id ) {
			return new WP_REST_Response(
				[
					'success' => true,
					'id'      => $wpdb->insert_id,
					'message' => 'Case created successfully',
				],
				201
			);
		}

		return new WP_REST_Response(
			[
				'success' => false,
				'message' => 'Failed to create case',
				'error'   => $inserted,
			],
			500
		);
	}

	/**
	 * Update an existing case entry.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return mixed Update result message.
	 */
	public function update( WP_REST_Request $data ) {
		$this->security_check( $data );

		$body = $data->get_body();
		$body = json_decode( $body );

		$title    = $body->title;
		$response = $this->sql->update(
			[ 'title' => $title ],
			[ 'id' => $data['id'] ]
		);
	}

	/**
	 * Delete a case entry and its associated progress records.
	 *
	 * @param WP_REST_Request $data The REST request object.
	 *
	 * @return void
	 */
	public function delete( WP_REST_Request $data ) {
		$this->security_check( $data );
		$delete          = $this->sql->delete( [ 'id' => $data['id'] ] );
		$delete_progress = $this->progress_sql->delete( [ 'id_case' => $data['id'] ] );
	}
}
