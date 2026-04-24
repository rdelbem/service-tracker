<?php

namespace STOLMC_Service_Tracker\includes\Analytics;

/**
 * Analytics Hook Integrations — listens to plugin domain hooks
 * and logs activity/notification events for analytics.
 *
 * @since    1.2.0
 * @package  STOLMC_Service_Tracker\includes\Analytics
 */
class Analytics_Hooks {

	/**
	 * Analytics logger instance.
	 *
	 * @var Analytics_Logger
	 */
	private $logger;

	/**
	 * Constructor.
	 *
	 * @param Analytics_Logger $logger The analytics logger instance.
	 */
	public function __construct( Analytics_Logger $logger ) {
		$this->logger = $logger;
	}

	/**
	 * Register all analytics hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		// Case activity hooks.
		add_action( 'stolmc_service_tracker_case_created', [ $this, 'on_case_created' ], 10, 2 );
		add_action( 'stolmc_service_tracker_case_updated', [ $this, 'on_case_updated' ], 10, 3 );
		add_action( 'stolmc_service_tracker_case_deleted', [ $this, 'on_case_deleted' ], 10, 3 );

		// Case status toggle hooks.
		add_action( 'stolmc_service_tracker_case_closed', [ $this, 'on_case_closed' ], 10, 3 );
		add_action( 'stolmc_service_tracker_case_reopened', [ $this, 'on_case_reopened' ], 10, 3 );

		// Progress activity hooks.
		add_action( 'stolmc_service_tracker_progress_created', [ $this, 'on_progress_created' ], 10, 2 );
		add_action( 'stolmc_service_tracker_progress_updated', [ $this, 'on_progress_updated' ], 10, 3 );
		add_action( 'stolmc_service_tracker_progress_deleted', [ $this, 'on_progress_deleted' ], 10, 2 );

		// User activity hooks.
		add_action( 'stolmc_service_tracker_user_created', [ $this, 'on_user_created' ], 10, 2 );

		// Email notification result hooks.
		add_action( 'stolmc_service_tracker_progress_email_result', [ $this, 'on_progress_email_result' ], 10, 6 );
		add_action( 'stolmc_service_tracker_toggle_email_result', [ $this, 'on_toggle_email_result' ], 10, 5 );
	}

	/**
	 * Handle case created event.
	 *
	 * @param int|false         $result    The insert result.
	 * @param array<string,mixed> $case_data The case data.
	 * @param \WP_REST_Request  $data      The REST request.
	 *
	 * @return void
	 */
	// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Hook signature keeps reserved argument slot.
	public function on_case_created( $result, array $case_data, $data = null ): void {
		if ( false === $result ) {
			return;
		}

		$actor = Analytics_Logger::capture_current_actor();

		$this->logger->log_activity(
			[
				'actor_user_id'  => $actor['actor_user_id'],
				'actor_email'    => $actor['actor_email'],
				'actor_name'     => $actor['actor_name'],
				'action_type'    => 'created',
				'entity_type'    => 'case',
				'entity_id'      => (int) $result,
				'target_user_id' => $case_data['id_user'] ?? null,
				'case_id'        => (int) $result,
				'metadata'       => [ 'title' => $case_data['title'] ?? '' ],
			]
		);
	}

	/**
	 * Handle case updated event.
	 *
	 * @param int|false         $response    The update result.
	 * @param array<string,mixed> $update_data The update data.
	 * @param array<string,mixed> $condition   The WHERE condition.
	 * @param \WP_REST_Request  $data        The REST request.
	 *
	 * @return void
	 */
	// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Hook signature keeps reserved argument slot.
	public function on_case_updated( $response, array $update_data, array $condition, $data = null ): void {
		if ( false === $response ) {
			return;
		}

		$actor   = Analytics_Logger::capture_current_actor();
		$case_id = $condition['id'] ?? null;

		$this->logger->log_activity(
			[
				'actor_user_id' => $actor['actor_user_id'],
				'actor_email'   => $actor['actor_email'],
				'actor_name'    => $actor['actor_name'],
				'action_type'   => 'updated',
				'entity_type'   => 'case',
				'entity_id'     => $case_id,
				'case_id'       => $case_id,
				'metadata'      => $update_data,
			]
		);
	}

	/**
	 * Handle case deleted event.
	 *
	 * @param int|false        $delete          The delete result.
	 * @param int|false        $delete_progress The progress delete result.
	 * @param int              $case_id         The case ID.
	 * @param \WP_REST_Request $data            The REST request.
	 *
	 * @return void
	 */
	// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Hook signature keeps reserved argument slot.
	public function on_case_deleted( $delete, $delete_progress, int $case_id, $data = null ): void {
		if ( false === $delete ) {
			return;
		}

		$actor = Analytics_Logger::capture_current_actor();

		$this->logger->log_activity(
			[
				'actor_user_id' => $actor['actor_user_id'],
				'actor_email'   => $actor['actor_email'],
				'actor_name'    => $actor['actor_name'],
				'action_type'   => 'deleted',
				'entity_type'   => 'case',
				'entity_id'     => $case_id,
				'case_id'       => $case_id,
			]
		);
	}

	/**
	 * Handle case closed event.
	 *
	 * @param int|false        $toggle  The toggle result.
	 * @param int              $id_user The user ID.
	 * @param string           $title   The case title.
	 * @param mixed            $data    Optional context payload.
	 *
	 * @return void
	 */
	public function on_case_closed( $toggle, int $id_user, string $title, $data = null ): void {
		if ( false === $toggle ) {
			return;
		}

		$actor   = Analytics_Logger::capture_current_actor();
		$case_id = is_array( $data ) ? ( $data['id'] ?? null ) : null;

		$this->logger->log_activity(
			[
				'actor_user_id'  => $actor['actor_user_id'],
				'actor_email'    => $actor['actor_email'],
				'actor_name'     => $actor['actor_name'],
				'action_type'    => 'closed',
				'entity_type'    => 'case',
				'entity_id'      => $case_id,
				'target_user_id' => $id_user,
				'case_id'        => $case_id,
			]
		);
	}

	/**
	 * Handle case reopened event.
	 *
	 * @param int|false        $toggle  The toggle result.
	 * @param int              $id_user The user ID.
	 * @param string           $title   The case title.
	 * @param mixed            $data    Optional context payload.
	 *
	 * @return void
	 */
	public function on_case_reopened( $toggle, int $id_user, string $title, $data = null ): void {
		if ( false === $toggle ) {
			return;
		}

		$actor   = Analytics_Logger::capture_current_actor();
		$case_id = is_array( $data ) ? ( $data['id'] ?? null ) : null;

		$this->logger->log_activity(
			[
				'actor_user_id'  => $actor['actor_user_id'],
				'actor_email'    => $actor['actor_email'],
				'actor_name'     => $actor['actor_name'],
				'action_type'    => 'reopened',
				'entity_type'    => 'case',
				'entity_id'      => $case_id,
				'target_user_id' => $id_user,
				'case_id'        => $case_id,
			]
		);
	}

	/**
	 * Handle progress created event.
	 *
	 * @param int|string|false $result        The insert result (ID in current flow, legacy success string in older flow).
	 * @param array<string,mixed> $progress_data The progress data.
	 * @param \WP_REST_Request $data          The REST request.
	 *
	 * @return void
	 */
	// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Hook signature keeps reserved argument slot.
	public function on_progress_created( $result, array $progress_data, $data = null ): void {
		if ( false === $result ) {
			return;
		}

		$actor         = Analytics_Logger::capture_current_actor();
		$progress_id   = 0;

		// Current create flow dispatches the created progress ID as an integer.
		if ( is_int( $result ) ) {
			$progress_id = $result;
		} elseif ( is_string( $result ) ) {
			// Legacy flow dispatched "Success..." strings.
			if ( ! str_starts_with( $result, 'Success' ) ) {
				return;
			}
			$progress_id = $this->get_last_insert_id();
		}

		if ( $progress_id <= 0 ) {
			$progress_id = $this->get_last_insert_id();
		}

		if ( $progress_id <= 0 ) {
			return;
		}

		$this->logger->log_activity(
			[
				'actor_user_id'  => $actor['actor_user_id'],
				'actor_email'    => $actor['actor_email'],
				'actor_name'     => $actor['actor_name'],
				'action_type'    => 'created',
				'entity_type'    => 'progress',
				'entity_id'      => $progress_id,
				'target_user_id' => $progress_data['id_user'] ?? null,
				'case_id'        => $progress_data['id_case'] ?? null,
				'progress_id'    => $progress_id,
			]
		);
	}

	/**
	 * Handle progress updated event.
	 *
	 * @param int|false        $response    The update result.
	 * @param array<string,mixed> $update_data The update data.
	 * @param array<string,mixed> $condition   The WHERE condition.
	 * @param \WP_REST_Request $data        The REST request.
	 *
	 * @return void
	 */
	// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Hook signature keeps reserved argument slot.
	public function on_progress_updated( $response, array $update_data, array $condition, $data = null ): void {
		if ( false === $response ) {
			return;
		}

		$actor         = Analytics_Logger::capture_current_actor();
		$progress_id   = $condition['id'] ?? null;

		$this->logger->log_activity(
			[
				'actor_user_id' => $actor['actor_user_id'],
				'actor_email'   => $actor['actor_email'],
				'actor_name'    => $actor['actor_name'],
				'action_type'   => 'updated',
				'entity_type'   => 'progress',
				'entity_id'     => $progress_id,
				'progress_id'   => $progress_id,
			]
		);
	}

	/**
	 * Handle progress deleted event.
	 *
	 * @param int|false        $delete The delete result.
	 * @param int              $id     The progress ID.
	 * @param \WP_REST_Request $data   The REST request.
	 *
	 * @return void
	 */
	// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Hook signature keeps reserved argument slot.
	public function on_progress_deleted( $delete, int $id, $data = null ): void {
		if ( false === $delete ) {
			return;
		}

		$actor = Analytics_Logger::capture_current_actor();

		$this->logger->log_activity(
			[
				'actor_user_id' => $actor['actor_user_id'],
				'actor_email'   => $actor['actor_email'],
				'actor_name'    => $actor['actor_name'],
				'action_type'   => 'deleted',
				'entity_type'   => 'progress',
				'entity_id'     => $id,
				'progress_id'   => $id,
			]
		);
	}

	/**
	 * Handle user created event.
	 *
	 * @param int              $user_id   The user ID.
	 * @param array<string,mixed> $user_data The user data.
	 * @param object|array<string,mixed> $body The request body.
	 * @param string           $password  The generated password.
	 *
	 * @return void
	 */
	// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Hook signature keeps reserved argument slots.
	public function on_user_created( int $user_id, array $user_data, $body = null, string $password = '' ): void {
		$actor = Analytics_Logger::capture_current_actor();

		$this->logger->log_activity(
			[
				'actor_user_id'  => $actor['actor_user_id'],
				'actor_email'    => $actor['actor_email'],
				'actor_name'     => $actor['actor_name'],
				'action_type'    => 'created',
				'entity_type'    => 'user',
				'entity_id'      => $user_id,
				'target_user_id' => $user_id,
				'metadata'       => [
					'user_email' => $user_data['user_email'] ?? '',
					'user_name'  => $user_data['display_name'] ?? '',
				],
			]
		);
	}

	/**
	 * Handle progress email result event.
	 *
	 * This logs the notification attempt with the actual wp_mail() result.
	 *
	 * @param bool   $sent        Whether wp_mail() returned success.
	 * @param string $to          The email recipient.
	 * @param string $subject     The email subject.
	 * @param int    $id_user     The user ID.
	 * @param int    $id_case     The case ID.
	 * @param int    $progress_id The progress ID (if available).
	 *
	 * @return void
	 */
	public function on_progress_email_result( bool $sent, string $to, string $subject, int $id_user, int $id_case, $progress_id ): void {
		$actor = Analytics_Logger::capture_current_actor();

		$this->logger->log_notification(
			[
				'id_user'       => $id_user,
				'id_case'       => $id_case,
				'id_progress'   => $progress_id,
				'actor_user_id' => $actor['actor_user_id'],
				'channel'       => 'email',
				'status'        => $sent ? 'sent' : 'failed',
				'recipient'     => $to,
				'subject'       => $subject,
				'error_message' => $sent ? null : 'wp_mail() returned false',
			]
		);
	}

	/**
	 * Handle toggle email result event.
	 *
	 * @param bool   $sent    Whether wp_mail() returned success.
	 * @param string $to      The email recipient.
	 * @param string $subject The email subject.
	 * @param int    $id_user The user ID.
	 * @param int    $id_case The case ID.
	 *
	 * @return void
	 */
	public function on_toggle_email_result( bool $sent, string $to, string $subject, int $id_user, int $id_case ): void {
		$actor = Analytics_Logger::capture_current_actor();

		$this->logger->log_notification(
			[
				'id_user'       => $id_user,
				'id_case'       => $id_case,
				'id_progress'   => null,
				'actor_user_id' => $actor['actor_user_id'],
				'channel'       => 'email',
				'status'        => $sent ? 'sent' : 'failed',
				'recipient'     => $to,
				'subject'       => $subject,
				'error_message' => $sent ? null : 'wp_mail() returned false',
			]
		);
	}

	/**
	 * Get the last insert ID from $wpdb.
	 *
	 * @return int|null
	 */
	private function get_last_insert_id(): ?int {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->insert_id ? (int) $wpdb->insert_id : null;
	}
}
