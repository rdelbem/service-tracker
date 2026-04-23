<?php

namespace STOLMC_Service_Tracker\includes\DTO;

class STOLMC_Service_Tracker_Case_Delete_Dto {
	public int $case_id;

	public function __construct( int $case_id ) {
		if ( $case_id <= 0 ) {
			throw new ValidationException( 'Invalid case ID' );
		}

		$this->case_id = $case_id;
	}
}
