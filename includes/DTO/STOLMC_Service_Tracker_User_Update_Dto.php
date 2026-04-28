<?php

namespace STOLMC_Service_Tracker\includes\DTO;

class STOLMC_Service_Tracker_User_Update_Dto {
	public int $user_id;

	/**
	 * @var array<string, mixed>
	 */
	private array $update_data;

	/**
	 * @param int                  $user_id User ID.
	 * @param array<string, mixed> $data Update payload.
	 */
	public function __construct( int $user_id, array $data ) {
		if ( $user_id <= 0 ) {
			throw new STOLMC_Validation_Exception( 'Invalid user ID' );
		}

		if ( isset( $data['email'] ) && '' !== (string) $data['email'] && ! is_email( (string) $data['email'] ) ) {
			throw new STOLMC_Validation_Exception( 'Invalid email address' );
		}

		$this->user_id     = $user_id;
		$this->update_data = $data;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function to_array(): array {
		return $this->update_data;
	}
}
