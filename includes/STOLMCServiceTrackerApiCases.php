<?php
namespace STOLMCServiceTracker\includes;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use STOLMCServiceTracker\includes\STOLMCServiceTrackerApiContract;
use STOLMCServiceTracker\includes\STOLMCServiceTrackerSql;
use STOLMCServiceTracker\includes\STOLMCServiceTrackerApi;
use \WP_REST_Server;
use \WP_REST_Request;

// The database name used for this class -> servicetracker_cases

/**
 * This class will resolve api calls intended to manipulate the cases table.
 * It extends the API class that serves as a model.
 *
 * ENDPOINT => wp-json/service-tracker/v1/progress/[case_id]
 */
class STOLMCServiceTrackerApiCases extends STOLMCServiceTrackerApi implements STOLMCServiceTrackerApiContract
{

	private $sql;

	private $progressSql;

	private const DB = 'servicetracker_cases';

	private const DB_PROGRESS = 'servicetracker_progress';

	public function run()
	{
		global $wpdb;

		$this->customApi();
		$this->sql = new STOLMCServiceTrackerSql($wpdb->prefix . self::DB);

		$this->progressSql = new STOLMCServiceTrackerSql($wpdb->prefix . self::DB_PROGRESS);
	}

	public function customApi()
	{
		// registerNewRoute -> method from superclass / extended class
		$this->registerNewRoute('cases', '_user', WP_REST_Server::READABLE, array($this, 'read'));
		$this->registerNewRoute('cases', '', WP_REST_Server::EDITABLE, array($this, 'update'));
		$this->registerNewRoute('cases', '', WP_REST_Server::DELETABLE, array($this, 'delete'));
		$this->registerNewRoute('cases', '_user', WP_REST_Server::CREATABLE, array($this, 'create'));
	}

	public function read(WP_REST_Request $data)
	{
		$this->securityCheck($data);

		$response = $this->sql->getBy(array('id_user' => $data['id_user']));
		return $response;
	}

	public function create(WP_REST_Request $data)
	{
		$this->securityCheck($data);

		$body = $data->get_body();
		$body = json_decode($body);
		$id_user = $body->id_user;
		$title = $body->title;

		return $this->sql->insert(
			array(
				'id_user' => $id_user,
				'title' => $title,
				'status' => 'open',
			)
		);
	}

	public function update(WP_REST_Request $data)
	{
		$this->securityCheck($data);

		$body = $data->get_body();
		$body = json_decode($body);

		$title = $body->title;
		$response = $this->sql->update(
			array('title' => $title),
			array('id' => $data['id'])
		);
	}

	public function delete(WP_REST_Request $data)
	{
		$this->securityCheck($data);
		$delete = $this->sql->delete(array('id' => $data['id']));
		$deleteProgress = $this->progressSql->delete(['id_case' => $data['id']]);
	}

}