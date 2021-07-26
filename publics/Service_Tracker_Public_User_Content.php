<?php
namespace ServiceTracker\publics;

use ServiceTracker\includes\Service_Tracker_Sql;
use \WP_User;

class Service_Tracker_Public_User_Content {

	public $current_user_id;
	public $user_cases_and_statuses;

	public function get_user_id() {

		if ( ! is_user_logged_in() ) {
			return;
		}

		$this->current_user_id = get_current_user_id();
		$this->check_user_role();
	}

	public function check_user_role() {
		$user = new WP_User( $this->current_user_id );

		if ( empty( $user->roles ) ) {
			return;
		}

		if ( is_array( $user->roles ) && ! in_array( 'client', $user->roles ) ) {
			return;
		}

		if ( is_array( $user->roles ) && in_array( 'client', $user->roles ) ) {
			$this->get_statuses_by_cases();
			$this->add_shortcode();
		}
	}

	public function get_user_cases() {
		$sql   = new Service_Tracker_Sql( 'servicetracker_cases' );
		$cases = $sql->get_by( array( 'id_user' => $this->current_user_id ) );
		return $cases;
	}

	public function get_case_progress( $id_case ) {
		$sql    = new Service_Tracker_Sql( 'servicetracker_progress' );
		$status = $sql->get_by( array( 'id_case' => $id_case ) );
		return $status;
	}

	public function get_statuses_by_cases() {
		$cases = $this->get_user_cases();

		$case_and_statuses = array();

		foreach ( $cases as $case ) {
			 $case_obj                = array();
			 $case_obj['case_title']  = $case->title;
			 $case_obj['case_id']     = $case->id;
			 $case_obj['created_at']  = $case->created_at;
			 $case_obj['case_status'] = $case->status;
			 $case_obj['progress']    = $this->get_case_progress( $case->id );

			 array_push( $case_and_statuses, $case_obj );
		}

		$this->user_cases_and_statuses = $case_and_statuses;
	}

	public function add_shortcode() {
		add_shortcode( 'service-tracker-cases-progress', array( $this, 'use_partial' ) );
	}

	public function use_partial() {
		$user_cases_and_statuses = $this->user_cases_and_statuses;
		include wp_normalize_path( plugin_dir_path( __FILE__ ) . 'partials/cases_progress.php' );
	}

}
