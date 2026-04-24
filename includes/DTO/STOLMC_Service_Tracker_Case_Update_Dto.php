<?php

namespace STOLMC_Service_Tracker\includes\DTO;

class STOLMC_Service_Tracker_Case_Update_Dto {
	public int $case_id;

	/**
	 * @var array<string, mixed>
	 */
	private array $update_data = [];

	/**
	 * @param int                  $case_id
	 * @param array<string, mixed> $data
	 */
	public function __construct( int $case_id, array $data ) {
		if ( $case_id <= 0 ) {
			throw new Validation_Exception( 'Invalid case ID' );
		}

		$this->case_id = $case_id;
		$this->set_optional_string( $data, 'title' );
		$this->set_optional_string( $data, 'status' );
		$this->set_optional_string( $data, 'description' );
		$this->set_optional_datetime( $data, 'start_at' );
		$this->set_optional_datetime( $data, 'due_at' );
		$this->set_optional_int( $data, 'owner_id' );
		$this->set_optional_int( $data, 'id_user' );
	}

	/**
	 * @return array<string, mixed>
	 */
	public function to_array(): array {
		return $this->update_data;
	}

	/**
	 * @param array<string, mixed> $data
	 */
	private function set_optional_string( array $data, string $field ): void {
		if ( ! array_key_exists( $field, $data ) ) {
			return;
		}

		$this->update_data[ $field ] = trim( (string) $data[ $field ] );
	}

	/**
	 * @param array<string, mixed> $data
	 */
	private function set_optional_datetime( array $data, string $field ): void {
		if ( ! array_key_exists( $field, $data ) ) {
			return;
		}

		$value = $data[ $field ];
		if ( null === $value || '' === trim( (string) $value ) ) {
			$this->update_data[ $field ] = null;

			return;
		}

		$date = trim( (string) $value );
		if ( false === strtotime( $date ) ) {
			throw new Validation_Exception( sprintf( 'Invalid %s format', $field ) );
		}

		$this->update_data[ $field ] = $date;
	}

	/**
	 * @param array<string, mixed> $data
	 */
	private function set_optional_int( array $data, string $field ): void {
		if ( ! array_key_exists( $field, $data ) ) {
			return;
		}

		$value = $data[ $field ];
		if ( null === $value || '' === trim( (string) $value ) ) {
			$this->update_data[ $field ] = null;

			return;
		}

		$int_value = (int) $value;
		if ( $int_value <= 0 ) {
			throw new Validation_Exception( sprintf( 'Invalid %s value', $field ) );
		}

		$this->update_data[ $field ] = $int_value;
	}
}
