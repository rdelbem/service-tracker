<?php

namespace STOLMC_Service_Tracker\includes\DTO;

/**
 * Calendar payload DTO.
 */
class STOLMC_Service_Tracker_Calendar_Payload_Dto {

	/**
	 * Calendar case items.
	 *
	 * @var array<int, STOLMC_Service_Tracker_Calendar_Case_Dto>
	 */
	public $cases;

	/**
	 * Calendar progress items.
	 *
	 * @var array<int, STOLMC_Service_Tracker_Calendar_Progress_Dto>
	 */
	public $progress;

	/**
	 * Calendar date index map.
	 *
	 * @var array<string, array<string, array<int>>>
	 */
	public $date_index;

	/**
	 * Constructor.
	 *
	 * @param array<int, STOLMC_Service_Tracker_Calendar_Case_Dto>     $cases Cases.
	 * @param array<int, STOLMC_Service_Tracker_Calendar_Progress_Dto> $progress Progress.
	 * @param array<string, array<string, array<int>>>                 $date_index Date index.
	 */
	public function __construct( array $cases, array $progress, array $date_index ) {
		$this->cases      = $cases;
		$this->progress   = $progress;
		$this->date_index = $date_index;
	}

	/**
	 * Convert DTO to array payload.
	 *
	 * @return array{
	 *   cases: array<int, array<string, mixed>>,
	 *   progress: array<int, array<string, mixed>>,
	 *   date_index: array<string, array<string, array<int>>>
	 * }
	 */
	public function to_array(): array {
		return [
			'cases'      => array_map( static fn( STOLMC_Service_Tracker_Calendar_Case_Dto $case_item ): array => $case_item->to_array(), $this->cases ),
			'progress'   => array_map( static fn( STOLMC_Service_Tracker_Calendar_Progress_Dto $item ): array => $item->to_array(), $this->progress ),
			'date_index' => $this->date_index,
		];
	}
}
