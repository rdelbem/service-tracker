<?php

namespace STOLMC_Service_Tracker\includes\Repositories;

use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Progress_Dto;
use STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Sql;

/**
 * Progress Repository class.
 *
 * Progress records are modeled as dependent data of cases.
 */
class STOLMC_Service_Tracker_Progress_Repository {

	private const PROGRESS_DB = 'servicetracker_progress';

	/**
	 * SQL handler for progress.
	 *
	 * @var STOLMC_Service_Tracker_Sql
	 */
	private $progress_sql;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;

		$this->progress_sql = new STOLMC_Service_Tracker_Sql( $wpdb->prefix . self::PROGRESS_DB );
	}

	/**
	 * Delete all progress rows for a case.
	 *
	 * @param int $id_case Case ID.
	 *
	 * @return int|false
	 */
	public function delete_by_case_id( int $id_case ): int|false {
		return $this->progress_sql->delete( [ 'id_case' => $id_case ] );
	}

	/**
	 * Get progress rows by case ID.
	 *
	 * @param int $id_case Case ID.
	 *
	 * @return array<STOLMC_Service_Tracker_Progress_Dto>|STOLMC_Service_Tracker_Progress_Dto|null
	 */
	public function find_by_case_id( int $id_case ): array|STOLMC_Service_Tracker_Progress_Dto|null {
		$rows = $this->progress_sql->get_by( [ 'id_case' => $id_case ] );

		return $this->map_many_or_one_progress_rows( $rows );
	}

	/**
	 * Get one progress row by ID.
	 *
	 * @param int $id Progress ID.
	 *
	 * @return STOLMC_Service_Tracker_Progress_Dto|null
	 */
	public function find_by_id( int $id ): ?STOLMC_Service_Tracker_Progress_Dto {
		$rows = $this->progress_sql->get_by( [ 'id' => $id ] );
		if ( ! is_array( $rows ) || ! isset( $rows[0] ) ) {
			return null;
		}

		return $this->map_progress_row( $rows[0] );
	}

	/**
	 * Insert a progress row.
	 *
	 * @param array<string, mixed> $data Progress data.
	 *
	 * @return string|false
	 */
	public function create( array $data ): string|false {
		return $this->progress_sql->insert( $data );
	}

	/**
	 * Update a progress row constrained by case relationship.
	 *
	 * @param int                  $id       Progress ID.
	 * @param int                  $id_case  Case ID.
	 * @param array<string, mixed> $data     Progress data.
	 *
	 * @return int|false
	 */
	public function update_by_id_for_case( int $id, int $id_case, array $data ): int|false {
		return $this->progress_sql->update(
			$data,
			[
				'id'      => $id,
				'id_case' => $id_case,
			]
		);
	}

	/**
	 * Delete a progress row constrained by case relationship.
	 *
	 * @param int $id      Progress ID.
	 * @param int $id_case Case ID.
	 *
	 * @return int|false
	 */
	public function delete_by_id_for_case( int $id, int $id_case ): int|false {
		return $this->progress_sql->delete(
			[
				'id'      => $id,
				'id_case' => $id_case,
			]
		);
	}

	/**
	 * Backward-compatible alias for find_by_case_id().
	 *
	 * @param int $id_case Case ID.
	 *
	 * @return array<STOLMC_Service_Tracker_Progress_Dto>|STOLMC_Service_Tracker_Progress_Dto|null
	 */
	public function get_by_case_id( int $id_case ): array|STOLMC_Service_Tracker_Progress_Dto|null {
		return $this->find_by_case_id( $id_case );
	}

	/**
	 * Backward-compatible alias for find_by_id().
	 *
	 * @param int $id Progress ID.
	 *
	 * @return STOLMC_Service_Tracker_Progress_Dto|null
	 */
	public function get_by_id( int $id ): ?STOLMC_Service_Tracker_Progress_Dto {
		return $this->find_by_id( $id );
	}

	/**
	 * Backward-compatible alias for create().
	 *
	 * @param array<string, mixed> $data Progress data.
	 *
	 * @return string|false
	 */
	public function insert( array $data ): string|false {
		return $this->create( $data );
	}

	/**
	 * Map a raw progress row into a progress DTO.
	 *
	 * @param object|array<string, mixed>|null $row Raw database row.
	 *
	 * @return STOLMC_Service_Tracker_Progress_Dto|null
	 */
	private function map_progress_row( object|array|null $row ): ?STOLMC_Service_Tracker_Progress_Dto {
		if ( null === $row ) {
			return null;
		}

		return STOLMC_Service_Tracker_Progress_Dto::from_row( $row );
	}

	/**
	 * Map a list of raw progress rows into progress DTOs.
	 *
	 * @param array<int, object|array<string, mixed>> $rows Raw rows.
	 *
	 * @return array<STOLMC_Service_Tracker_Progress_Dto>
	 */
	private function map_progress_rows( array $rows ): array {
		$mapped = [];

		foreach ( $rows as $row ) {
			$dto = $this->map_progress_row( $row );
			if ( $dto instanceof STOLMC_Service_Tracker_Progress_Dto ) {
				$mapped[] = $dto;
			}
		}

		return $mapped;
	}

	/**
	 * Map repository raw response preserving array/single/null shape.
	 *
	 * @param array<int, object|array<string, mixed>>|object|array<string, mixed>|null $rows Raw rows.
	 *
	 * @return array<STOLMC_Service_Tracker_Progress_Dto>|STOLMC_Service_Tracker_Progress_Dto|null
	 */
	private function map_many_or_one_progress_rows( array|object|null $rows ): array|STOLMC_Service_Tracker_Progress_Dto|null {
		if ( null === $rows ) {
			return null;
		}

		if ( is_array( $rows ) ) {
			if ( [] === $rows ) {
				return [];
			}

			if ( isset( $rows[0] ) ) {
				return $this->map_progress_rows( $rows );
			}

			return $this->map_progress_row( $rows );
		}

		return $this->map_progress_row( $rows );
	}
}
