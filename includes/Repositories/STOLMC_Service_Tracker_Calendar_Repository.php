<?php

namespace STOLMC_Service_Tracker\includes\Repositories;

use STOLMC_Service_Tracker\includes\DB\STOLMC_Calendar_Index;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Calendar_Case_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Calendar_Payload_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Calendar_Progress_Dto;
use STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Sql;

/**
 * Calendar Repository class for aggregating case and progress data.
 */
class STOLMC_Service_Tracker_Calendar_Repository {

	/**
	 * SQL handler for cases table.
	 *
	 * @var STOLMC_Service_Tracker_Sql
	 */
	private STOLMC_Service_Tracker_Sql $cases_sql;

	/**
	 * SQL handler for progress table.
	 *
	 * @var STOLMC_Service_Tracker_Sql
	 */
	private STOLMC_Service_Tracker_Sql $progress_sql;

	/**
	 * Database table name constants.
	 */
	private const CASES_DB = 'servicetracker_cases';
	private const PROGRESS_DB = 'servicetracker_progress';

	/**
	 * Constructor for the Calendar Repository class.
	 */
	public function __construct() {
		global $wpdb;

		$this->cases_sql    = new STOLMC_Service_Tracker_Sql( $wpdb->prefix . self::CASES_DB );
		$this->progress_sql = new STOLMC_Service_Tracker_Sql( $wpdb->prefix . self::PROGRESS_DB );
	}

	/**
	 * Get calendar data aggregated from cases and progress.
	 *
	 * @param string      $start   Start date (YYYY-MM-DD format).
	 * @param string      $end     End date (YYYY-MM-DD format).
	 * @param int|null    $id_user Optional user ID to filter cases.
	 * @param string|null $status  Optional status to filter cases.
	 *
	 * @return STOLMC_Service_Tracker_Calendar_Payload_Dto
	 */
	public function find_calendar_data( string $start, string $end, ?int $id_user = null, ?string $status = null ): STOLMC_Service_Tracker_Calendar_Payload_Dto {
		return new STOLMC_Service_Tracker_Calendar_Payload_Dto(
			$this->find_calendar_cases( $start, $end, $id_user, $status ),
			$this->find_calendar_progress( $start, $end, $id_user ),
			STOLMC_Calendar_Index::get()
		);
	}

	/**
	 * Get cases with date ranges for calendar view.
	 *
	 * @param string      $start   Start date (YYYY-MM-DD format).
	 * @param string      $end     End date (YYYY-MM-DD format).
	 * @param int|null    $id_user Optional user ID to filter cases.
	 * @param string|null $status  Optional status to filter cases.
	 *
	 * @return array<int, STOLMC_Service_Tracker_Calendar_Case_Dto>
	 */
	public function find_calendar_cases( string $start, string $end, ?int $id_user = null, ?string $status = null ): array {
		$cases_query_args = [];
		if ( ! empty( $id_user ) ) {
			$cases_query_args['id_user'] = $id_user;
		}

		if ( ! empty( $status ) ) {
			$cases_query_args['status'] = $status;
		}

		$cases = [];
		if ( empty( $cases_query_args ) ) {
			$raw_cases = $this->cases_sql->get_all();
			if ( is_array( $raw_cases ) ) {
				$cases = $raw_cases;
			}
		} else {
			$raw_cases = $this->cases_sql->get_by( $cases_query_args );
			if ( is_array( $raw_cases ) ) {
				$cases = $raw_cases;
			}
		}

		$calendar_cases = [];
		foreach ( $cases as $case ) {
			if ( empty( $case->start_at ) && empty( $case->due_at ) ) {
				continue;
			}

			$case_start = ! empty( $case->start_at ) ? (string) $case->start_at : (string) ( $case->created_at ?? '' );
			$case_end   = ! empty( $case->due_at ) ? (string) $case->due_at : (string) ( $case->created_at ?? '' );

			if ( '' === $case_start || '' === $case_end ) {
				continue;
			}

			if ( $case_start > $end || $case_end < $start ) {
				continue;
			}

			$calendar_cases[] = new STOLMC_Service_Tracker_Calendar_Case_Dto(
				(int) ( $case->id ?? 0 ),
				(int) ( $case->id_user ?? 0 ),
				(string) ( $case->title ?? '' ),
				(string) ( $case->status ?? '' ),
				(string) ( $case->description ?? '' ),
				isset( $case->start_at ) ? (string) $case->start_at : null,
				isset( $case->due_at ) ? (string) $case->due_at : null,
				$this->resolve_user_display_name( (int) ( $case->id_user ?? 0 ) )
			);
		}

		return $calendar_cases;
	}

	/**
	 * Get progress entries for calendar view.
	 *
	 * @param string   $start   Start date (YYYY-MM-DD format).
	 * @param string   $end     End date (YYYY-MM-DD format).
	 * @param int|null $id_user Optional user ID to filter progress entries.
	 *
	 * @return array<int, STOLMC_Service_Tracker_Calendar_Progress_Dto>
	 */
	public function find_calendar_progress( string $start, string $end, ?int $id_user = null ): array {
		$raw_progress = $this->progress_sql->get_all();
		$progress     = is_array( $raw_progress ) ? $raw_progress : [];

		$calendar_progress = [];
		foreach ( $progress as $entry ) {
			$created_at = (string) ( $entry->created_at ?? '' );
			if ( '' === $created_at ) {
				continue;
			}

			if ( $created_at < $start || $created_at > $end ) {
				continue;
			}

			if ( null !== $id_user && (int) ( $entry->id_user ?? 0 ) !== $id_user ) {
				continue;
			}

			$entry_case_id      = (int) ( $entry->id_case ?? 0 );
			$calendar_progress[] = new STOLMC_Service_Tracker_Calendar_Progress_Dto(
				(int) ( $entry->id ?? 0 ),
				$entry_case_id,
				(int) ( $entry->id_user ?? 0 ),
				(string) ( $entry->text ?? '' ),
				$created_at,
				$this->resolve_case_title( $entry_case_id ),
				$this->resolve_user_display_name( (int) ( $entry->id_user ?? 0 ) )
			);
		}

		return $calendar_progress;
	}

	/**
	 * Get date index for calendar.
	 *
	 * @return array<string, array<string, array<int>>>
	 */
	public function find_date_index(): array {
		return STOLMC_Calendar_Index::get();
	}

	/**
	 * Rebuild the calendar date index.
	 *
	 * @return void
	 */
	public function rebuild_date_index(): void {
		STOLMC_Calendar_Index::rebuild();
	}

	/**
	 * Clear the calendar date index.
	 *
	 * @return void
	 */
	public function clear_date_index(): void {
		STOLMC_Calendar_Index::clear();
	}

	/**
	 * Backward-compatible alias for find_calendar_data().
	 *
	 * @param string      $start Start date.
	 * @param string      $end End date.
	 * @param int|null    $id_user User ID.
	 * @param string|null $status Status.
	 *
	 * @return array{
	 *     cases: array<int, array<string, mixed>>,
	 *     progress: array<int, array<string, mixed>>,
	 *     date_index: array<string, array<string, array<int>>>
	 * }
	 */
	public function get_calendar_data( string $start, string $end, ?int $id_user = null, ?string $status = null ): array {
		return $this->find_calendar_data( $start, $end, $id_user, $status )->to_array();
	}

	/**
	 * Backward-compatible alias for find_calendar_cases().
	 *
	 * @param string      $start Start date.
	 * @param string      $end End date.
	 * @param int|null    $id_user User ID.
	 * @param string|null $status Status.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function get_calendar_cases( string $start, string $end, ?int $id_user = null, ?string $status = null ): array {
		return array_map(
			static fn( STOLMC_Service_Tracker_Calendar_Case_Dto $item ): array => $item->to_array(),
			$this->find_calendar_cases( $start, $end, $id_user, $status )
		);
	}

	/**
	 * Backward-compatible alias for find_calendar_progress().
	 *
	 * @param string   $start Start date.
	 * @param string   $end End date.
	 * @param int|null $id_user User ID.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function get_calendar_progress( string $start, string $end, ?int $id_user = null ): array {
		return array_map(
			static fn( STOLMC_Service_Tracker_Calendar_Progress_Dto $item ): array => $item->to_array(),
			$this->find_calendar_progress( $start, $end, $id_user )
		);
	}

	/**
	 * Backward-compatible alias for find_date_index().
	 *
	 * @return array<string, array<string, array<int>>>
	 */
	public function get_date_index(): array {
		return $this->find_date_index();
	}

	/**
	 * Resolve a user display name.
	 *
	 * @param int $id_user User ID.
	 *
	 * @return string
	 */
	private function resolve_user_display_name( int $id_user ): string {
		$user = get_user_by( 'id', $id_user );
		if ( is_object( $user ) && isset( $user->display_name ) ) {
			return (string) $user->display_name;
		}

		return 'Unknown';
	}

	/**
	 * Resolve case title by case ID.
	 *
	 * @param int $id_case Case ID.
	 *
	 * @return string
	 */
	private function resolve_case_title( int $id_case ): string {
		$case_result = $this->cases_sql->get_by( [ 'id' => $id_case ] );
		if ( is_array( $case_result ) && isset( $case_result[0] ) && is_object( $case_result[0] ) && isset( $case_result[0]->title ) ) {
			return (string) $case_result[0]->title;
		}

		return 'Unknown Case';
	}
}
