<?php

namespace STOLMC_Service_Tracker\includes\DTO;

class STOLMC_Service_Tracker_Cases_Read_Query_Dto {
	public int $user_id;
	public int $page;
	public int $per_page;

	public function __construct( int $user_id, int $page = 1, int $per_page = 6 ) {
		if ( $user_id <= 0 ) {
			throw new Validation_Exception( 'Invalid user ID' );
		}

		$this->user_id  = $user_id;
		$this->page     = max( 1, $page );
		$this->per_page = max( 1, $per_page );
	}
}
