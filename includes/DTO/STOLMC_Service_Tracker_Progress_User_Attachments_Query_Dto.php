<?php

namespace STOLMC_Service_Tracker\includes\DTO;

class STOLMC_Service_Tracker_Progress_User_Attachments_Query_Dto {
	public int $user_id;

	public function __construct( int $user_id ) {
		if ( $user_id <= 0 ) {
			throw new Validation_Exception( 'Missing user ID parameter' );
		}

		$this->user_id = $user_id;
	}
}
