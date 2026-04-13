<?php

namespace STOLMC_Service_Tracker\includes\API;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Analytics API endpoint.
 *
 * Provides aggregated analytics data for the admin dashboard.
 *
 * @since    1.2.0
 * @package  STOLMC_Service_Tracker\includes\API
 */
class STOLMC_Service_Tracker_Api_Analytics extends STOLMC_Service_Tracker_Api {

	/**
	 * Initialize the API and register routes.
	 *
	 * @return void
	 */
	public function run(): void {
		register_rest_route(
			'service-tracker-stolmc/v1',
			'/analytics',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_analytics' ],
				'permission_callback' => [ $this, 'permission_check' ],
			]
		);
	}

	/**
	 * Get aggregated analytics data.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function get_analytics( WP_REST_Request $request ): WP_REST_Response {
		$start = $request->get_param( 'start' );
		$end   = $request->get_param( 'end' );

		$summary        = $this->get_summary_stats();
		$customer_stats = $this->get_customer_stats();
		$admin_stats    = $this->get_admin_stats();
		$trends         = $this->get_trends( $start, $end );

		return $this->rest_response(
			[
				'summary'        => $summary,
				'customer_stats' => $customer_stats,
				'admin_stats'    => $admin_stats,
				'trends'         => $trends,
			],
			200
		);
	}

	/**
	 * Get summary statistics.
	 *
	 * @return array<string, mixed>
	 */
	private function get_summary_stats(): array {
		global $wpdb;

		$cases_table    = $wpdb->prefix . 'servicetracker_cases';
		$progress_table = $wpdb->prefix . 'servicetracker_progress';
		$notifications  = $wpdb->prefix . 'servicetracker_notifications';
		$activity_log   = $wpdb->prefix . 'servicetracker_activity_log';

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total_customers = (int) $wpdb->get_var(
			"SELECT COUNT(DISTINCT id) FROM {$wpdb->users}"
		);

		$total_cases = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$cases_table}" );

		$open_cases = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$cases_table} WHERE status = %s",
				'open'
			)
		);

		$closed_cases = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$cases_table} WHERE status = %s",
				'close'
			)
		);

		$total_progress = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$progress_table}" );

		$notifications_attempted = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$notifications}" );
		$notifications_sent = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$notifications} WHERE status = %s", 'sent' )
		);
		$notifications_failed = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$notifications} WHERE status = %s", 'failed' )
		);

		$active_admins = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT actor_user_id) FROM {$activity_log} WHERE actor_user_id IS NOT NULL AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)",
				30
			)
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

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
	 * @return array<int, array<string, mixed>>
	 */
	private function get_customer_stats(): array {
		global $wpdb;

		$cases_table    = $wpdb->prefix . 'servicetracker_cases';
		$progress_table = $wpdb->prefix . 'servicetracker_progress';
		$notifications  = $wpdb->prefix . 'servicetracker_notifications';

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$customers = $wpdb->get_results(
			"SELECT ID as user_id, display_name, user_email FROM {$wpdb->users}",
			ARRAY_A
		);

		if ( ! is_array( $customers ) ) {
			return [];
		}

		$stats = [];
		foreach ( $customers as $customer ) {
			$user_id = (int) $customer['user_id'];

			$total_cases = (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*) FROM {$cases_table} WHERE id_user = %d", $user_id )
			);

			$open_cases = (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*) FROM {$cases_table} WHERE id_user = %d AND status = %s", $user_id, 'open' )
			);

			$closed_cases = (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*) FROM {$cases_table} WHERE id_user = %d AND status = %s", $user_id, 'close' )
			);

			$progress_updates = (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*) FROM {$progress_table} WHERE id_user = %d", $user_id )
			);

			$notifications_sent = (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*) FROM {$notifications} WHERE id_user = %d AND status = %s", $user_id, 'sent' )
			);

			$last_activity = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT MAX(created_at) FROM (
						SELECT created_at FROM {$cases_table} WHERE id_user = %d
						UNION ALL
						SELECT created_at FROM {$progress_table} WHERE id_user = %d
					) as combined",
					$user_id,
					$user_id
				)
			);

			if ( $total_cases > 0 || $progress_updates > 0 ) {
				$stats[] = [
					'user_id'            => $user_id,
					'name'               => $customer['display_name'],
					'email'              => $customer['user_email'],
					'total_cases'        => $total_cases,
					'open_cases'         => $open_cases,
					'closed_cases'       => $closed_cases,
					'progress_updates'   => $progress_updates,
					'notifications_sent' => $notifications_sent,
					'last_activity_at'   => $last_activity,
				];
			}
		}
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return $stats;
	}

	/**
	 * Get per-admin/staff statistics.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function get_admin_stats(): array {
		global $wpdb;

		$activity_log  = $wpdb->prefix . 'servicetracker_activity_log';
		$notifications = $wpdb->prefix . 'servicetracker_notifications';

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$admins = $wpdb->get_results(
			"SELECT DISTINCT actor_user_id FROM {$activity_log} WHERE actor_user_id IS NOT NULL",
			ARRAY_A
		);

		if ( ! is_array( $admins ) ) {
			return [];
		}

		$stats = [];
		foreach ( $admins as $admin ) {
			$actor_id = (int) $admin['actor_user_id'];
			$user     = get_user_by( 'id', $actor_id );

			if ( ! $user ) {
				continue;
			}

			$cases_created = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$activity_log} WHERE actor_user_id = %d AND entity_type = %s AND action_type = %s",
					$actor_id,
					'case',
					'created'
				)
			);

			$cases_updated = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$activity_log} WHERE actor_user_id = %d AND entity_type = %s AND action_type = %s",
					$actor_id,
					'case',
					'updated'
				)
			);

			$cases_deleted = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$activity_log} WHERE actor_user_id = %d AND entity_type = %s AND action_type = %s",
					$actor_id,
					'case',
					'deleted'
				)
			);

			$progress_created = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$activity_log} WHERE actor_user_id = %d AND entity_type = %s AND action_type = %s",
					$actor_id,
					'progress',
					'created'
				)
			);

			$progress_updated = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$activity_log} WHERE actor_user_id = %d AND entity_type = %s AND action_type = %s",
					$actor_id,
					'progress',
					'updated'
				)
			);

			$progress_deleted = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$activity_log} WHERE actor_user_id = %d AND entity_type = %s AND action_type = %s",
					$actor_id,
					'progress',
					'deleted'
				)
			);

			$notifications_triggered = (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*) FROM {$notifications} WHERE actor_user_id = %d", $actor_id )
			);

			$last_activity = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT MAX(created_at) FROM {$activity_log} WHERE actor_user_id = %d",
					$actor_id
				)
			);

			$stats[] = [
				'user_id'                 => $actor_id,
				'display_name'            => $user->display_name,
				'email'                   => $user->user_email,
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
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return $stats;
	}

	/**
	 * Get trend data by period.
	 *
	 * @param string|null $start Start date.
	 * @param string|null $end   End date.
	 *
	 * @return array<string, mixed>
	 */
	private function get_trends( ?string $start, ?string $end ): array {
		global $wpdb;

		$cases_table    = $wpdb->prefix . 'servicetracker_cases';
		$progress_table = $wpdb->prefix . 'servicetracker_progress';
		$notifications  = $wpdb->prefix . 'servicetracker_notifications';
		$activity_log   = $wpdb->prefix . 'servicetracker_activity_log';

		$where  = '';
		$params = [];

		if ( $start ) {
			$where   .= ' AND created_at >= %s';
			$params[] = $start;
		}

		if ( $end ) {
			$where   .= ' AND created_at <= %s';
			$params[] = $end;
		}

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$cases_by_period    = $this->get_trend_by_period( $cases_table, $where, $params );
		$progress_by_period = $this->get_trend_by_period( $progress_table, $where, $params );
		$notifications_by_period = $this->get_trend_by_period( $notifications, $where, $params );
		$admin_actions_by_period = $this->get_trend_by_period( $activity_log, $where, $params, true );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return [
			'cases_created_by_period'    => $cases_by_period,
			'progress_created_by_period' => $progress_by_period,
			'notifications_by_period'    => $notifications_by_period,
			'admin_actions_by_period'    => $admin_actions_by_period,
		];
	}

	/**
	 * Get trend data for a specific table.
	 *
	 * @param string $table_name   The table name.
	 * @param string $where_clause Additional WHERE conditions.
	 * @param array  $params       Query parameters.
	 * @param bool   $actor_filter Whether to filter by actor_user_id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function get_trend_by_period( string $table_name, string $where_clause, array $params, bool $actor_filter = false ): array {
		global $wpdb;

		$actor_condition = $actor_filter ? ' AND actor_user_id IS NOT NULL' : '';
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$sql = "SELECT DATE(created_at) as period, COUNT(*) as count FROM {$table_name} WHERE 1=1 {$actor_condition} {$where_clause} GROUP BY DATE(created_at) ORDER BY period DESC LIMIT 30";

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$results = empty( $params )
			? $wpdb->get_results( $sql, ARRAY_A )
			: $wpdb->get_results( $wpdb->prepare( $sql, ...$params ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		return \is_array( $results ) ? $results : [];
	}
}
