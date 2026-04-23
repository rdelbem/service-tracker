<?php

namespace STOLMC_Service_Tracker\includes\DTO;

class STOLMC_Service_Tracker_User_Delete_Dto {
	public int $user_id;

	public function __construct( int $user_id ) {
		if ( $user_id <= 0 ) {
			throw new ValidationException( 'Invalid user ID' );
		}

		$this->user_id = $user_id;
	}
}
