<?php

namespace STOLMC_Service_Tracker\includes\Repositories;

use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Progress_Dto;

/**
 * Case-scoped progress Repository.
 *
 * This enforces the "progress belongs to case" relationship by binding a
 * single case ID at construction time.
 */
class STOLMC_Service_Tracker_Case_Progress_Repository {

	/**
	 * Parent case ID for all operations.
	 *
	 * @var int
	 */
	private $case_id;

	/**
	 * Shared progress Repository.
	 *
	 * @var STOLMC_Service_Tracker_Progress_Repository
	 */
	private $progress_repository;

	/**
	 * Constructor.
	 *
	 * @param int                               $case_id      Case ID.
	 * @param STOLMC_Service_Tracker_Progress_Repository $progress_repository Progress Repository dependency.
	 */
	public function __construct( int $case_id, STOLMC_Service_Tracker_Progress_Repository $progress_repository ) {
		$this->case_id      = $case_id;
		$this->progress_repository = $progress_repository;
	}

	/**
	 * Read all progress entries for the bound case.
	 *
	 * @return array<STOLMC_Service_Tracker_Progress_Dto>
	 */
	public function find_all(): array {
		return $this->progress_repository->find_by_case_id( $this->case_id );
	}

	/**
	 * Create a progress entry for the bound case.
	 *
	 * @param array<string, mixed> $data Progress data.
	 *
	 * @return string|false
	 */
	public function create( array $data ): string|false {
		$data['id_case'] = $this->case_id;

		return $this->progress_repository->create( $data );
	}

	/**
	 * Update a progress entry that belongs to the bound case.
	 *
	 * @param int                  $id   Progress ID.
	 * @param array<string, mixed> $data Progress data.
	 *
	 * @return int|false
	 */
	public function update_by_id( int $id, array $data ): int|false {
		return $this->progress_repository->update_by_id_for_case( $id, $this->case_id, $data );
	}

	/**
	 * Delete a progress entry that belongs to the bound case.
	 *
	 * @param int $id Progress ID.
	 *
	 * @return int|false
	 */
	public function delete_by_id( int $id ): int|false {
		return $this->progress_repository->delete_by_id_for_case( $id, $this->case_id );
	}

	/**
	 * Delete all progress entries for the bound case.
	 *
	 * @return int|false
	 */
	public function delete_all(): int|false {
		return $this->progress_repository->delete_by_case_id( $this->case_id );
	}

	/**
	 * Backward-compatible alias for find_all().
	 *
	 * @return array<STOLMC_Service_Tracker_Progress_Dto>
	 */
	public function read_all(): array {
		return $this->find_all();
	}
}
