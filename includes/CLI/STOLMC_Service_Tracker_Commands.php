<?php

namespace STOLMC_Service_Tracker\includes\CLI;

defined( 'ABSPATH' ) || exit;

use STOLMC_Service_Tracker\includes\DB\STOLMC_Calendar_Index;
use WP_CLI;
use WP_CLI_Command;

/**
 * WP-CLI commands for Service Tracker.
 *
 * Provides command-line utilities for managing plugin data.
 *
 * @since    1.1.0
 * @package  STOLMC_Service_Tracker
 *
 * @when before_wp_load
 */
class STOLMC_Service_Tracker_Commands extends WP_CLI_Command {

	/**
	 * Rebuild the calendar date index.
	 *
	 * Reads all cases from the database and populates the
	 * `stolmc_service_tracker_calendar_index` option with a mapping of
	 * date strings to case start/end events.  This index is used
	 * by the calendar UI to render day-level indicators.
	 *
	 * ## OPTIONS
	 *
	 * [--dry-run]
	 * : Show what would be indexed without saving.
	 *
	 * ## EXAMPLES
	 *
	 *     wp stolmc-service-tracker rebuild-calendar-index
	 *     wp stolmc-service-tracker rebuild-calendar-index --dry-run
	 *
	 * @since 1.1.0
	 *
	 * @param array<int, string>       $args       Positional arguments (unused).
	 * @param array<string, mixed>     $assoc_args Associative arguments.
	 * @return void
	 */
	public function rebuild_calendar_index( $args, $assoc_args ): void {
		$dry_run = \WP_CLI\Utils\get_flag_value( $assoc_args, 'dry-run', false );

		if ( $dry_run ) {
			WP_CLI::log( 'Running in dry-run mode — no changes will be saved.' );
			$index = $this->build_index_for_display();
			WP_CLI::log( wp_json_encode( $index, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
			WP_CLI::success( 'Dry-run complete.' );
			return;
		}

		STOLMC_Calendar_Index::rebuild();
		$index = STOLMC_Calendar_Index::get();
		$total_dates = count( $index );

		$total_starts = 0;
		$total_ends   = 0;
		foreach ( $index as $entry ) {
			$total_starts += count( $entry['starts'] ?? [] );
			$total_ends   += count( $entry['ends'] ?? [] );
		}

		WP_CLI::success(
			sprintf(
				'Calendar index rebuilt: %d date(s), %d start(s), %d end(s).',
				$total_dates,
				$total_starts,
				$total_ends
			)
		);
	}

	/**
	 * Show the current calendar date index.
	 *
	 * Displays the contents of the `stolmc_service_tracker_calendar_index`
	 * option.
	 *
	 * ## EXAMPLES
	 *
	 *     wp stolmc-service-tracker show-calendar-index
	 *
	 * @since 1.1.0
	 *
	 * @param array<int, string> $args Positional arguments (unused).
	 * @return void
	 */
	public function show_calendar_index( $args ): void {
		$index = STOLMC_Calendar_Index::get();

		if ( empty( $index ) ) {
			WP_CLI::warning( 'Calendar index is empty. Run `wp stolmc-service-tracker rebuild-calendar-index` to populate it.' );
			return;
		}

		WP_CLI::log( wp_json_encode( $index, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
	}

	/**
	 * Build the index as a plain array (for display purposes).
	 *
	 * Duplicates the rebuild logic without saving to the database.
	 *
	 * @since 1.1.0
	 *
	 * @return array<string, array<string, int[]>>
	 */
	private function build_index_for_display(): array {
		global $wpdb;

		$table = $wpdb->prefix . 'servicetracker_cases';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$cases = $wpdb->get_results(
			"SELECT id, start_at, due_at FROM {$wpdb->prefix}servicetracker_cases WHERE start_at IS NOT NULL OR due_at IS NOT NULL",
			ARRAY_A
		);

		if ( empty( $cases ) ) {
			return [ 'only_dates_index' => [] ];
		}

		$index = [];

		foreach ( $cases as $case ) {
			$case_id    = (int) $case['id'];
			$start_date = isset( $case['start_at'] ) ? substr( (string) $case['start_at'], 0, 10 ) : null;
			$end_date   = isset( $case['due_at'] ) ? substr( (string) $case['due_at'], 0, 10 ) : null;

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

		ksort( $index );

		return [ 'only_dates_index' => $index ];
	}
}
