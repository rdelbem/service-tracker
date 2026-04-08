<?php
namespace STOLMC_Service_Tracker\includes\Utils;

/**
 * SQL helper class for database operations.
 *
 * Provides generic CRUD operations for custom database tables.
 * This class wraps $wpdb methods for simplified database interactions.
 */
class STOLMC_Service_Tracker_Sql {

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
	public function __construct( string $table_name ) {
		$this->table_name = $table_name;
	}

	/**
	 * Insert data into the current table.
	 *
	 * @param array<string, mixed> $data Data to enter into the database table.
	 *
	 * @return string|false Insert result message or false on failure.
	 */
	public function insert( array $data ): string|false {
		global $wpdb;

		if ( empty( $data ) ) {
			return false;
		}

		/**
		 * Filters the data before inserting into the database.
		 *
		 * @since 1.0.0
		 *
		 * @param array        $data        The data to insert.
		 * @param string|false $table_name  The table name.
		 */
		$data = apply_filters( 'stolmc_service_tracker_sql_insert_data', $data, $this->table_name );

		try {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Generic insert helper method.
			$wpdb->insert( $this->table_name, $data );

			/**
			 * Fires after data has been inserted into the database.
			 *
			 * @since 1.0.0
			 *
			 * @param int|false    $insert_id   The ID of the inserted row.
			 * @param array        $data        The data that was inserted.
			 * @param string|false $table_name  The table name.
			 */
			do_action( 'stolmc_service_tracker_sql_after_insert', $wpdb->insert_id, $data, $this->table_name );

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
	 * @return array<object>|object|null Table results.
	 */
	public function get_all( ?string $order_by = null ): array|object|null {
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
	 * @param array<string, mixed> $condition_value A key-value pair of the conditions to search on.
	 * @param string $condition       A string value for the condition of the query. Defaults to equals.
	 *
	 * @throws \Exception If values for IN query are not an array.
	 *
	 * @return array<object>|object|null Query results.
	 */
	public function get_by( array $condition_value, string $condition = '=' ): array|object|null {
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

		/**
		 * Filters the SQL query before execution.
		 *
		 * @since 1.0.0
		 *
		 * @param string       $sql             The SQL query.
		 * @param array        $condition_value The condition values.
		 * @param string       $condition       The condition operator.
		 * @param string|false $table_name      The table name.
		 */
		$sql = apply_filters( 'stolmc_service_tracker_sql_get_by_query', $sql, $condition_value, $condition, $this->table_name );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Dynamic query built above.
		$results = $wpdb->get_results( $sql );

		/**
		 * Filters the results from the get_by query.
		 *
		 * @since 1.0.0
		 *
		 * @param array|object|null $results         The query results.
		 * @param array             $condition_value The condition values.
		 * @param string            $condition       The condition operator.
		 * @param string|false      $table_name      The table name.
		 */
		return apply_filters( 'stolmc_service_tracker_sql_get_by_results', $results, $condition_value, $condition, $this->table_name );
	}

	/**
	 * Update table records in the database.
	 *
	 * @param array<string, mixed> $data            Array of data to be updated.
	 * @param array<string, mixed> $condition_value Key-value pair for the WHERE clause of the query.
	 *
	 * @return int|false Number of rows updated, or false on failure.
	 */
	public function update( array $data, array $condition_value ): int|false {
		global $wpdb;

		if ( empty( $data ) ) {
			return false;
		}

		/**
		 * Filters the data before updating in the database.
		 *
		 * @since 1.0.0
		 *
		 * @param array        $data            The data to update.
		 * @param array        $condition_value The WHERE condition.
		 * @param string|false $table_name      The table name.
		 */
		$data = apply_filters( 'stolmc_service_tracker_sql_update_data', $data, $condition_value, $this->table_name );

		try {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Generic update helper method.
			$updated = $wpdb->update( $this->table_name, $data, $condition_value );

			/**
			 * Fires after data has been updated in the database.
			 *
			 * @since 1.0.0
			 *
			 * @param int|false    $updated       The number of rows updated.
			 * @param array        $data          The data that was updated.
			 * @param array        $condition_value The WHERE condition.
			 * @param string|false $table_name    The table name.
			 */
			do_action( 'stolmc_service_tracker_sql_after_update', $updated, $data, $condition_value, $this->table_name );

			return $updated;
		} catch ( \Throwable $th ) {
			return false;
		}
	}

	/**
	 * Delete rows from the database table.
	 *
	 * @param array<string, mixed> $condition_value Key-value pair for the WHERE clause of the query.
	 *
	 * @return int|false Number of rows deleted, or false on failure.
	 */
	public function delete( array $condition_value ): int|false {
		if ( empty( $condition_value ) ) {
			return -1;
		}

		global $wpdb;

		/**
		 * Fires before deleting from the database.
		 *
		 * @since 1.0.0
		 *
		 * @param array        $condition_value The WHERE condition.
		 * @param string|false $table_name      The table name.
		 */
		do_action( 'stolmc_service_tracker_sql_before_delete', $condition_value, $this->table_name );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Generic delete helper method.
		$deleted = $wpdb->delete( $this->table_name, $condition_value );

		/**
		 * Fires after deleting from the database.
		 *
		 * @since 1.0.0
		 *
		 * @param int|false    $deleted       The number of rows deleted.
		 * @param array        $condition_value The WHERE condition.
		 * @param string|false $table_name    The table name.
		 */
		do_action( 'stolmc_service_tracker_sql_after_delete', $deleted, $condition_value, $this->table_name );

		return $deleted;
	}
}
