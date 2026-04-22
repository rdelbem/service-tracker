<?php

namespace STOLMC_Service_Tracker\includes\DTO;

/**
 * Calendar Case DTO.
 */
class STOLMC_Service_Tracker_Calendar_Case_Dto {

	/**
	 * Case ID.
	 *
	 * @var int
	 */
	public $id;

	/**
	 * User ID.
	 *
	 * @var int
	 */
	public $id_user;

	/**
	 * Case title.
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Case status.
	 *
	 * @var string
	 */
	public $status;

	/**
	 * Case description.
	 *
	 * @var string
	 */
	public $description;

	/**
	 * Start datetime.
	 *
	 * @var string|null
	 */
	public $start_at;

	/**
	 * Due datetime.
	 *
	 * @var string|null
	 */
	public $due_at;

	/**
	 * Client display name.
	 *
	 * @var string
	 */
	public $client_name;

	/**
	 * Constructor.
	 *
	 * @param int         $id Case ID.
	 * @param int         $id_user User ID.
	 * @param string      $title Title.
	 * @param string      $status Status.
	 * @param string      $description Description.
	 * @param string|null $start_at Start datetime.
	 * @param string|null $due_at Due datetime.
	 * @param string      $client_name Client name.
	 */
	public function __construct( int $id, int $id_user, string $title, string $status, string $description, ?string $start_at, ?string $due_at, string $client_name ) {
		$this->id          = $id;
		$this->id_user     = $id_user;
		$this->title       = $title;
		$this->status      = $status;
		$this->description = $description;
		$this->start_at    = $start_at;
		$this->due_at      = $due_at;
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
			'id_user'     => $this->id_user,
			'title'       => $this->title,
			'status'      => $this->status,
			'description' => $this->description,
			'start_at'    => $this->start_at,
			'due_at'      => $this->due_at,
			'client_name' => $this->client_name,
		];
	}
}
