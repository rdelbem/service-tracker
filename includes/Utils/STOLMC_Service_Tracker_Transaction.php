<?php
namespace STOLMC_Service_Tracker\includes\Utils;

/**
 * Transaction helper for managing database transactions with RAII pattern.
 *
 * This class provides automatic transaction management where the transaction
 * is automatically rolled back if not explicitly committed.
 *
 * Usage:
 * ```php
 * $transaction = new STOLMC_Service_Tracker_Transaction($sql);
 * try {
 *     // Perform database operations
 *     $sql->insert($data);
 *     $transaction->commit();
 * } catch (\Exception $e) {
 *     // Transaction will be automatically rolled back in destructor
 *     throw $e;
 * }
 * ```
 *
 * @since 1.5.0
 */
class STOLMC_Service_Tracker_Transaction {

	/**
	 * SQL helper instance.
	 *
	 * @since 1.5.0
	 * @var STOLMC_Service_Tracker_Sql
	 */
	private $sql;

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
	 *
	 * @param STOLMC_Service_Tracker_Sql $sql SQL helper instance.
	 */
	public function __construct( STOLMC_Service_Tracker_Sql $sql ) {
		$this->sql = $sql;

		// Start transaction if not already in one
		if ( ! $this->sql->in_transaction() ) {
			$this->sql->begin_transaction();
			$this->started_transaction = true;
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
		if ( $this->committed ) {
			return false; // Already committed
		}

		// Only commit if we started the transaction
		if ( $this->started_transaction ) {
			$result = $this->sql->commit();
			if ( $result ) {
				$this->committed = true;
			}
			return $result;
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
		if ( $this->committed ) {
			return false; // Already committed, cannot rollback
		}

		// Only rollback if we started the transaction
		if ( $this->started_transaction ) {
			return $this->sql->rollback();
		}

		return true;
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
	 * Check if currently in a transaction.
	 *
	 * @since 1.5.0
	 *
	 * @return bool True if in a transaction, false otherwise.
	 */
	public function in_transaction(): bool {
		return $this->sql->in_transaction();
	}

	/**
	 * Destructor.
	 *
	 * Automatically rolls back the transaction if it wasn't committed.
	 *
	 * @since 1.5.0
	 */
	public function __destruct() {
		if ( ! $this->committed && $this->started_transaction && $this->sql->in_transaction() ) {
			$this->sql->rollback();
		}
	}
}
