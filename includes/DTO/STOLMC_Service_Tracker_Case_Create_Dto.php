<?php

namespace STOLMC_Service_Tracker\includes\DTO;

class STOLMC_Service_Tracker_Case_Create_Dto {
	public int $id_user;
	public string $title;
	public string $status;
	public string $description;
	public ?string $start_at;
	public ?string $due_at;
	public ?int $owner_id;

	/**
	 * @param array<string, mixed> $data
	 */
	public function __construct( array $data ) {
		$id_user = isset( $data['id_user'] ) ? (int) $data['id_user'] : 0;
		$title   = isset( $data['title'] ) ? trim( (string) $data['title'] ) : '';

		if ( $id_user <= 0 || '' === $title ) {
			throw new Validation_Exception( 'id_user and title are required fields' );
		}

		$this->id_user     = $id_user;
		$this->title       = $title;
		$this->status      = isset( $data['status'] ) && '' !== trim( (string) $data['status'] ) ? trim( (string) $data['status'] ) : 'open';
		$this->description = isset( $data['description'] ) ? (string) $data['description'] : '';
		$this->start_at    = $this->normalize_datetime_or_null( $data['start_at'] ?? null, 'start_at' );
		$this->due_at      = $this->normalize_datetime_or_null( $data['due_at'] ?? null, 'due_at' );
		$this->owner_id    = $this->normalize_optional_int( $data['owner_id'] ?? null, 'owner_id' );

		if ( null !== $this->start_at && null !== $this->due_at && $this->start_at > $this->due_at ) {
			throw new Validation_Exception( 'start_at must be before or equal to due_at' );
		}
	}

	/**
	 * @return array<string, mixed>
	 */
	public function to_array(): array {
		return [
			'id_user'     => $this->id_user,
			'title'       => $this->title,
			'status'      => $this->status,
			'description' => $this->description,
			'start_at'    => $this->start_at,
			'due_at'      => $this->due_at,
			'owner_id'    => $this->owner_id,
		];
	}

	private function normalize_datetime_or_null( mixed $value, string $field_name ): ?string {
		if ( null === $value || '' === trim( (string) $value ) ) {
			return null;
		}

		$date = trim( (string) $value );
		if ( false === strtotime( $date ) ) {
			throw new Validation_Exception( sprintf( 'Invalid %s format', $field_name ) );
		}

		return $date;
	}

	private function normalize_optional_int( mixed $value, string $field_name ): ?int {
		if ( null === $value || '' === trim( (string) $value ) ) {
			return null;
		}

		$int_value = (int) $value;
		if ( $int_value <= 0 ) {
			throw new Validation_Exception( sprintf( 'Invalid %s value', $field_name ) );
		}

		return $int_value;
	}
}
