<?php
namespace STOLMC_Service_Tracker\includes\Utils;

/**
 * WordPress transaction helper for managing database transactions
 * when using WordPress core functions.
 *
 * This class provides transaction management for operations that use
 * WordPress functions like wp_insert_user() and update_user_meta()
 * which don't use the STOLMC_Service_Tracker_Sql class directly.
 *
 * Usage:
 * ```php
 * $transaction = new STOLMC_Service_Tracker_WordPress_Transaction();
 * try {
 *     $user_id = wp_insert_user($user_data);
 *     if (is_wp_error($user_id)) {
 *         $transaction->rollback();
 *         // Handle error
 *     }
 *     
 *     update_user_meta($user_id, 'phone', $phone);
 *     $transaction->commit();
 * } catch (\Exception $e) {
 *     $transaction->rollback();
 *     throw $e;
 * }
 * ```
 *
 * @since 1.5.0
 */
class STOLMC_Service_Tracker_WordPress_Transaction {

	/**
	 * Whether the transaction has been committed.
	 *
	 * @since 1.5.0
	 * @var bool
	 */
	private $committed = false;

	/**
	 * Whether the transaction was started by this instance.
	 *
	 * @since 1.5.0
	 * @var bool
	 */
	private $started_transaction = false;

	/**
	 * Constructor.
	 *
	 * Starts a new transaction if not already in one.
	 *
	 * @since 1.5.0
	 */
	public function __construct() {
		global $wpdb;

		// Check if already in a transaction by checking $wpdb state
		if ( ! $this->in_transaction() ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$result = $wpdb->query( 'START TRANSACTION' );
			if ( $result !== false ) {
				$this->started_transaction = true;
				
				/**
				 * Fires after a WordPress transaction has been started.
				 *
				 * @since 1.5.0
				 */
				do_action( 'stolmc_service_tracker_wordpress_transaction_started' );
			}
		}
	}

	/**
	 * Commit the transaction.
	 *
	 * Commits all changes made during the transaction.
	 *
	 * @since 1.5.0
	 *
	 * @return bool True if committed successfully, false otherwise.
	 */
	public function commit(): bool {
		global $wpdb;

		if ( $this->committed ) {
			return false; // Already committed
		}

		// Only commit if we started the transaction
		if ( $this->started_transaction ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$result = $wpdb->query( 'COMMIT' );
			if ( $result !== false ) {
				$this->committed = true;
				
				/**
				 * Fires after a WordPress transaction has been committed.
				 *
				 * @since 1.5.0
				 */
				do_action( 'stolmc_service_tracker_wordpress_transaction_committed' );
			}
			return $result !== false;
		}

		// If we didn't start the transaction, just mark as committed
		$this->committed = true;
		return true;
	}

	/**
	 * Rollback the transaction.
	 *
	 * Reverts all changes made during the transaction.
	 *
	 * @since 1.5.0
	 *
	 * @return bool True if rolled back successfully, false otherwise.
	 */
	public function rollback(): bool {
		global $wpdb;

		if ( $this->committed ) {
			return false; // Already committed, cannot rollback
		}

		// Only rollback if we started the transaction
		if ( $this->started_transaction ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$result = $wpdb->query( 'ROLLBACK' );
			if ( $result !== false ) {
				/**
				 * Fires after a WordPress transaction has been rolled back.
				 *
				 * @since 1.5.0
				 */
				do_action( 'stolmc_service_tracker_wordpress_transaction_rolled_back' );
			}
			return $result !== false;
		}

		return true;
	}

	/**
	 * Check if currently in a transaction.
	 *
	 * Note: This is a best-effort check since WordPress doesn't
	 * expose transaction state directly.
	 *
	 * @since 1.5.0
	 *
	 * @return bool True if likely in a transaction, false otherwise.
	 */
	public function in_transaction(): bool {
		global $wpdb;

		// Try to detect if we're in a transaction by checking
		// if we can start and rollback a savepoint
		// This is a heuristic and may not be 100% accurate
		static $transaction_check = null;

		if ( $transaction_check === null ) {
			// Try to create a savepoint - if it fails, we might be in a transaction
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$savepoint_result = $wpdb->query( 'SAVEPOINT st_transaction_check' );
			if ( $savepoint_result !== false ) {
				// Clean up the savepoint
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->query( 'ROLLBACK TO SAVEPOINT st_transaction_check' );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->query( 'RELEASE SAVEPOINT st_transaction_check' );
				$transaction_check = false;
			} else {
				$transaction_check = true;
			}
		}

		return $transaction_check;
	}

	/**
	 * Check if transaction has been committed.
	 *
	 * @since 1.5.0
	 *
	 * @return bool True if committed, false otherwise.
	 */
	public function is_committed(): bool {
		return $this->committed;
	}

	/**
	 * Destructor.
	 *
	 * Automatically rolls back the transaction if it wasn't committed.
	 *
	 * @since 1.5.0
	 */
	public function __destruct() {
		if ( ! $this->committed && $this->started_transaction ) {
			$this->rollback();
		}
	}
}
