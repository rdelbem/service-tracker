<?php

namespace STOLMC_Service_Tracker\includes\DB;

/**
 * Calendar date index builder and manager.
 *
 * Maintains a lightweight WordPress option (service_tracker_calendar_index)
 * that maps each date string to the case IDs that start or end on that
 * day.  This allows the frontend calendar to render day-level indicators
 * (start/end markers) without iterating every case for every day cell.
 *
 * The index is rebuilt automatically on case create, update, or delete
 * via hooks.  It can also be rebuilt manually through WP CLI.
 *
 * @since    1.1.0
 * @package  STOLMC_Service_Tracker\includes\DB
 */
class CalendarIndex {

	/**
	 * Option key for storing the calendar index.
	 *
	 * @since 1.1.0
	 * @var   string
	 */
	public const OPTION_KEY = 'service_tracker_calendar_index';

	/**
	 * Rebuild the entire calendar index from scratch.
	 *
	 * Reads all cases from the database and builds the
	 * only_dates_index mapping, then saves it to the wp_options table.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public static function rebuild(): void {
		global $wpdb;

		$table = $wpdb->prefix . 'servicetracker_cases';

		// Fetch only the columns we need for the index.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$cases = $wpdb->get_results(
			"SELECT id, start_at, due_at FROM {$wpdb->prefix}servicetracker_cases WHERE start_at IS NOT NULL OR due_at IS NOT NULL",
			ARRAY_A
		);

		if ( empty( $cases ) ) {
			update_option( self::OPTION_KEY, [ 'only_dates_index' => [] ] );
			return;
		}

		$index = [];

		foreach ( $cases as $case ) {
			$case_id  = (int) $case['id'];
			$start_at = $case['start_at'] ?? null;
			$due_at   = $case['due_at'] ?? null;

			// Normalize to date-only strings.
			$start_date = $start_at ? substr( (string) $start_at, 0, 10 ) : null;
			$end_date   = $due_at ? substr( (string) $due_at, 0, 10 ) : null;

			if ( $start_date ) {
				if ( ! isset( $index[ $start_date ] ) ) {
					$index[ $start_date ] = [
						'starts' => [],
						'ends'   => [],
					];
				}
				$index[ $start_date ]['starts'][] = $case_id;
			}

			if ( $end_date ) {
				if ( ! isset( $index[ $end_date ] ) ) {
					$index[ $end_date ] = [
						'starts' => [],
						'ends'   => [],
					];
				}
				$index[ $end_date ]['ends'][] = $case_id;
			}
		}

		// Sort index keys chronologically for easier debugging.
		ksort( $index );

		update_option(
			self::OPTION_KEY,
			[ 'only_dates_index' => $index ],
			true
		);
	}

	/**
	 * Read the current index from the database.
	 *
	 * @since 1.1.0
	 *
	 * @return array<string, array<string, array<int>>>
	 */
	public static function get(): array {
		$stored = get_option( self::OPTION_KEY, null );

		if ( ! is_array( $stored ) || ! isset( $stored['only_dates_index'] ) ) {
			return [];
		}

		return $stored['only_dates_index'];
	}

	/**
	 * Delete the calendar index option.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public static function clear(): void {
		delete_option( self::OPTION_KEY );
	}
}
