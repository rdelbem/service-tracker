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
	 * Allowed comparison operators for get_by().
	 *
	 * @var array<int, string>
	 */
	private const ALLOWED_OPERATORS = [
		'=',
		'!=',
		'<>',
		'>',
		'>=',
		'<',
		'<=',
		'LIKE',
		'NOT LIKE',
		'IN',
		'NOT IN',
	];

	/**
	 * Constructor for the database class to inject the table name.
	 *
	 * @param string $table_name The current table name.
	 */
	public function __construct( string $table_name ) {
		$this->table_name = $table_name;
	}

	/**
	 * Validate and normalize SQL identifier.
	 *
	 * @param string $identifier SQL identifier.
	 *
	 * @return string|null
	 */
	private function normalize_identifier( string $identifier ): ?string {
		$identifier = trim( $identifier );
		if ( preg_match( '/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier ) !== 1 ) {
			return null;
		}

		return $identifier;
	}

	/**
	 * Get a normalized table reference.
	 *
	 * @return string|null
	 */
	private function get_table_reference(): ?string {
		if ( ! is_string( $this->table_name ) || '' === $this->table_name ) {
			return null;
		}

		$table_name = trim( $this->table_name );
		if ( preg_match( '/^[A-Za-z_][A-Za-z0-9_]*$/', $table_name ) !== 1 ) {
			return null;
		}

		return '`' . $table_name . '`';
	}

	/**
	 * Normalize comparison operator.
	 *
	 * @param string $condition Operator.
	 *
	 * @return string
	 */
	private function normalize_operator( string $condition ): string {
		$operator = strtoupper( trim( $condition ) );
		if ( ! in_array( $operator, self::ALLOWED_OPERATORS, true ) ) {
			return '=';
		}

		return $operator;
	}

	/**
	 * Build a placeholder list and corresponding values.
	 *
	 * @param array<int, mixed> $values Values for placeholders.
	 *
	 * @return array{placeholders: string, values: array<int, mixed>}
	 */
	private function build_placeholders( array $values ): array {
		$placeholders = [];
		$params       = [];

		foreach ( $values as $value ) {
			if ( is_int( $value ) || is_bool( $value ) ) {
				$placeholders[] = '%d';
				$params[]       = (int) $value;
				continue;
			}

			if ( is_float( $value ) ) {
				$placeholders[] = '%f';
				$params[]       = $value;
				continue;
			}

			$placeholders[] = '%s';
			$params[]       = (string) $value;
		}

		return [
			'placeholders' => implode( ', ', $placeholders ),
			'values'       => $params,
		];
	}

	/**
	 * Normalize and validate SELECT column list.
	 *
	 * @param array<int, string> $columns Column names.
	 *
	 * @return array<int, string>
	 */
	private function normalize_columns( array $columns ): array {
		$normalized_columns = [];
		foreach ( $columns as $column ) {
			$normalized = $this->normalize_identifier( (string) $column );
			if ( null !== $normalized ) {
				$normalized_columns[] = $normalized;
			}
		}

		return $normalized_columns;
	}

	/**
	 * Normalize ORDER BY clause.
	 *
	 * @param string|null $order_by Order by expression.
	 *
	 * @return string
	 */
	private function normalize_order_by_clause( ?string $order_by ): string {
		if ( empty( $order_by ) ) {
			return '';
		}

		$normalized_order = trim( $order_by );
		$direction        = 'ASC';

		if ( str_contains( $normalized_order, ' ' ) ) {
			$order_parts      = preg_split( '/\s+/', $normalized_order );
			$normalized_order = $order_parts[0] ?? '';
			$direction_part   = strtoupper( $order_parts[1] ?? 'ASC' );
			if ( in_array( $direction_part, [ 'ASC', 'DESC' ], true ) ) {
				$direction = $direction_part;
			}
		}

		$normalized_order = $this->normalize_identifier( $normalized_order );
		if ( null === $normalized_order ) {
			return '';
		}

		return ' ORDER BY `' . $normalized_order . '` ' . $direction;
	}

	/**
	 * Build WHERE clause and params from condition map.
	 *
	 * Supported keys:
	 * - `field` (defaults to `=` operator)
	 * - `field >=`, `field <=`, `field !=`, `field LIKE`, etc.
	 *
	 * @param array<string, mixed> $conditions Conditions map.
	 *
	 * @return array{where_sql: string, params: array<int, mixed>}
	 */
	private function build_where_clause_from_conditions( array $conditions ): array {
		if ( empty( $conditions ) ) {
			return [
				'where_sql' => '',
				'params'    => [],
			];
		}

		$where  = [];
		$params = [];

		foreach ( $conditions as $raw_field => $value ) {
			$field    = trim( (string) $raw_field );
			$operator = '=';

			if ( preg_match( '/^([A-Za-z_][A-Za-z0-9_]*)(?:\s*(=|!=|<>|>=|<=|>|<|LIKE|NOT LIKE|IN|NOT IN))?$/i', $field, $matches ) === 1 ) {
				$field    = $matches[1];
				$operator = isset( $matches[2] ) ? $this->normalize_operator( $matches[2] ) : '=';
			}

			$normalized_field = $this->normalize_identifier( $field );
			if ( null === $normalized_field ) {
				continue;
			}

			if ( in_array( $operator, [ 'IN', 'NOT IN' ], true ) ) {
				if ( ! is_array( $value ) || empty( $value ) ) {
					continue;
				}

				$placeholder_parts = $this->build_placeholders( array_values( $value ) );
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Field/operator validated; values are placeholders.
				$where[] = '`' . $normalized_field . '` ' . $operator . ' (' . $placeholder_parts['placeholders'] . ')';
				array_push( $params, ...$placeholder_parts['values'] );
				continue;
			}

			if ( null === $value ) {
				if ( in_array( $operator, [ '!=', '<>', 'NOT LIKE', 'NOT IN' ], true ) ) {
					$where[] = '`' . $normalized_field . '` IS NOT NULL';
				} else {
					$where[] = '`' . $normalized_field . '` IS NULL';
				}
				continue;
			}

			$placeholder_parts = $this->build_placeholders( [ $value ] );
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Field/operator validated; values are placeholders.
			$where[] = '`' . $normalized_field . '` ' . $operator . ' ' . $placeholder_parts['placeholders'];
			$params[] = $placeholder_parts['values'][0];
		}

		if ( empty( $where ) ) {
			return [
				'where_sql' => '',
				'params'    => [],
			];
		}

		return [
			'where_sql' => ' WHERE ' . implode( ' AND ', $where ),
			'params'    => $params,
		];
	}

	/**
	 * Get all records with custom selected columns.
	 *
	 * @param array<int, string> $columns  Column names to select.
	 * @param string|null        $order_by Order by expression.
	 *
	 * @return array<object>|object|null
	 */
	public function get_all_with_columns( array $columns, ?string $order_by = null ): array|object|null {
		global $wpdb;

		$table = $this->get_table_reference();
		if ( null === $table ) {
			return [];
		}

		$normalized_columns = $this->normalize_columns( $columns );
		if ( empty( $normalized_columns ) ) {
			return [];
		}

		$column_sql = implode(
			', ',
			array_map(
				static fn( string $column ): string => '`' . $column . '`',
				$normalized_columns
			)
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table, columns and order clause are validated.
		$sql = 'SELECT ' . $column_sql . ' FROM ' . $table . $this->normalize_order_by_clause( $order_by );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Query is built from validated SQL identifiers.
		return $wpdb->get_results( $sql );
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
			$inserted = $wpdb->insert( $this->table_name, $data );

			if ( false === $inserted ) {
				return false;
			}

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
			return false;
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

		$table = $this->get_table_reference();
		if ( null === $table ) {
			return [];
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Generic select helper with validated identifiers.
		$sql = 'SELECT * FROM ' . $table;

		$sql .= $this->normalize_order_by_clause( $order_by );

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

		if ( empty( $condition_value ) ) {
			return [];
		}

		$table = $this->get_table_reference();
		if ( null === $table ) {
			return [];
		}

		$operator = $this->normalize_operator( $condition );
		$where    = [];
		$params   = [];

		foreach ( $condition_value as $field => $value ) {
			$normalized_field = $this->normalize_identifier( (string) $field );
			if ( null === $normalized_field ) {
				continue;
			}

			if ( in_array( $operator, [ 'IN', 'NOT IN' ], true ) ) {
				if ( ! is_array( $value ) ) {
					throw new \Exception( 'Values for IN query must be an array.', 1 );
				}

				if ( empty( $value ) ) {
					return [];
				}

				$placeholder_parts = $this->build_placeholders( array_values( $value ) );
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Field and operator are validated.
				$where[] = '`' . $normalized_field . '` ' . $operator . ' (' . $placeholder_parts['placeholders'] . ')';
				array_push( $params, ...$placeholder_parts['values'] );
				continue;
			}

			if ( null === $value ) {
				if ( in_array( $operator, [ '!=', '<>', 'NOT LIKE' ], true ) ) {
					$where[] = '`' . $normalized_field . '` IS NOT NULL';
				} else {
					$where[] = '`' . $normalized_field . '` IS NULL';
				}
				continue;
			}

			$placeholder_parts = $this->build_placeholders( [ $value ] );
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Field and operator are validated.
			$where[] = '`' . $normalized_field . '` ' . $operator . ' ' . $placeholder_parts['placeholders'];
			$params[] = $placeholder_parts['values'][0];
		}

		if ( empty( $where ) ) {
			return [];
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Query parts are validated and value placeholders prepared.
		$sql = 'SELECT * FROM ' . $table . ' WHERE ' . implode( ' AND ', $where );

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

		if ( ! empty( $params ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Placeholder count is built from the same parameter list.
			$sql = $wpdb->prepare( $sql, ...$params );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Dynamic query built above and prepared when params exist.
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
	 * Count rows by condition.
	 *
	 * @param array<string, mixed> $condition_value Conditions.
	 *
	 * @return int
	 */
	public function count_by( array $condition_value ): int {
		global $wpdb;

		if ( empty( $condition_value ) ) {
			return 0;
		}

		$table = $this->get_table_reference();
		if ( null === $table ) {
			return 0;
		}

		$where  = [];
		$params = [];

		foreach ( $condition_value as $field => $value ) {
			$normalized_field = $this->normalize_identifier( (string) $field );
			if ( null === $normalized_field ) {
				continue;
			}

			if ( null === $value ) {
				$where[] = '`' . $normalized_field . '` IS NULL';
				continue;
			}

			$placeholder_parts = $this->build_placeholders( [ $value ] );
			$where[]           = '`' . $normalized_field . '` = ' . $placeholder_parts['placeholders'];
			$params[]          = $placeholder_parts['values'][0];
		}

		if ( empty( $where ) ) {
			return 0;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Query parts are validated.
		$sql = 'SELECT COUNT(*) FROM ' . $table . ' WHERE ' . implode( ' AND ', $where );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Placeholder count is built from the same parameter list.
		$sql = $wpdb->prepare( $sql, ...$params );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Query prepared above.
		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Count all rows in current table.
	 *
	 * @return int
	 */
	public function count_all(): int {
		global $wpdb;

		$table = $this->get_table_reference();
		if ( null === $table ) {
			return 0;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Table reference validated.
		$sql = 'SELECT COUNT(*) FROM ' . $table;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- No dynamic values beyond validated table name.
		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Count distinct values for one column with optional conditions.
	 *
	 * @param string              $column     Column name.
	 * @param array<string, mixed> $conditions Conditions map.
	 *
	 * @return int
	 */
	public function count_distinct( string $column, array $conditions = [] ): int {
		global $wpdb;

		$table = $this->get_table_reference();
		if ( null === $table ) {
			return 0;
		}

		$normalized_column = $this->normalize_identifier( $column );
		if ( null === $normalized_column ) {
			return 0;
		}

		$where_parts = $this->build_where_clause_from_conditions( $conditions );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table/column/where are validated placeholders.
		$sql = 'SELECT COUNT(DISTINCT `' . $normalized_column . '`) FROM ' . $table . $where_parts['where_sql'];

		if ( ! empty( $where_parts['params'] ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Placeholder count is built from same params list.
			$sql = $wpdb->prepare( $sql, ...$where_parts['params'] );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared when params exist.
		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Get maximum value for one column with optional conditions.
	 *
	 * @param string               $column     Column name.
	 * @param array<string, mixed> $conditions Conditions map.
	 *
	 * @return string|null
	 */
	public function max_of( string $column, array $conditions = [] ): ?string {
		global $wpdb;

		$table = $this->get_table_reference();
		if ( null === $table ) {
			return null;
		}

		$normalized_column = $this->normalize_identifier( $column );
		if ( null === $normalized_column ) {
			return null;
		}

		$where_parts = $this->build_where_clause_from_conditions( $conditions );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table/column/where are validated placeholders.
		$sql = 'SELECT MAX(`' . $normalized_column . '`) FROM ' . $table . $where_parts['where_sql'];

		if ( ! empty( $where_parts['params'] ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Placeholder count is built from same params list.
			$sql = $wpdb->prepare( $sql, ...$where_parts['params'] );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared when params exist.
		$value = $wpdb->get_var( $sql );
		if ( null === $value || '' === $value ) {
			return null;
		}

		return (string) $value;
	}

	/**
	 * Get distinct values for a column with optional conditions.
	 *
	 * @param string               $column     Column name.
	 * @param array<string, mixed> $conditions Conditions map.
	 * @param string|null          $order_by   Optional order by column.
	 *
	 * @return array<int, mixed>
	 */
	public function get_distinct_values( string $column, array $conditions = [], ?string $order_by = null ): array {
		global $wpdb;

		$table = $this->get_table_reference();
		if ( null === $table ) {
			return [];
		}

		$normalized_column = $this->normalize_identifier( $column );
		if ( null === $normalized_column ) {
			return [];
		}

		$where_parts = $this->build_where_clause_from_conditions( $conditions );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table/column/where/order are validated placeholders.
		$sql = 'SELECT DISTINCT `' . $normalized_column . '` FROM ' . $table . $where_parts['where_sql'];
		$sql .= $this->normalize_order_by_clause( $order_by );

		if ( ! empty( $where_parts['params'] ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Placeholder count is built from same params list.
			$sql = $wpdb->prepare( $sql, ...$where_parts['params'] );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared when params exist.
		$results = $wpdb->get_col( $sql );
		if ( ! is_array( $results ) ) {
			return [];
		}

		return $results;
	}

	/**
	 * Get daily grouped count trend.
	 *
	 * @param string               $date_column Date/time column name.
	 * @param array<string, mixed> $conditions  Conditions map.
	 * @param int                  $limit       Maximum rows to return.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function get_daily_counts( string $date_column, array $conditions = [], int $limit = 30 ): array {
		global $wpdb;

		$table = $this->get_table_reference();
		if ( null === $table ) {
			return [];
		}

		$normalized_date_column = $this->normalize_identifier( $date_column );
		if ( null === $normalized_date_column ) {
			return [];
		}

		$limit       = max( 1, $limit );
		$where_parts = $this->build_where_clause_from_conditions( $conditions );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table/column/where are validated placeholders.
		$sql = 'SELECT DATE(`' . $normalized_date_column . '`) as period, COUNT(*) as count FROM ' . $table . $where_parts['where_sql'] . ' GROUP BY DATE(`' . $normalized_date_column . '`) ORDER BY period DESC LIMIT %d';

		$params   = $where_parts['params'];
		$params[] = $limit;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Placeholder count is built from same params list.
		$sql = $wpdb->prepare( $sql, ...$params );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Query prepared above.
		$results = $wpdb->get_results( $sql, ARRAY_A );

		return \is_array( $results ) ? $results : [];
	}

	/**
	 * Get rows by condition using pagination.
	 *
	 * @param array<string, mixed> $condition_value Conditions.
	 * @param int                  $limit           Number of rows.
	 * @param int                  $offset          Row offset.
	 * @param string|null          $order_by        Order by expression.
	 *
	 * @return array<object>|object|null
	 */
	public function get_by_paginated( array $condition_value, int $limit, int $offset, ?string $order_by = null ): array|object|null {
		global $wpdb;

		if ( empty( $condition_value ) ) {
			return [];
		}

		$table = $this->get_table_reference();
		if ( null === $table ) {
			return [];
		}

		$where  = [];
		$params = [];

		foreach ( $condition_value as $field => $value ) {
			$normalized_field = $this->normalize_identifier( (string) $field );
			if ( null === $normalized_field ) {
				continue;
			}

			if ( null === $value ) {
				$where[] = '`' . $normalized_field . '` IS NULL';
				continue;
			}

			$placeholder_parts = $this->build_placeholders( [ $value ] );
			$where[]           = '`' . $normalized_field . '` = ' . $placeholder_parts['placeholders'];
			$params[]          = $placeholder_parts['values'][0];
		}

		if ( empty( $where ) ) {
			return [];
		}

		$limit  = max( 1, $limit );
		$offset = max( 0, $offset );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Query parts are validated.
		$sql = 'SELECT * FROM ' . $table . ' WHERE ' . implode( ' AND ', $where );
		$sql .= $this->normalize_order_by_clause( $order_by );
		$sql .= ' LIMIT %d OFFSET %d';

		$params[] = $limit;
		$params[] = $offset;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Placeholder count is built from the same parameter list.
		$sql = $wpdb->prepare( $sql, ...$params );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Query prepared above.
		return $wpdb->get_results( $sql );
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

	/**
	 * Begin a database transaction.
	 *
	 * Starts a new transaction if not already in one.
	 * Note: MySQL doesn't support true nested transactions.
	 *
	 * @since 1.5.0
	 *
	 * @return bool True if transaction started successfully, false otherwise.
	 */
	public function begin_transaction(): bool {
		global $wpdb;

			// Check if already in a transaction.
		if ( $this->in_transaction() ) {
			return false;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( 'START TRANSACTION' );

		if ( $result !== false ) {
			$this->transaction_active = true;
			/**
			 * Fires after a transaction has been started.
			 *
			 * @since 1.5.0
			 *
			 * @param string|false $table_name The table name.
			 */
			do_action( 'stolmc_service_tracker_sql_transaction_started', $this->table_name );
		}

		return $result !== false;
	}

	/**
	 * Commit the current database transaction.
	 *
	 * Commits all changes made during the current transaction.
	 *
	 * @since 1.5.0
	 *
	 * @return bool True if transaction committed successfully, false otherwise.
	 */
	public function commit(): bool {
		global $wpdb;

			// Check if in a transaction.
		if ( ! $this->in_transaction() ) {
			return false;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( 'COMMIT' );

		if ( $result !== false ) {
			$this->transaction_active = false;
			/**
			 * Fires after a transaction has been committed.
			 *
			 * @since 1.5.0
			 *
			 * @param string|false $table_name The table name.
			 */
			do_action( 'stolmc_service_tracker_sql_transaction_committed', $this->table_name );
		}

		return $result !== false;
	}

	/**
	 * Rollback the current database transaction.
	 *
	 * Reverts all changes made during the current transaction.
	 *
	 * @since 1.5.0
	 *
	 * @return bool True if transaction rolled back successfully, false otherwise.
	 */
	public function rollback(): bool {
		global $wpdb;

			// Check if in a transaction.
		if ( ! $this->in_transaction() ) {
			return false;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( 'ROLLBACK' );

		if ( $result !== false ) {
			$this->transaction_active = false;
			/**
			 * Fires after a transaction has been rolled back.
			 *
			 * @since 1.5.0
			 *
			 * @param string|false $table_name The table name.
			 */
			do_action( 'stolmc_service_tracker_sql_transaction_rolled_back', $this->table_name );
		}

		return $result !== false;
	}

	/**
	 * Check if currently in a database transaction.
	 *
	 * @since 1.5.0
	 *
	 * @return bool True if in a transaction, false otherwise.
	 */
	public function in_transaction(): bool {
		return $this->transaction_active;
	}

	/**
	 * Transaction state flag.
	 *
	 * @since 1.5.0
	 * @var bool
	 */
	private $transaction_active = false;
}
