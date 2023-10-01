<?php
namespace STOLMCServiceTracker\includes;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use Rdelbem\WPMailerClass\WPMailerClass;
use STOLMCServiceTracker\includes\STOLMCServiceTrackerSql;
use STOLMCServiceTracker\includes\STOLMCServiceTrackerApi;
use \WP_REST_Server;
use \WP_REST_Request;

class STOLMCServiceTrackerApiToggle extends STOLMCServiceTrackerApi
{

	private $sql;

	/**
	 * The messages that will be sent over email
	 *
	 * @var array
	 */
	private $closed;

	/**
	 * The messages that will be sent over email
	 *
	 * @var array
	 */
	private $opened;

	private const DB = 'servicetracker_cases';

	public function __construct()
	{
		$this->closed = array(__('Your case was closed!', 'service-tracker'), __('is now closed!', 'service-tracker'));
		$this->opened = array(__('Your case was opened!', 'service-tracker'), __('is now opened!', 'service-tracker'));
	}

	public function run()
	{
		global $wpdb;

		$this->customApi();
		$this->sql = new STOLMCServiceTrackerSql($wpdb->prefix . self::DB);
	}

	public function customApi()
	{
		// Route for toggleling cases statuses
		$this->registerNewRoute('cases-status', '', WP_REST_Server::CREATABLE, array($this, 'toggle_status'));
	}

	public function sendEmail($id_user, $title, $case_state_msg)
	{
		$send_mail = new WPMailerClass(
			$id_user,
			$case_state_msg[0],
			$title . ' - ' . $case_state_msg[1]
		);
		$send_mail->sendEmail();
	}

	/**
	 * A case status progress always has a state, which indicates wether it is
	 * opened, still in progress, or closed, it has been concluded.
	 *
	 * @param WP_REST_Request $data
	 */
	public function toggle_status(WP_REST_Request $data)
	{

		$this->securityCheck($data);

		$response = $this->sql->getBy(array('id' => $data['id']));
		$response = (array) $response[0];
		$id_user = $response['id_user'];
		$title = $response['title'];

		if ($response['status'] === 'open') {
			$toggle = $this->sql->update(
				array('status' => 'close'),
				array('id' => $data['id'])
			);

			$this->sendEmail($id_user, $title, $this->closed);

			return $toggle;
		}

		if ($response['status'] === 'close') {
			$toggle = $this->sql->update(
				array('status' => 'open'),
				array('id' => $data['id'])
			);

			$this->sendEmail($id_user, $title, $this->opened);

			return $toggle;
		}
	}
}