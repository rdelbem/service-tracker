<?php
namespace ServiceTracker\publics;

use ServiceTracker\includes\STOServiceTrackerSql;
use \Moment\Moment;
use \WP_User;

class STOServiceTrackerPublicUserContent
{
	public $current_user_id;
	public $user_cases_and_statuses;

	public function getUserId()
	{

		if (!is_user_logged_in()) {
			return;
		}

		$this->current_user_id = get_current_user_id();
		$this->checkUserRole();
	}

	public function checkUserRole()
	{
		$user = new WP_User($this->current_user_id);

		if (empty($user->roles)) {
			return;
		}

		if (is_array($user->roles) && !in_array('customer', $user->roles)) {
			return;
		}

		if (is_array($user->roles) && in_array('customer', $user->roles)) {
			$this->getStatusesByCases();
			$this->addShortcode();
		}
	}

	public function getUserCases()
	{
		$sql = new STOServiceTrackerSql('servicetracker_cases');
		$cases = $sql->getBy(array('id_user' => $this->current_user_id));
		return $cases;
	}

	public function getCaseProgress($id_case)
	{
		$sql = new STOServiceTrackerSql('servicetracker_progress');
		$status = $sql->getBy(array('id_case' => $id_case));
		$progress_array = array();

		foreach ($status as $stat) {
			$status_obj = array();
			$status_obj['created_at'] = $this->localeTranslationTime($stat->{'created_at'});
			$status_obj['text'] = $stat->{'text'};

			array_push($progress_array, $status_obj);
		}

		return $progress_array;
	}

	public function localeTranslationTime($date)
	{
		$locale = get_locale();

		switch ($locale) {
			case 'pt_BR':
				$time_format = 'd/m/y - H:i:s';
				break;
			case 'en_US':
				$time_format = 'M d/y - h:i:s a';
				break;
			default:
				$time_format = 'm/d/y - h:i:s a';
				break;
		}

		$format_date = new Moment($date);

		return $format_date->format($time_format);
	}

	public function getStatusesByCases()
	{
		$cases = $this->getUserCases();

		$case_and_statuses = array();

		foreach ($cases as $case) {
			$case_obj = array();
			$case_obj['case_title'] = $case->title;
			$case_obj['case_id'] = $case->id;
			$case_obj['created_at'] = $this->localeTranslationTime($case->created_at);
			$case_obj['case_status'] = $case->status;
			$case_obj['progress'] = $this->getCaseProgress($case->id);

			array_push($case_and_statuses, $case_obj);
		}

		$this->user_cases_and_statuses = $case_and_statuses;
	}

	public function addShortcode()
	{
		add_shortcode('service-tracker-cases-progress', array($this, 'usePartial'));
	}

	public function usePartial()
	{
		$user_cases_and_statuses = $this->user_cases_and_statuses;
		include wp_normalize_path(plugin_dir_path(__FILE__) . 'partials/cases_progress.php');
	}

}