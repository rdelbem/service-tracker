<?php

namespace STOLMC_Service_Tracker\includes\Analytics;

defined( 'ABSPATH' ) || exit;

/**
 * Analytics Logger — handles persistent logging of notifications
 * and admin/staff activity for the Service Tracker analytics system.
 *
 * @since    1.2.0
 * @package  STOLMC_Service_Tracker
 */
class STOLMC_Analytics_Logger {

	/**
	 * Notifications table name.
	 *
	 * @var string
	 */
	private $notifications_table;

	/**
	 * Activity log table name.
	 *
	 * @var string
	 */
	private $activity_log_table;

	/**
	 * Constructor.
	 *
	 * @param string $notifications_table Full notifications table name.
	 * @param string $activity_log_table  Full activity log table name.
	 */
	public function __construct( string $notifications_table, string $activity_log_table ) {
		$this->notifications_table = $notifications_table;
		$this->activity_log_table  = $activity_log_table;
	}

	/**
	 * Log a notification/email attempt.
	 *
	 * @param array<string, mixed> $args {
	 *     Notification arguments.
	 *
	 *     @type int         $id_user       The customer user ID (recipient).
	 *     @type int         $id_case       The case ID.
	 *     @type int|null    $id_progress   The progress ID (nullable).
	 *     @type int|null    $actor_user_id The admin/staff user ID who triggered it.
	 *     @type string      $channel       Notification channel (default 'email').
	 *     @type string      $status        Status: 'attempted', 'sent', or 'failed'.
	 *     @type string      $recipient     The email recipient address.
	 *     @type string      $subject       The email subject.
	 *     @type string|null $error_message Error message if failed (nullable).
	 * }
	 *
	 * @return int|false The notification ID on success, false on failure.
	 */
	public function log_notification( array $args ): int|false {
		global $wpdb;

		$defaults = [
			'id_user'       => 0,
			'id_case'       => 0,
			'id_progress'   => null,
			'actor_user_id' => null,
			'channel'       => 'email',
			'status'        => 'attempted',
			'recipient'     => '',
			'subject'       => '',
			'error_message' => null,
		];

		$data = wp_parse_args( $args, $defaults );

		$formats = [
			'%d', // id_user.
			'%d', // id_case.
			null !== $data['id_progress'] ? '%d' : '%s', // id_progress.
			null !== $data['actor_user_id'] ? '%d' : '%s', // actor_user_id.
			'%s', // channel.
			'%s', // status.
			'%s', // recipient.
			'%s', // subject.
			null !== $data['error_message'] ? '%s' : '%s', // error_message.
		];

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->insert(
			$this->notifications_table,
			[
				'id_user'       => (int) $data['id_user'],
				'id_case'       => (int) $data['id_case'],
				'id_progress'   => null !== $data['id_progress'] ? (int) $data['id_progress'] : null,
				'actor_user_id' => null !== $data['actor_user_id'] ? (int) $data['actor_user_id'] : null,
				'channel'       => sanitize_text_field( $data['channel'] ),
				'status'        => sanitize_text_field( $data['status'] ),
				'recipient'     => sanitize_email( $data['recipient'] ),
				'subject'       => sanitize_text_field( $data['subject'] ),
				'error_message' => null !== $data['error_message'] ? sanitize_text_field( $data['error_message'] ) : null,
			],
			$formats
		);

		if ( false === $result ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log(
				sprintf(
					'[Service Tracker] Failed to log notification: %s',
					$wpdb->last_error
				)
			);
			return false;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return (int) $wpdb->insert_id;
	}

	/**
	 * Log an admin/staff activity event.
	 *
	 * @param array<string, mixed> $args {
	 *     Activity arguments.
	 *
	 *     @type int|null    $actor_user_id  The admin/staff user ID.
	 *     @type string|null $actor_email    The actor's email (optional snapshot).
	 *     @type string|null $actor_name     The actor's display name (optional snapshot).
	 *     @type string      $action_type    The action performed (e.g., 'created', 'updated', 'deleted').
	 *     @type string      $entity_type    The entity type (e.g., 'case', 'progress', 'user', 'notification').
	 *     @type int|null    $entity_id      The entity ID affected (nullable).
	 *     @type int|null    $target_user_id The customer user ID involved (nullable).
	 *     @type int|null    $case_id        The case ID involved (nullable).
	 *     @type int|null    $progress_id    The progress ID involved (nullable).
	 *     @type array|null  $metadata       Additional metadata as associative array (nullable).
	 * }
	 *
	 * @return int|false The activity log ID on success, false on failure.
	 */
	public function log_activity( array $args ): int|false {
		global $wpdb;

		$defaults = [
			'actor_user_id'  => null,
			'actor_email'    => null,
			'actor_name'     => null,
			'action_type'    => '',
			'entity_type'    => '',
			'entity_id'      => null,
			'target_user_id' => null,
			'case_id'        => null,
			'progress_id'    => null,
			'metadata'       => null,
		];

		$data = wp_parse_args( $args, $defaults );

		$metadata_json = null;
		if ( \is_array( $data['metadata'] ) ) {
			$metadata_json = wp_json_encode( $data['metadata'] );
		}

		$formats = [
			null !== $data['actor_user_id'] ? '%d' : '%s', // actor_user_id.
			null !== $data['actor_email'] ? '%s' : '%s', // actor_email.
			null !== $data['actor_name'] ? '%s' : '%s', // actor_name.
			'%s', // action_type.
			'%s', // entity_type.
			null !== $data['entity_id'] ? '%d' : '%s', // entity_id.
			null !== $data['target_user_id'] ? '%d' : '%s', // target_user_id.
			null !== $data['case_id'] ? '%d' : '%s', // case_id.
			null !== $data['progress_id'] ? '%d' : '%s', // progress_id.
			'%s', // metadata.
		];

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->insert(
			$this->activity_log_table,
			[
				'actor_user_id'  => null !== $data['actor_user_id'] ? (int) $data['actor_user_id'] : null,
				'actor_email'    => null !== $data['actor_email'] ? sanitize_email( $data['actor_email'] ) : null,
				'actor_name'     => null !== $data['actor_name'] ? sanitize_text_field( $data['actor_name'] ) : null,
				'action_type'    => sanitize_text_field( $data['action_type'] ),
				'entity_type'    => sanitize_text_field( $data['entity_type'] ),
				'entity_id'      => null !== $data['entity_id'] ? (int) $data['entity_id'] : null,
				'target_user_id' => null !== $data['target_user_id'] ? (int) $data['target_user_id'] : null,
				'case_id'        => null !== $data['case_id'] ? (int) $data['case_id'] : null,
				'progress_id'    => null !== $data['progress_id'] ? (int) $data['progress_id'] : null,
				'metadata'       => $metadata_json,
			],
			$formats
		);

		if ( false === $result ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log(
				sprintf(
					'[Service Tracker] Failed to log activity: %s',
					$wpdb->last_error
				)
			);
			return false;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return (int) $wpdb->insert_id;
	}

	/**
	 * Capture the current actor (admin/staff user) information.
	 *
	 * @return array{actor_user_id: int|null, actor_email: string|null, actor_name: string|null}
	 */
	public static function capture_current_actor(): array {
		$actor_user_id = get_current_user_id();

		if ( ! $actor_user_id ) {
			return [
				'actor_user_id' => null,
				'actor_email'   => null,
				'actor_name'    => null,
			];
		}

		$user = get_user_by( 'id', $actor_user_id );

		return [
			'actor_user_id' => $actor_user_id,
			'actor_email'   => $user ? $user->user_email : null,
			'actor_name'    => $user ? $user->display_name : null,
		];
	}
}
