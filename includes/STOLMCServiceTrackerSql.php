<?php
namespace STOLMCServiceTracker\includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * SQL helper class for database operations.
 *
 * Provides generic CRUD operations for custom database tables.
 * This class wraps $wpdb methods for simplified database interactions.
 */
class STOLMCServiceTrackerSql {

	/**
	 * The current table name.
	 *
	 * @var string|false
	 */
	private $table_name = false;

	/**
	 * Constructor for the database class to inject the table name.
	 *
	 * @param string $table_name The current table name.
	 */
	public function __construct( $table_name ) {
		$this->table_name = $table_name;
	}

	/**
	 * Insert data into the current table.
	 *
	 * @param array $data Data to enter into the database table.
	 *
	 * @return string|false Insert result message or false on failure.
	 */
	public function insert( array $data ) {
		global $wpdb;

		if ( empty( $data ) ) {
			return false;
		}

		try {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Generic insert helper method.
			$wpdb->insert( $this->table_name, $data );
			return 'Success, data was inserted' . $wpdb->insert_id;
		} catch ( \Throwable $th ) {
			return 'Error: ' . $th;
		}
	}

	/**
	 * Get all records from the selected table.
	 *
	 * @param string|null $order_by Order by column name.
	 *
	 * @return array|object|null Table results.
	 */
	public function get_all( $order_by = null ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Generic select helper with optional ordering.
		$sql = 'SELECT * FROM `' . $this->table_name . '`';

		if ( ! empty( $order_by ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Column name for ORDER BY cannot be parameterized.
			$sql .= ' ORDER BY ' . $order_by;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Dynamic query with optional ORDER BY.
		return $wpdb->get_results( $sql );
	}

	/**
	 * Get records by condition.
	 *
	 * @param array  $condition_value A key-value pair of the conditions to search on.
	 * @param string $condition       A string value for the condition of the query. Defaults to equals.
	 *
	 * @throws \Exception If values for IN query are not an array.
	 *
	 * @return array|object|null Query results.
	 */
	public function get_by( array $condition_value, $condition = '=' ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Generic select helper building WHERE clause.
		$sql = 'SELECT * FROM `' . $this->table_name . '` WHERE ';

		foreach ( $condition_value as $field => $value ) {
			switch ( strtolower( $condition ) ) {
				case 'in':
					if ( ! is_array( $value ) ) {
						throw new \Exception( 'Values for IN query must be an array.', 1 );
					}

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Dynamic IN clause handling.
					$sql .= $wpdb->prepare( '`%s` IN (%s)', $field, implode( ',', $value ) );
					break;

				default:
					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Dynamic WHERE clause handling.
					$sql .= $wpdb->prepare( '`' . $field . '` ' . $condition . ' %s', $value );
					break;
			}
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Dynamic query built above.
		return $wpdb->get_results( $sql );
	}

	/**
	 * Update table records in the database.
	 *
	 * @param array $data            Array of data to be updated.
	 * @param array $condition_value Key-value pair for the WHERE clause of the query.
	 *
	 * @return int|false Number of rows updated, or false on failure.
	 */
	public function update( array $data, array $condition_value ) {
		global $wpdb;

		if ( empty( $data ) ) {
			return false;
		}

		try {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Generic update helper method.
			$updated = $wpdb->update( $this->table_name, $data, $condition_value );
			return $updated;
		} catch ( \Throwable $th ) {
			return $th;
		}
	}

	/**
	 * Delete rows from the database table.
	 *
	 * @param array $condition_value Key-value pair for the WHERE clause of the query.
	 *
	 * @return int|false Number of rows deleted, or false on failure.
	 */
	public function delete( array $condition_value ) {
		if ( empty( $condition_value ) ) {
			return -1;
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Generic delete helper method.
		$deleted = $wpdb->delete( $this->table_name, $condition_value );

		return $deleted;
	}
}
