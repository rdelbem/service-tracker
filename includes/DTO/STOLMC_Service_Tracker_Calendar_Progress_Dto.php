<?php

namespace STOLMC_Service_Tracker\includes\DTO;

/**
 * Calendar Progress DTO.
 */
class STOLMC_Service_Tracker_Calendar_Progress_Dto {

	/**
	 * Progress ID.
	 *
	 * @var int
	 */
	public $id;

	/**
	 * Case ID.
	 *
	 * @var int
	 */
	public $id_case;

	/**
	 * User ID.
	 *
	 * @var int
	 */
	public $id_user;

	/**
	 * Progress text.
	 *
	 * @var string
	 */
	public $text;

	/**
	 * Created datetime.
	 *
	 * @var string
	 */
	public $created_at;

	/**
	 * Related case title.
	 *
	 * @var string
	 */
	public $case_title;

	/**
	 * Client display name.
	 *
	 * @var string
	 */
	public $client_name;

	/**
	 * Constructor.
	 *
	 * @param int    $id Progress ID.
	 * @param int    $id_case Case ID.
	 * @param int    $id_user User ID.
	 * @param string $text Progress text.
	 * @param string $created_at Created datetime.
	 * @param string $case_title Case title.
	 * @param string $client_name Client name.
	 */
	public function __construct( int $id, int $id_case, int $id_user, string $text, string $created_at, string $case_title, string $client_name ) {
		$this->id          = $id;
		$this->id_case     = $id_case;
		$this->id_user     = $id_user;
		$this->text        = $text;
		$this->created_at  = $created_at;
		$this->case_title  = $case_title;
		$this->client_name = $client_name;
	}

	/**
	 * Convert DTO to array payload.
	 *
	 * @return array<string, mixed>
	 */
	public function to_array(): array {
		return [
			'id'          => $this->id,
			'id_case'     => $this->id_case,
			'id_user'     => $this->id_user,
			'text'        => $this->text,
			'created_at'  => $this->created_at,
			'case_title'  => $this->case_title,
			'client_name' => $this->client_name,
		];
	}
}
