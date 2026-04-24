<?php

namespace STOLMC_Service_Tracker\includes\DTO;

class STOLMC_Service_Tracker_Progress_Create_Dto {
	public int $case_id;
	public string $text;
	public int $user_id;
	public mixed $attachments;

	/**
	 * @param array<string, mixed> $data
	 */
	public function __construct( array $data ) {
		$this->case_id = isset( $data['id_case'] ) ? (int) $data['id_case'] : 0;
		$this->text    = isset( $data['text'] ) ? (string) $data['text'] : '';
		$this->user_id = isset( $data['id_user'] ) ? (int) $data['id_user'] : 0;

		if ( $this->case_id <= 0 || '' === trim( $this->text ) ) {
			throw new Validation_Exception( 'id_case and text are required fields' );
		}

		$this->attachments = $data['attachments'] ?? null;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function to_array(): array {
		return [
			'id_case'     => $this->case_id,
			'id_user'     => $this->user_id,
			'text'        => $this->text,
			'attachments' => $this->attachments,
		];
	}
}
