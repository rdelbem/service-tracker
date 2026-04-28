<?php

namespace STOLMC_Service_Tracker\includes\DTO;

class STOLMC_Service_Tracker_Calendar_Query_Dto {
	public string $start;
	public string $end;
	public ?int $user_id;
	public ?string $status;

	public function __construct( string $start, string $end, ?int $user_id = null, ?string $status = null ) {
		$start = trim( $start );
		$end   = trim( $end );

		if ( '' === $start || '' === $end ) {
			throw new STOLMC_Validation_Exception( 'Missing required parameters: start and end' );
		}

		if ( ! $this->is_valid_date( $start ) || ! $this->is_valid_date( $end ) ) {
			throw new STOLMC_Validation_Exception( 'Invalid date format. Expected YYYY-MM-DD' );
		}

		if ( strtotime( $start ) > strtotime( $end ) ) {
			throw new STOLMC_Validation_Exception( 'Start date must be before or equal to end date' );
		}

		$this->start   = $start;
		$this->end     = $end;
		$this->user_id = $user_id;
		$this->status  = null !== $status && '' !== trim( $status ) ? trim( $status ) : null;
	}

	private function is_valid_date( string $date ): bool {
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) !== 1 ) {
			return false;
		}

		$date_parts = explode( '-', $date );
		if ( count( $date_parts ) !== 3 ) {
			return false;
		}

		return checkdate( (int) $date_parts[1], (int) $date_parts[2], (int) $date_parts[0] );
	}
}
