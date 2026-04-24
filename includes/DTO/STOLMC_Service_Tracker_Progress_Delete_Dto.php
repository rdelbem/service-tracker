<?php

namespace STOLMC_Service_Tracker\includes\DTO;

class STOLMC_Service_Tracker_Progress_Delete_Dto {
	public int $progress_id;

	public function __construct( int $progress_id ) {
		if ( $progress_id <= 0 ) {
			throw new Validation_Exception( 'Invalid progress ID' );
		}

		$this->progress_id = $progress_id;
	}
}
