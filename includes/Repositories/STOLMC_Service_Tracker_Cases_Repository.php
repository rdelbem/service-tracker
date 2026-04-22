<?php

namespace STOLMC_Service_Tracker\includes\Repositories;

use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Case_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Progress_Dto;
use STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Sql;

/**
 * Cases Repository class, use for data resolving and type safety.
 * 
 * @template T of STOLMC_Service_Tracker_Case_Dto
 */
class STOLMC_Service_Tracker_Cases_Repository {

	private const CASES_DB = 'servicetracker_cases';

	/**
	 * SQL handler for cases.
	 *
	 * @var STOLMC_Service_Tracker_Sql
	 */
	private STOLMC_Service_Tracker_Sql $cases_sql;

	/**
	 * Progress Repository bound to cases as dependent relationship.
	 *
	 * @var STOLMC_Service_Tracker_Progress_Repository
	 */
	private $progress_repository;

	/**
	 * Constructor for the Cases Repository class.
	 */
	public function __construct() {
		global $wpdb;

		$this->cases_sql           = new STOLMC_Service_Tracker_Sql( $wpdb->prefix . self::CASES_DB );
		$this->progress_repository = new STOLMC_Service_Tracker_Progress_Repository();
	}

	/**
	 * Return a case-scoped progress relation object.
	 *
	 * @param int $id_case Case ID.
	 *
	 * @return STOLMC_Service_Tracker_Case_Progress_Repository
	 */
	public function progress( int $id_case ): STOLMC_Service_Tracker_Case_Progress_Repository {
		return new STOLMC_Service_Tracker_Case_Progress_Repository( $id_case, $this->progress_repository );
	}

	/**
	 * Resolve case-scoped progress relation from a progress ID.
	 *
	 * @param int $progress_id Progress ID.
	 *
	 * @return STOLMC_Service_Tracker_Case_Progress_Repository|null
	 */
	public function progress_from_progress_id( int $progress_id ): ?STOLMC_Service_Tracker_Case_Progress_Repository {
		$progress = $this->progress_repository->find_by_id( $progress_id );
		if ( $progress instanceof STOLMC_Service_Tracker_Progress_Dto ) {
			return $this->progress( $progress->id_case );
		}

		return null;
	}

	/**
	 * Get all cases from the database for index/search building.
	 *
	 * @return array<STOLMC_Service_Tracker_Case_Dto>
	 */
	public function find_all(): array {
		$rows = $this->cases_sql->get_all_with_columns(
			[ 'id', 'id_user', 'title', 'status' ],
			'id ASC'
		);

		return $this->map_many_or_one_case_rows( $rows );
	}

	/**
	 * Get a case by its ID from the database.
	 *
	 * @param int $id The ID of the case.
	 *
	 * @return STOLMC_Service_Tracker_Case_Dto|null The case DTO or null if not found.
	 */
	public function find_by_id( int $id ): ?STOLMC_Service_Tracker_Case_Dto {
		$cases = $this->cases_sql->get_by( [ 'id' => $id ] );
		if ( ! is_array( $cases ) || ! isset( $cases[0] ) ) {
			return null;
		}

		return $this->map_case_row( $cases[0] );
	}

	/**
	 * Get multiple cases by IDs.
	 *
	 * @param array<int, int> $ids Case IDs.
	 *
	 * @return array<STOLMC_Service_Tracker_Case_Dto>
	 */
	public function find_by_ids( array $ids ): array {
		if ( empty( $ids ) ) {
			return [];
		}

		$cases = $this->cases_sql->get_by( [ 'id' => $ids ], 'IN' );
		if ( ! is_array( $cases ) ) {
			return [];
		}

		return $this->map_case_rows( $cases );
	}

	/**
	 * Count all cases for a user.
	 *
	 * @param int $id_user User ID.
	 *
	 * @return int
	 */
	public function count_by_user( int $id_user ): int {
		return $this->cases_sql->count_by( [ 'id_user' => $id_user ] );
	}

	/**
	 * Get paginated cases for a user.
	 *
	 * @param int $id_user User ID.
	 * @param int $per_page Items per page.
	 * @param int $offset Offset.
	 *
	 * @return array<STOLMC_Service_Tracker_Case_Dto>
	 */
	public function find_paginated_by_user( int $id_user, int $per_page, int $offset ): array {
		$rows = $this->cases_sql->get_by_paginated(
			[ 'id_user' => $id_user ],
			$per_page,
			$offset,
			'created_at DESC'
		);

		return $this->map_many_or_one_case_rows( $rows );
	}

	/**
	 * Get cases by conditions.
	 *
	 * @param array<string, mixed> $query_args Query arguments.
	 *
	 * @return array<STOLMC_Service_Tracker_Case_Dto>
	 */
	public function find_by( array $query_args ): array {
		$rows = $this->cases_sql->get_by( $query_args );

		return $this->map_many_or_one_case_rows( $rows );
	}

	/**
	 * Insert a new case row.
	 *
	 * @param array<string, mixed> $data Case data.
	 *
	 * @return string|false
	 */
	public function create( array $data ): string|false {
		return $this->cases_sql->insert( $data );
	}

	/**
	 * Update case row by ID.
	 *
	 * @param int                  $id Case ID.
	 * @param array<string, mixed> $data Data to update.
	 *
	 * @return int|false
	 */
	public function update_by_id( int $id, array $data ): int|false {
		return $this->cases_sql->update( $data, [ 'id' => $id ] );
	}

	/**
	 * Delete case row by ID.
	 *
	 * @param int $id Case ID.
	 *
	 * @return int|false
	 */
	public function delete_by_id( int $id ): int|false {
		return $this->cases_sql->delete( [ 'id' => $id ] );
	}

	/**
	 * Delete all progress rows for a case.
	 *
	 * @param int $id_case Case ID.
	 *
	 * @return int|false
	 */
	public function delete_progress_by_case_id( int $id_case ): int|false {
		return $this->progress( $id_case )->delete_all();
	}

	/**
	 * Get last inserted row ID.
	 *
	 * @return int
	 */
	public function get_last_insert_id(): int {
		global $wpdb;

		return (int) ( $wpdb->insert_id ?? 0 );
	}

	/**
	 * Backward-compatible alias for find_all().
	 *
	 * @return array<STOLMC_Service_Tracker_Case_Dto>
	 */
	public function get_all(): array {
		return $this->find_all();
	}

	/**
	 * Backward-compatible alias for find_by_id().
	 *
	 * @param int $id Case ID.
	 *
	 * @return STOLMC_Service_Tracker_Case_Dto|null
	 */
	public function get_by_id( int $id ): ?STOLMC_Service_Tracker_Case_Dto {
		return $this->find_by_id( $id );
	}

	/**
	 * Backward-compatible alias for find_by_ids().
	 *
	 * @param array<int, int> $ids Case IDs.
	 *
	 * @return array<STOLMC_Service_Tracker_Case_Dto>
	 */
	public function get_by_ids( array $ids ): array {
		return $this->find_by_ids( $ids );
	}

	/**
	 * Backward-compatible alias for find_paginated_by_user().
	 *
	 * @param int $id_user User ID.
	 * @param int $per_page Page size.
	 * @param int $offset Offset.
	 *
	 * @return array<STOLMC_Service_Tracker_Case_Dto>
	 */
	public function get_by_user_paginated( int $id_user, int $per_page, int $offset ): array {
		return $this->find_paginated_by_user( $id_user, $per_page, $offset );
	}

	/**
	 * Backward-compatible alias for find_by().
	 *
	 * @param array<string, mixed> $query_args Query arguments.
	 *
	 * @return array<STOLMC_Service_Tracker_Case_Dto>
	 */
	public function get_by( array $query_args ): array {
		return $this->find_by( $query_args );
	}

	/**
	 * Backward-compatible alias for create().
	 *
	 * @param array<string, mixed> $data Case data.
	 *
	 * @return string|false
	 */
	public function insert( array $data ): string|false {
		return $this->create( $data );
	}

	/**
	 * Map a raw case row into a case DTO.
	 *
	 * @param object|array<int|string, mixed>|null $row Raw database row.
	 *
	 * @return STOLMC_Service_Tracker_Case_Dto|null
	 */
	private function map_case_row( object|array|null $row ): ?STOLMC_Service_Tracker_Case_Dto {
		if ( null === $row ) {
			return null;
		}

		return STOLMC_Service_Tracker_Case_Dto::from_row( $row );
	}

	/**
	 * Map a list of raw case rows into case DTOs.
	 *
	 * @param array<int|string, mixed> $rows Raw rows.
	 *
	 * @return array<STOLMC_Service_Tracker_Case_Dto>
	 */
	private function map_case_rows( array $rows ): array {
		$mapped = [];

		foreach ( $rows as $row ) {
			$dto = $this->map_case_row( $row );
			if ( $dto instanceof STOLMC_Service_Tracker_Case_Dto ) {
				$mapped[] = $dto;
			}
		}

		return $mapped;
	}

	/**
	 * Map repository raw response to array of DTOs.
	 *
	 * @param array<int|string, mixed>|object|array<string, mixed>|null $rows Raw rows.
	 *
	 * @return array<STOLMC_Service_Tracker_Case_Dto>
	 */
	private function map_many_or_one_case_rows( array|object|null $rows ): array {
		if ( null === $rows ) {
			return [];
		}

		if ( is_array( $rows ) ) {
			if ( [] === $rows ) {
				return [];
			}

			if ( isset( $rows[0] ) ) {
				return $this->map_case_rows( $rows );
			}

			$dto = $this->map_case_row( $rows );
			return $dto instanceof STOLMC_Service_Tracker_Case_Dto ? [ $dto ] : [];
		}

		$dto = $this->map_case_row( $rows );
		return $dto instanceof STOLMC_Service_Tracker_Case_Dto ? [ $dto ] : [];
	}
}
