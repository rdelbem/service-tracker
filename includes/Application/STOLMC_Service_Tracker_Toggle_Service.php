<?php

namespace STOLMC_Service_Tracker\includes\Application;

use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Service_Result_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Toggle_Request_Dto;
use STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Toggle_Repository;

/**
 * Toggle Service for business logic operations on case status toggling.
 *
 * This service encapsulates all business logic for case status toggle operations
 * and returns uniform Service Result DTOs.
 */
class STOLMC_Service_Tracker_Toggle_Service {

	/**
	 * Toggle Repository instance.
	 *
	 * @var STOLMC_Service_Tracker_Toggle_Repository
	 */
	private $toggle_repository;

	/**
	 * Constructor.
	 *
	 * @param STOLMC_Service_Tracker_Toggle_Repository|null $toggle_repository Toggle repository.
	 */
	public function __construct( ?STOLMC_Service_Tracker_Toggle_Repository $toggle_repository = null ) {
		$this->toggle_repository = $toggle_repository ?? new STOLMC_Service_Tracker_Toggle_Repository();
	}

	/**
	 * Toggle case status between open and closed.
	 *
	 * @param STOLMC_Service_Tracker_Toggle_Request_Dto $toggle_dto Toggle request DTO.
	 *
	 * @return STOLMC_Service_Tracker_Service_Result_Dto Service result.
	 */
	public function toggle_case_status( STOLMC_Service_Tracker_Toggle_Request_Dto $toggle_dto ): STOLMC_Service_Tracker_Service_Result_Dto {
		$transaction_started = false;

		try {
			$case_id = $toggle_dto->case_id;

			// Validate case ID.
			if ( $case_id <= 0 ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'invalid_case_id',
					'Invalid case ID',
					400
				);
			}

			// Check if case exists and can be toggled.
			if ( ! $this->toggle_repository->can_toggle( $case_id ) ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'cannot_toggle_case',
					'Case cannot be toggled (not found or invalid status)',
					400
				);
			}

			// Get case details for email notification.
			$case = $this->toggle_repository->find_by_id( $case_id );
			if ( null === $case ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'case_not_found',
					'Case not found',
					404
				);
			}

			$id_user = $case->id_user;
			$title   = $case->title;
			$current_status = $case->status;

			// Convert case to array for filter compatibility.
			$case_array = (array) $case;

			/**
			 * Filters the case data before toggle decision.
			 *
			 * @since 1.0.0
			 *
			 * @param array $case_array The case data.
			 * @param int   $case_id    The case ID.
			 */
			$case_array = apply_filters( 'stolmc_service_tracker_toggle_case_data', $case_array, $case_id );

			$transaction_started = $this->toggle_repository->begin_transaction();
			if ( ! $transaction_started ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'transaction_start_failed',
					'Failed to start transaction for case toggle',
					500
				);
			}

			// Determine new status and appropriate messages.
			if ( 'open' === $case_array['status'] ) {
				/**
				 * Fires before a case is closed.
				 *
				 * @since 1.0.0
				 *
				 * @param int   $case_id    The ID of the case.
				 * @param array $case_array The case data.
				 */
				do_action( 'stolmc_service_tracker_case_before_closing', $case_id, $case_array );

				$toggle_result = $this->toggle_repository->close_case( $case_id );

				if ( false === $toggle_result ) {
					$this->toggle_repository->rollback_transaction();

					return STOLMC_Service_Tracker_Service_Result_Dto::fail(
						'close_failed',
						'Failed to close case',
						500
					);
				}

				$new_status = 'close';
				$action = 'closed';
			} elseif ( 'close' === $case_array['status'] ) {
				/**
				 * Fires before a case is reopened.
				 *
				 * @since 1.0.0
				 *
				 * @param int   $case_id    The ID of the case.
				 * @param array $case_array The case data.
				 */
				do_action( 'stolmc_service_tracker_case_before_reopening', $case_id, $case_array );

				$toggle_result = $this->toggle_repository->open_case( $case_id );

				if ( false === $toggle_result ) {
					$this->toggle_repository->rollback_transaction();

					return STOLMC_Service_Tracker_Service_Result_Dto::fail(
						'open_failed',
						'Failed to open case',
						500
					);
				}

				$new_status = 'open';
				$action = 'opened';
			} else {
				$this->toggle_repository->rollback_transaction();

				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'invalid_case_status',
					'Case has invalid status for toggling',
					400
				);
			}

			if ( ! $this->toggle_repository->commit_transaction() ) {
				$this->toggle_repository->rollback_transaction();

				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'transaction_commit_failed',
					'Failed to commit transaction for case toggle',
					500
				);
			}

			/**
			 * Fires after a case has been closed.
			 *
			 * @since 1.0.0
			 *
			 * @param int|false|null $toggle_result The update result.
			 * @param int            $id_user       The user ID.
			 * @param string         $title         The case title.
			 */
			if ( 'closed' === $action ) {
				do_action( 'stolmc_service_tracker_case_closed', $toggle_result, $id_user, $title );
			}

			/**
			 * Fires after a case has been reopened.
			 *
			 * @since 1.0.0
			 *
			 * @param int|false|null $toggle_result The update result.
			 * @param int            $id_user       The user ID.
			 * @param string         $title         The case title.
			 */
			if ( 'opened' === $action ) {
				do_action( 'stolmc_service_tracker_case_reopened', $toggle_result, $id_user, $title );
			}

			// Send email notification after successful commit.
			if ( 'closed' === $action ) {
				$this->send_email_notification( $id_user, $title, $this->get_closed_messages(), $case_id );
			}
			if ( 'opened' === $action ) {
				$this->send_email_notification( $id_user, $title, $this->get_opened_messages(), $case_id );
			}

			$result_data = [
				'case_id'         => $case_id,
				'previous_status' => $current_status,
				'new_status'      => $new_status,
				'action'          => $action,
				'rows_affected'   => $toggle_result,
			];

			return STOLMC_Service_Tracker_Service_Result_Dto::ok( $result_data, 200 );
		} catch ( \Exception $e ) {
			if ( $transaction_started ) {
				$this->toggle_repository->rollback_transaction();
			}

			return STOLMC_Service_Tracker_Service_Result_Dto::fail(
				'toggle_error',
				'Failed to toggle case status: ' . $e->getMessage(),
				500
			);
		}
	}

	/**
	 * Get translated messages for closed state.
	 *
	 * @return array<int, string> Translation messages for closed state.
	 */
	private function get_closed_messages(): array {
		$messages = [
			__( 'Your case was closed!', 'service-tracker-stolmc' ),
			__( 'is now closed!', 'service-tracker-stolmc' ),
		];

		/**
		 * Filters the closed status email messages.
		 *
		 * @since 1.0.0
		 *
		 * @param array $messages Translation messages for closed state.
		 */
		return apply_filters( 'stolmc_service_tracker_closed_status_messages', $messages );
	}

	/**
	 * Get translated messages for opened state.
	 *
	 * @return array<int, string> Translation messages for opened state.
	 */
	private function get_opened_messages(): array {
		$messages = [
			__( 'Your case was opened!', 'service-tracker-stolmc' ),
			__( 'is now opened!', 'service-tracker-stolmc' ),
		];

		/**
		 * Filters the opened status email messages.
		 *
		 * @since 1.0.0
		 *
		 * @param array $messages Translation messages for opened state.
		 */
		return apply_filters( 'stolmc_service_tracker_opened_status_messages', $messages );
	}

	/**
	 * Send email notification about case status change.
	 *
	 * @param int              $user_id        User ID to send email to.
	 * @param string           $title          Case title.
	 * @param array<int, string> $state_messages Translation messages for the state change.
	 * @param int              $case_id        The case ID.
	 *
	 * @return void
	 */
	private function send_email_notification( int $user_id, string $title, array $state_messages, int $case_id ): void {
		/**
		 * Filters the toggle email data before sending.
		 *
		 * @since 1.0.0
		 *
		 * @param array $email_data {
		 *     Email data array.
		 *
		 *     @type int    $user_id        User ID to send email to.
		 *     @type string $subject        The email subject.
		 *     @type string $message        The email message.
		 *     @type string $title          Case title.
		 *     @type array  $state_messages Translation messages for the state change.
		 * }
		 */
		$email_data = apply_filters(
			'stolmc_service_tracker_toggle_email_data',
			[
				'user_id'        => $user_id,
				'subject'        => $state_messages[0],
				'message'        => $title . ' - ' . $state_messages[1],
				'title'          => $title,
				'state_messages' => $state_messages,
			]
		);

		// Get user email from WordPress user.
		$user = get_user_by( 'id', $email_data['user_id'] );
		if ( false !== $user ) {
			/**
			 * Fires before the toggle email is sent.
			 *
			 * @since 1.0.0
			 *
			 * @param string $to      The email recipient.
			 * @param string $subject The email subject.
			 * @param string $message The email message.
			 * @param int    $user_id The user ID.
			 */
			do_action( 'stolmc_service_tracker_toggle_before_email_sent', $user->user_email, $email_data['subject'], $email_data['message'], $user_id );

			$sent = wp_mail( $user->user_email, $email_data['subject'], $email_data['message'] );

			/**
			 * Fires after the toggle email attempt to log notification status.
			 *
			 * @since 1.2.0
			 *
			 * @param bool   $sent    Whether wp_mail() returned success.
			 * @param string $to      The email recipient.
			 * @param string $subject The email subject.
			 * @param int    $user_id The user ID.
			 * @param int    $case_id The case ID.
			 */
			do_action( 'stolmc_service_tracker_toggle_email_result', $sent, $user->user_email, $email_data['subject'], $user_id, $case_id );
		}
	}

	/**
	 * Check if a case can be toggled.
	 *
	 * @param int $case_id The ID of the case to check.
	 *
	 * @return STOLMC_Service_Tracker_Service_Result_Dto Service result with can_toggle boolean.
	 */
	public function can_toggle_case( int $case_id ): STOLMC_Service_Tracker_Service_Result_Dto {
		try {
			// Validate case ID.
			if ( $case_id <= 0 ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'invalid_case_id',
					'Invalid case ID',
					400
				);
			}

			$can_toggle = $this->toggle_repository->can_toggle( $case_id );
			$case = $this->toggle_repository->find_by_id( $case_id );

			$result_data = [
				'can_toggle'     => $can_toggle,
				'case_id'        => $case_id,
				'case_exists'    => null !== $case,
				'current_status' => $case ? $case->status : null,
			];

			return STOLMC_Service_Tracker_Service_Result_Dto::ok( $result_data, 200 );
		} catch ( \Exception $e ) {
			return STOLMC_Service_Tracker_Service_Result_Dto::fail(
				'check_toggle_error',
				'Failed to check if case can be toggled: ' . $e->getMessage(),
				500
			);
		}
	}

	/**
	 * Get case status.
	 *
	 * @param int $case_id The ID of the case.
	 *
	 * @return STOLMC_Service_Tracker_Service_Result_Dto Service result with case status.
	 */
	public function get_case_status( int $case_id ): STOLMC_Service_Tracker_Service_Result_Dto {
		try {
			// Validate case ID.
			if ( $case_id <= 0 ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'invalid_case_id',
					'Invalid case ID',
					400
				);
			}

			$status = $this->toggle_repository->get_status( $case_id );
			$case = $this->toggle_repository->find_by_id( $case_id );

			if ( null === $status ) {
				return STOLMC_Service_Tracker_Service_Result_Dto::fail(
					'case_not_found',
					'Case not found',
					404
				);
			}

			$result_data = [
				'case_id' => $case_id,
				'status'  => $status,
				'title'   => $case ? $case->title : null,
				'user_id' => $case ? $case->id_user : null,
			];

			return STOLMC_Service_Tracker_Service_Result_Dto::ok( $result_data, 200 );
		} catch ( \Exception $e ) {
			return STOLMC_Service_Tracker_Service_Result_Dto::fail(
				'get_status_error',
				'Failed to get case status: ' . $e->getMessage(),
				500
			);
		}
	}
}
