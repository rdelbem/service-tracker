<?php

namespace STOLMC_Service_Tracker\includes\DTO;

class STOLMC_Service_Tracker_Progress_Update_Dto {
	public int $progress_id;

	/**
	 * @var array<string, mixed>
	 */
	private array $update_data;

	/**
	 * @param int                  $progress_id Progress ID.
	 * @param array<string, mixed> $data Update payload.
	 */
	public function __construct( int $progress_id, array $data ) {
		if ( $progress_id <= 0 ) {
			throw new ValidationException( 'Invalid progress ID' );
		}

		$this->progress_id = $progress_id;
		$this->update_data = $data;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function to_array(): array {
		return $this->update_data;
	}
}
