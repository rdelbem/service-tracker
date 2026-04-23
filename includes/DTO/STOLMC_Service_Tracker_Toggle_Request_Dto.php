<?php

namespace STOLMC_Service_Tracker\includes\DTO;

class STOLMC_Service_Tracker_Toggle_Request_Dto {
	public int $case_id;

	public function __construct( int $case_id ) {
		if ( $case_id <= 0 ) {
			throw new ValidationException( 'Invalid JSON data or missing case ID' );
		}

		$this->case_id = $case_id;
	}
}
