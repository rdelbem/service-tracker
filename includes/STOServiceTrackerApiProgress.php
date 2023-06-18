<?php
namespace ServiceTracker\includes;

use \WP_REST_Server;
use \WP_REST_Request;
use Rdelbem\WPMailerClass\WPMailerClass;
use ServiceTracker\includes\STOServiceTrackerApi;
use ServiceTracker\includes\STOServiceTrackerSql;
use ServiceTracker\includes\STOServiceTrackerApiContract;

/**
 * This class will resolve api calls intended to manipulate the progress table.
 * It extends the API class that serves as a model.
 *
 * ENDPOINT => wp-json/service-tracker/v1/cases/[user_id]
 */
class STOServiceTrackerApiProgress extends STOServiceTrackerApi implements STOServiceTrackerApiContract
{

	private $sql;

	private const DB = 'servicetracker_progress';

	public function run()
	{
		global $wpdb;

		$this->customApi();
		$this->sql = new STOServiceTrackerSql($wpdb->prefix . self::DB);
	}

	public function customApi()
	{

		// registerNewRoute -> method from superclass / extended class

		$this->registerNewRoute('progress', '_case', WP_REST_Server::READABLE, array($this, 'read'));

		$this->registerNewRoute('progress', '', WP_REST_Server::EDITABLE, array($this, 'update'));

		$this->registerNewRoute('progress', '', WP_REST_Server::DELETABLE, array($this, 'delete'));

		$this->registerNewRoute('progress', '_case', WP_REST_Server::CREATABLE, array($this, 'create'));
	}

	public function read(WP_REST_Request $data)
	{
		$this->securityCheck($data);

		$response = $this->sql->getBy(array('id_case' => $data['id_case']));
		return $response;
	}

	public function create(WP_REST_Request $data)
	{
		$this->securityCheck($data);

		$body = $data->get_body();
		$body = json_decode($body);

		$id_user = $body->id_user;
		$id_case = $body->id_case;
		$text = $body->text;

		// An email will be sent to the customer with the progress info
		$send_mail = new WPMailerClass(
			$id_user,
			__('New status!', 'service-tracker'),
			__('You got a new status: ', 'service-tracker') . $text
		);
		$send_mail->sendEmail();

		return $this->sql->insert(
			array(
				'id_user' => $id_user,
				'id_case' => $id_case,
				'text' => $text,
			)
		);
	}

	public function update(WP_REST_Request $data)
	{
		$this->securityCheck($data);

		$body = $data->get_body();
		$body = json_decode($body);

		$text = $body->text;
		$response = $this->sql->update(
			array('text' => $text),
			array('id' => $data['id'])
		);
	}

	public function delete(WP_REST_Request $data)
	{
		$this->securityCheck($data);
		$delete = $this->sql->delete(array('id' => $data['id']));
	}

}