<?php

namespace STOLMC_Service_Tracker\includes\DB;

/**
 * Schema Manager — reconciles the actual database state against the
 * declarative schema definition and applies incremental migrations.
 *
 * Lifecycle:
 *   1. On every `init` hook the manager checks whether the stored
 *      DB version (wp_options) matches Schema::VERSION.
 *   2. If they match, nothing happens (fast path).
 *   3. If the stored version is lower, the manager compares the
 *      current DB against the declarative schema and applies only
 *      the necessary ALTER TABLE statements (add columns, remove
 *      columns, add/drop indexes).
 *   4. After all migrations succeed the option is updated to the
 *      new version.
 *
 * Activation creates tables via maybe_create_table() using the
 * declarative schema.  Deactivation drops them entirely.  This class
 * handles *updates* to the schema between versions.
 *
 * @since    1.1.0
 * @package  STOLMC_Service_Tracker\includes\DB
 */
class Schema_Manager {

	/**
	 * Option key for storing the applied migrations log.
	 *
	 * @since 1.1.0
	 * @var   string
	 */
	public const MIGRATIONS_LOG_OPTION = 'stolmc_service_tracker_migrations_log';

	/**
	 * Run the schema reconciliation.
	 *
	 * Should be called on the `init` hook.  Returns early if the
	 * database is already up-to-date.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function sync(): void {
		$stored_version = (int) get_option( Schema::VERSION_OPTION, 0 );
		$target_version = Schema::VERSION;

		if ( $stored_version >= $target_version ) {
			return;
		}

		$applied = $this->apply_migrations();

		if ( $applied ) {
			update_option( Schema::VERSION_OPTION, $target_version, true );
			$this->log_migration( $stored_version, $target_version );
		}
	}

	/**
	 * Force-create all tables from the declarative schema.
	 *
	 * Used on plugin activation.  Uses WordPress' maybe_create_table()
	 * which is idempotent (does nothing if the table already exists
	 * with a matching schema).
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function create_tables(): void {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$tables = Schema::get_all_tables();

		foreach ( $tables as $key => $table_def ) {
			$sql = Schema::build_create_table_sql( $table_def );

			/**
			 * Filters the CREATE TABLE SQL for a specific table before creation.
			 *
			 * @see STOLMC_Service_Tracker_Activator for usage.
			 */
			$sql = apply_filters(
				"stolmc_service_tracker_{$key}_table_schema",
				$sql
			);

			$table_name = $table_def['table_name'];
			maybe_create_table( $table_name, $sql );

			/**
			 * Fires after a table has been created (or confirmed).
			 *
			 * @since 1.0.0
			 *
			 * @param string $table_name The full table name.
			 * @param string $sql        The SQL used.
			 */
			do_action( "stolmc_service_tracker_{$key}_table_created", $table_name, $sql );
		}
	}

	/**
	 * Drop all plugin tables.
	 *
	 * Used on plugin deactivation.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function drop_tables(): void {
		global $wpdb;

		$tables = Schema::get_all_tables();

		foreach ( $tables as $table_def ) {
			$table_name = $table_def['table_name'];

			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "DROP TABLE IF EXISTS `{$table_name}`" );
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			$plain_name = Schema::get_plain_table_name( $table_def );

			/**
			 * Fires after a table has been dropped.
			 *
			 * @since 1.0.0
			 *
			 * @param string $table_name The dropped table name.
			 */
			do_action( 'stolmc_service_tracker_table_dropped', $plain_name );
		}
	}

	/**
	 * Compare the current database against the declarative schema and
	 * apply only the necessary ALTER TABLE statements.
	 *
	 * @since 1.1.0
	 *
	 * @return bool True if any migration was applied.
	 */
	private function apply_migrations(): bool {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$tables = Schema::get_all_tables();
		$did_migrate = false;

		foreach ( $tables as $key => $table_def ) {
			$table_name = $table_def['table_name'];

			// If the table doesn't exist yet, create it during migration.
			if ( $this->table_exists( $table_name ) === false ) {
				$sql = Schema::build_create_table_sql( $table_def );
				$sql = apply_filters(
					"stolmc_service_tracker_{$key}_table_schema",
					$sql
				);
				maybe_create_table( $table_name, $sql );
				do_action( "stolmc_service_tracker_{$key}_table_created", $table_name, $sql );
				$did_migrate = true;
				continue;
			}

			$migrated = $this->sync_table( $table_def );
			if ( $migrated ) {
				$did_migrate = true;
			}
		}

		return $did_migrate;
	}

	/**
	 * Synchronize a single table: add missing columns, remove
	 * extra columns, and manage indexes.
	 *
	 * @since 1.1.0
	 *
	 * @param array<string, mixed> $table_def A single table definition from Schema::get_all_tables().
	 * @return bool
	 */
	private function sync_table( array $table_def ): bool {
		global $wpdb;

		$table_name   = $table_def['table_name'];
		$plain_name   = Schema::get_plain_table_name( $table_def );
		$desired_cols = $table_def['columns'];
		$desired_idx  = $table_def['indexes'] ?? [];

		$current_cols = $this->get_table_columns( $table_name );
		$current_idx  = $this->get_table_indexes( $table_name );

		$alter_clauses = [];

		// 1. Find columns to ADD (exist in schema, not in DB).
		foreach ( $desired_cols as $col_def ) {
			$col_name = $col_def['name'];
			if ( ! isset( $current_cols[ $col_name ] ) ) {
				$alter_clauses[] = "ADD COLUMN `{$col_name}` {$col_def['definition']}";
			}
		}

		// 2. Find columns to DROP (exist in DB, not in schema).
		// We intentionally avoid dropping PRIMARY KEY or columns that might be
		// part of foreign keys.  Columns not in the schema are considered
		// leftovers from prior versions and are safe to remove.
		$protected_cols = [ 'id' ];
		foreach ( $current_cols as $col_name => $col_info ) {
			$in_schema = false;
			foreach ( $desired_cols as $def ) {
				if ( $def['name'] === $col_name ) {
					$in_schema = true;
					break;
				}
			}
			if ( ! $in_schema && ! in_array( $col_name, $protected_cols, true ) ) {
				$alter_clauses[] = "DROP COLUMN `{$col_name}`";
			}
		}

		// 3. Find indexes to ADD.
		foreach ( $desired_idx as $idx_def ) {
			$idx_name = $idx_def['name'];
			if ( ! isset( $current_idx[ $idx_name ] ) ) {
				$unique  = ! empty( $idx_def['unique'] ) ? 'UNIQUE ' : '';
				$cols    = implode( ', ', array_map( fn( $c ) => "`$c`", $idx_def['columns'] ) );
				$alter_clauses[] = "ADD {$unique}INDEX `{$idx_name}` ($cols)";
			}
		}

		// 4. Find indexes to DROP (exist in DB, not in schema).
		foreach ( $current_idx as $idx_name => $idx_info ) {
			$in_schema = false;
			foreach ( $desired_idx as $def ) {
				if ( $def['name'] === $idx_name ) {
					$in_schema = true;
					break;
				}
			}
			if ( ! $in_schema ) {
				$alter_clauses[] = "DROP INDEX `{$idx_name}`";
			}
		}

		if ( empty( $alter_clauses ) ) {
			return false;
		}

		$alter_sql = 'ALTER TABLE `' . $table_name . '` ' . implode( ', ', $alter_clauses );

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->query( $alter_sql );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared

		if ( false === $result ) {
			// Log the error but do not halt — partial migrations are better
			// than a fatal error on every page load.
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Logged once during migrations for operator visibility.
				error_log(
				sprintf(
					'[Service Tracker] Schema migration failed for table `%s`: %s',
					$plain_name,
					$wpdb->last_error
				)
			);
		}

		return true;
	}

	/**
	 * Check if a table exists.
	 *
	 * @since 1.1.0
	 *
	 * @param string $table_name Full table name.
	 * @return bool
	 */
	private function table_exists( string $table_name ): bool {
		global $wpdb;

			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$result = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES
					WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s',
					$wpdb->dbname,
					$table_name
				)
			);
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		return (bool) $result;
	}

	/**
	 * Get the current columns of a table.
	 *
	 * Returns an associative array keyed by column name:
	 *   [ 'column_name' => [ 'type' => '...', 'nullable' => '...', 'default' => '...' ], ... ]
	 *
	 * @since 1.1.0
	 *
	 * @param string $table_name Full table name.
	 * @return array<string, array{column_name: string, data_type: string, is_nullable: string, column_default: string}>
	 */
	private function get_table_columns( string $table_name ): array {
		global $wpdb;

			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$results = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_TYPE
					FROM INFORMATION_SCHEMA.COLUMNS
					WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s
					ORDER BY ORDINAL_POSITION',
					$wpdb->dbname,
					$table_name
				),
				ARRAY_A
			);
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( ! is_array( $results ) ) {
			return [];
		}

		$cols = [];
		foreach ( $results as $row ) {
			$cols[ $row['COLUMN_NAME'] ] = $row;
		}

		return $cols;
	}

	/**
	 * Get the current indexes of a table.
	 *
	 * Returns an associative array keyed by index name:
	 *   [ 'index_name' => [ 'non_unique' => '0', 'columns' => ['col1', 'col2'] ], ... ]
	 *
	 * @since 1.1.0
	 *
	 * @param string $table_name Full table name.
	 * @return array<string, array{non_unique: string, columns: string[]}>
	 */
	private function get_table_indexes( string $table_name ): array {
		global $wpdb;

			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$results = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT INDEX_NAME, NON_UNIQUE, COLUMN_NAME, SEQ_IN_INDEX
					FROM INFORMATION_SCHEMA.STATISTICS
					WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s
					ORDER BY INDEX_NAME, SEQ_IN_INDEX',
					$wpdb->dbname,
					$table_name
				),
				ARRAY_A
			);
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( ! is_array( $results ) ) {
			return [];
		}

		$indexes = [];
		foreach ( $results as $row ) {
			$name = $row['INDEX_NAME'];
			if ( 'PRIMARY' === $name ) {
				continue; // Never manage primary keys.
			}
			if ( ! isset( $indexes[ $name ] ) ) {
				$indexes[ $name ] = [
					'non_unique' => $row['NON_UNIQUE'],
					'columns'    => [],
				];
			}
			$indexes[ $name ]['columns'][] = $row['COLUMN_NAME'];
		}

		return $indexes;
	}

	/**
	 * Log a migration event for debugging / auditing.
	 *
	 * @since 1.1.0
	 *
	 * @param int $from Old version.
	 * @param int $to   New version.
	 * @return void
	 */
	private function log_migration( int $from, int $to ): void {
		$log = get_option( self::MIGRATIONS_LOG_OPTION, [] );
		if ( ! is_array( $log ) ) {
			$log = [];
		}

		$log[] = [
			'from'    => $from,
			'to'      => $to,
			'date'    => gmdate( 'Y-m-d H:i:s' ),
			'version' => STOLMC_SERVICE_TRACKER_VERSION,
		];

		// Keep only the last 50 entries.
		if ( count( $log ) > 50 ) {
			$log = array_slice( $log, -50 );
		}

		update_option( self::MIGRATIONS_LOG_OPTION, $log, false );
	}
}
