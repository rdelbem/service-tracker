<?php

namespace STOLMC_Service_Tracker\includes\Repositories;

use STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Sql;

/**
 * Analytics Repository for dashboard analytics queries.
 */
class STOLMC_Service_Tracker_Analytics_Repository {

	/**
	 * Number of days to show in trends data.
	 *
	 * @var int
	 */
	private const TRENDS_DAYS_LIMIT = 5;

	/**
	 * Users SQL helper.
	 *
	 * @var STOLMC_Service_Tracker_Sql
	 */
	private $users_sql;

	/**
	 * Cases SQL helper.
	 *
	 * @var STOLMC_Service_Tracker_Sql
	 */
	private $cases_sql;

	/**
	 * Progress SQL helper.
	 *
	 * @var STOLMC_Service_Tracker_Sql
	 */
	private $progress_sql;

	/**
	 * Notifications SQL helper.
	 *
	 * @var STOLMC_Service_Tracker_Sql
	 */
	private $notifications_sql;

	/**
	 * Activity-log SQL helper.
	 *
	 * @var STOLMC_Service_Tracker_Sql
	 */
	private $activity_log_sql;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;

		$this->users_sql         = new STOLMC_Service_Tracker_Sql( $wpdb->users ); // phpcs:ignore
		$this->cases_sql         = new STOLMC_Service_Tracker_Sql( $wpdb->prefix . 'servicetracker_cases' );
		$this->progress_sql      = new STOLMC_Service_Tracker_Sql( $wpdb->prefix . 'servicetracker_progress' );
		$this->notifications_sql = new STOLMC_Service_Tracker_Sql( $wpdb->prefix . 'servicetracker_notifications' );
		$this->activity_log_sql  = new STOLMC_Service_Tracker_Sql( $wpdb->prefix . 'servicetracker_activity_log' );
	}

	/**
	 * Get summary statistics.
	 *
	 * @return array{
	 *     total_customers: int,
	 *     total_cases: int,
	 *     open_cases: int,
	 *     closed_cases: int,
	 *     total_progress_updates: int,
	 *     notifications_attempted: int,
	 *     notifications_sent: int,
	 *     notifications_failed: int,
	 *     active_admins_last_30_days: int
	 * }
	 */
	public function find_summary_stats(): array {
		$active_admin_cutoff = gmdate(
			'Y-m-d H:i:s',
			strtotime( '-30 days' )
		);

		$total_customers         = $this->users_sql->count_distinct( 'ID' );
		$total_cases             = $this->cases_sql->count_all();
		$open_cases              = $this->cases_sql->count_by( [ 'status' => 'open' ] );
		$closed_cases            = $this->cases_sql->count_by( [ 'status' => 'close' ] );
		$total_progress          = $this->progress_sql->count_all();
		$notifications_attempted = $this->notifications_sql->count_all();
		$notifications_sent      = $this->notifications_sql->count_by( [ 'status' => 'sent' ] );
		$notifications_failed    = $this->notifications_sql->count_by( [ 'status' => 'failed' ] );
		$active_admins           = $this->activity_log_sql->count_distinct(
			'actor_user_id',
			[
				'actor_user_id !=' => null,
				'created_at >='    => $active_admin_cutoff,
			]
		);

		return [
			'total_customers'            => $total_customers,
			'total_cases'                => $total_cases,
			'open_cases'                 => $open_cases,
			'closed_cases'               => $closed_cases,
			'total_progress_updates'     => $total_progress,
			'notifications_attempted'    => $notifications_attempted,
			'notifications_sent'         => $notifications_sent,
			'notifications_failed'       => $notifications_failed,
			'active_admins_last_30_days' => $active_admins,
		];
	}

	/**
	 * Get per-customer statistics.
	 *
	 * @return array{
	 *     user_id: int,
	 *     name: string,
	 *     email: string,
	 *     total_cases: int,
	 *     open_cases: int,
	 *     closed_cases: int,
	 *     progress_updates: int,
	 *     notifications_sent: int,
	 *     last_activity_at: string|null
	 * }
	 */
	public function find_customer_stats(): array {
		$customers = $this->users_sql->get_all_with_columns(
			[ 'ID', 'display_name', 'user_email' ],
			'ID ASC'
		);

		if ( ! is_array( $customers ) || empty( $customers ) ) {
			return [];
		}

		$stats = [];
		foreach ( $customers as $customer ) {
			if ( ! isset( $customer->ID ) ) {
				continue;
			}

			$user_id = (int) $customer->ID;

			$total_cases        = $this->cases_sql->count_by( [ 'id_user' => $user_id ] );
			$open_cases         = $this->cases_sql->count_by( [
				'id_user' => $user_id,
				'status'  => 'open',
				] );
			$closed_cases       = $this->cases_sql->count_by( [
				'id_user' => $user_id,
				'status'  => 'close',
				] );
			$progress_updates   = $this->progress_sql->count_by( [ 'id_user' => $user_id ] );
			$notifications_sent = $this->notifications_sql->count_by( [
				'id_user' => $user_id,
				'status'  => 'sent',
				] );

			$last_case_activity     = $this->cases_sql->max_of( 'created_at', [ 'id_user' => $user_id ] );
			$last_progress_activity = $this->progress_sql->max_of( 'created_at', [ 'id_user' => $user_id ] );
			$last_activity          = null;

			if ( null !== $last_case_activity && null !== $last_progress_activity ) {
				$last_activity = ( $last_case_activity >= $last_progress_activity )
					? $last_case_activity
					: $last_progress_activity;
			} elseif ( null !== $last_case_activity ) {
				$last_activity = $last_case_activity;
			} elseif ( null !== $last_progress_activity ) {
				$last_activity = $last_progress_activity;
			}

			if ( $total_cases > 0 || $progress_updates > 0 ) {
				$stats[] = [
					'user_id'            => $user_id,
					'name'               => (string) ( $customer->display_name ?? '' ),
					'email'              => (string) ( $customer->user_email ?? '' ),
					'total_cases'        => $total_cases,
					'open_cases'         => $open_cases,
					'closed_cases'       => $closed_cases,
					'progress_updates'   => $progress_updates,
					'notifications_sent' => $notifications_sent,
					'last_activity_at'   => $last_activity,
				];
			}
		}

		return $stats;
	}

	/**
	 * Get per-admin/staff statistics.
	 *
	 * @return array{
	 *     user_id: int,
	 *     display_name: string,
	 *     email: string,
	 *     cases_created: int,
	 *     cases_updated: int,
	 *     cases_deleted: int,
	 *     progress_created: int,
	 *     progress_updated: int,
	 *     progress_deleted: int,
	 *     notifications_triggered: int,
	 *     last_activity_at: string|null
	 * }
	 */
	public function find_admin_stats(): array {
		$admin_ids = $this->activity_log_sql->get_distinct_values(
			'actor_user_id',
			[ 'actor_user_id !=' => null ],
			'actor_user_id ASC'
		);

		if ( empty( $admin_ids ) ) {
			return [];
		}

		$stats = [];

		foreach ( $admin_ids as $admin_id ) {
			$actor_id = (int) $admin_id;
			if ( $actor_id <= 0 ) {
				continue;
			}

			$user_result = $this->users_sql->get_by( [ 'ID' => $actor_id ] );
			if ( ! is_array( $user_result ) || ! isset( $user_result[0] ) ) {
				continue;
			}

			$user = $user_result[0];

			$cases_created = $this->activity_log_sql->count_by(
				[
					'actor_user_id' => $actor_id,
					'entity_type'   => 'case',
					'action_type'   => 'created',
				]
			);

			$cases_updated = $this->activity_log_sql->count_by(
				[
					'actor_user_id' => $actor_id,
					'entity_type'   => 'case',
					'action_type'   => 'updated',
				]
			);

			$cases_deleted = $this->activity_log_sql->count_by(
				[
					'actor_user_id' => $actor_id,
					'entity_type'   => 'case',
					'action_type'   => 'deleted',
				]
			);

			$progress_created = $this->activity_log_sql->count_by(
				[
					'actor_user_id' => $actor_id,
					'entity_type'   => 'progress',
					'action_type'   => 'created',
				]
			);

			$progress_updated = $this->activity_log_sql->count_by(
				[
					'actor_user_id' => $actor_id,
					'entity_type'   => 'progress',
					'action_type'   => 'updated',
				]
			);

			$progress_deleted = $this->activity_log_sql->count_by(
				[
					'actor_user_id' => $actor_id,
					'entity_type'   => 'progress',
					'action_type'   => 'deleted',
				]
			);

			$notifications_triggered = $this->notifications_sql->count_by( [ 'actor_user_id' => $actor_id ] );
			$last_activity           = $this->activity_log_sql->max_of( 'created_at', [ 'actor_user_id' => $actor_id ] );

			$stats[] = [
				'user_id'                 => $actor_id,
				'display_name'            => (string) ( $user->display_name ?? '' ),
				'email'                   => (string) ( $user->user_email ?? '' ),
				'cases_created'           => $cases_created,
				'cases_updated'           => $cases_updated,
				'cases_deleted'           => $cases_deleted,
				'progress_created'        => $progress_created,
				'progress_updated'        => $progress_updated,
				'progress_deleted'        => $progress_deleted,
				'notifications_triggered' => $notifications_triggered,
				'last_activity_at'        => $last_activity,
			];
		}

		return $stats;
	}

	/**
	 * Get trend data by period.
	 *
	 * @param string|null $start Start date.
	 * @param string|null $end   End date.
	 *
	 * @return array{
	 *     cases_created_by_period: array<string, int>,
	 *     progress_created_by_period: array<string, int>,
	 *     notifications_by_period: array<string, int>,
	 *     admin_actions_by_period: array<string, int>
	 * }
	 */
	public function find_trends( ?string $start, ?string $end ): array {
		$conditions = [];

		if ( $start ) {
			$conditions['created_at >='] = $start;
		}

		if ( $end ) {
			$conditions['created_at <='] = $end;
		}

		$cases_by_period         = $this->cases_sql->get_daily_counts( 'created_at', $conditions, self::TRENDS_DAYS_LIMIT );
		$progress_by_period      = $this->progress_sql->get_daily_counts( 'created_at', $conditions, self::TRENDS_DAYS_LIMIT );
		$notifications_by_period = $this->notifications_sql->get_daily_counts( 'created_at', $conditions, self::TRENDS_DAYS_LIMIT );
		$admin_actions_by_period = $this->activity_log_sql->get_daily_counts(
			'created_at',
			array_merge(
				$conditions,
				[ 'actor_user_id !=' => null ]
			),
			self::TRENDS_DAYS_LIMIT
		);

		return [
			'cases_created_by_period'    => $cases_by_period,
			'progress_created_by_period' => $progress_by_period,
			'notifications_by_period'    => $notifications_by_period,
			'admin_actions_by_period'    => $admin_actions_by_period,
		];
	}

	/**
	 * Backward-compatible alias for find_summary_stats().
	 *
	 * @return array<string, mixed>
	 */
	public function get_summary_stats(): array {
		return $this->find_summary_stats();
	}

	/**
	 * Backward-compatible alias for find_customer_stats().
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function get_customer_stats(): array {
		return $this->find_customer_stats();
	}

	/**
	 * Backward-compatible alias for find_admin_stats().
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function get_admin_stats(): array {
		return $this->find_admin_stats();
	}

	/**
	 * Backward-compatible alias for find_trends().
	 *
	 * @param string|null $start Start date.
	 * @param string|null $end End date.
	 *
	 * @return array<string, mixed>
	 */
	public function get_trends( ?string $start, ?string $end ): array {
		return $this->find_trends( $start, $end );
	}
}
