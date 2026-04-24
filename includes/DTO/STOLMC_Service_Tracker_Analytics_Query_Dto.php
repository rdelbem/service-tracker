<?php

namespace STOLMC_Service_Tracker\includes\DTO;

class STOLMC_Service_Tracker_Analytics_Query_Dto {
	public ?string $start;
	public ?string $end;

	public function __construct( ?string $start = null, ?string $end = null ) {
		$this->start = $this->normalize_optional_string( $start );
		$this->end   = $this->normalize_optional_string( $end );
	}

	private function normalize_optional_string( ?string $value ): ?string {
		if ( null === $value ) {
			return null;
		}

		$normalized = trim( $value );
		if ( '' === $normalized ) {
			return null;
		}

		return $normalized;
	}
}
