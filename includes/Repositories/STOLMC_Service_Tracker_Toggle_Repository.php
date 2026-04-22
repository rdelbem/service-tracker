<?php

namespace STOLMC_Service_Tracker\includes\Repositories;

use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Case_Dto;
use STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Sql;

/**
 * Toggle Repository for case status transition persistence.
 *
 * This class owns case status transition business logic and persistence.
 */
class STOLMC_Service_Tracker_Toggle_Repository {

	/**
	 * Database table name constant.
	 */
	private const DB = 'servicetracker_cases';

	/**
	 * SQL handler for cases table operations.
	 *
	 * @var STOLMC_Service_Tracker_Sql|null
	 */
	private $cases_sql = null;

	/**
	 * Cases Repository for case operations.
	 *
	 * @var STOLMC_Service_Tracker_Cases_Repository
	 */
	private $cases_repository;

	/**
	 * Constructor for the Toggle Repository class.
	 */
	public function __construct() {
		global $wpdb;

		$this->cases_sql = new STOLMC_Service_Tracker_Sql( $wpdb->prefix . self::DB );
		$this->cases_repository = new STOLMC_Service_Tracker_Cases_Repository();
	}

	/**
	 * Get a case by its ID.
	 *
	 * @param int $id The ID of the case.
	 *
	 * @return STOLMC_Service_Tracker_Case_Dto|null The case DTO or null if not found.
	 */
	public function find_by_id( int $id ): ?STOLMC_Service_Tracker_Case_Dto {
		return $this->cases_repository->find_by_id( $id );
	}

	/**
	 * Toggle case status between open and closed.
	 *
	 * @param int $id_case The ID of the case to toggle.
	 *
	 * @return int|false|null Update result:
	 *                       - int: Number of rows affected (1 for success)
	 *                       - false: Update failed
	 *                       - null: Invalid status (neither 'open' nor 'close')
	 */
	public function toggle_status( int $id_case ): int|false|null {
		$case = $this->find_by_id( $id_case );
		if ( null === $case ) {
			return false;
		}

		$current_status = $case->status;
		$new_status = $this->get_opposite_status( $current_status );

		if ( null === $new_status ) {
			return null;
		}

		return $this->cases_sql->update(
			[ 'status' => $new_status ],
			[ 'id' => $id_case ]
		);
	}

	/**
	 * Close a case (set status to 'close').
	 *
	 * @param int $id_case The ID of the case to close.
	 *
	 * @return int|false Update result:
	 *                   - int: Number of rows affected (1 for success)
	 *                   - false: Update failed
	 */
	public function close_case( int $id_case ): int|false {
		$case = $this->find_by_id( $id_case );
		if ( null === $case || 'close' === $case->status ) {
			return false;
		}

		return $this->cases_sql->update(
			[ 'status' => 'close' ],
			[ 'id' => $id_case ]
		);
	}

	/**
	 * Open a case (set status to 'open').
	 *
	 * @param int $id_case The ID of the case to open.
	 *
	 * @return int|false Update result:
	 *                   - int: Number of rows affected (1 for success)
	 *                   - false: Update failed
	 */
	public function open_case( int $id_case ): int|false {
		$case = $this->find_by_id( $id_case );
		if ( null === $case || 'open' === $case->status ) {
			return false;
		}

		return $this->cases_sql->update(
			[ 'status' => 'open' ],
			[ 'id' => $id_case ]
		);
	}

	/**
	 * Get the opposite status for toggling.
	 *
	 * @param string $current_status Current case status.
	 *
	 * @return string|null Opposite status or null if invalid.
	 */
	private function get_opposite_status( string $current_status ): ?string {
		if ( 'open' === $current_status ) {
			return 'close';
		}

		if ( 'close' === $current_status ) {
			return 'open';
		}

		return null;
	}

	/**
	 * Check if a case can be toggled (has valid status).
	 *
	 * @param int $id_case The ID of the case to check.
	 *
	 * @return bool True if case exists and has valid status ('open' or 'close').
	 */
	public function can_toggle( int $id_case ): bool {
		$case = $this->find_by_id( $id_case );
		if ( null === $case ) {
			return false;
		}

		return in_array( $case->status, [ 'open', 'close' ], true );
	}

	/**
	 * Get case status.
	 *
	 * @param int $id_case The ID of the case.
	 *
	 * @return string|null Case status or null if not found.
	 */
	public function get_status( int $id_case ): ?string {
		$case = $this->find_by_id( $id_case );
		if ( null === $case ) {
			return null;
		}

		return $case->status;
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
	 * Backward-compatible alias for toggle_status().
	 *
	 * @param int $id_case Case ID.
	 *
	 * @return int|false|null
	 */
	public function toggle( int $id_case ): int|false|null {
		return $this->toggle_status( $id_case );
	}
}
