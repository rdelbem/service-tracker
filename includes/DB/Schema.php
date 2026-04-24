<?php

namespace STOLMC_Service_Tracker\includes\DB;

/**
 * Declarative schema definition for all Service Tracker tables.
 *
 * This class is the single source of truth for the database schema.
 * Every column, type, and index for every table managed by this plugin
 * is described here. The Schema_Manager reads this definition and
 * reconciles the actual database state against it.
 *
 * To add a new column in a future version:
 *   1. Add the column definition to the appropriate table below.
 *   2. Bump STOLMC_SERVICE_TRACKER_DB_VERSION.
 *
 * To remove a column: remove it from the definition.
 *
 * @since    1.1.0
 * @package  STOLMC_Service_Tracker\includes\DB
 */
class Schema {

	/**
	 * Current database schema version.
	 *
	 * Increment this constant whenever the schema definition changes
	 * (new columns, removed columns, index changes, etc.). The
	 * Schema_Manager compares this value against the stored
	 * `stolmc_service_tracker_db_version` option and runs migrations
	 * when the stored version is lower.
	 *
	 * @since 1.1.0
	 * @var   int
	 */
	public const VERSION = 5;

	/**
	 * Option key used in wp_options to store the current DB version.
	 *
	 * @since 1.1.0
	 * @var   string
	 */
	public const VERSION_OPTION = 'stolmc_service_tracker_db_version';

	/**
	 * Return the full declarative schema for all plugin tables.
	 *
	 * Each table is an associative array with the following structure:
	 *
	 *   [
	 *     'table_name'   => 'wp_servicetracker_cases',
	 *     'sql_base'     => 'CREATE TABLE {table} (',
	 *     'columns'      => [ ... ],
	 *     'indexes'      => [ ... ],
	 *   ]
	 *
	 * Column definition keys:
	 *   - name        (string) Column name (required)
	 *   - definition  (string)  Raw MySQL column definition (required)
	 *                             e.g. 'VARCHAR(255) NOT NULL'
	 *   - default     (string)  Default value (optional)
	 *   - null        (bool)    Whether NULL is allowed (optional, default true)
	 *
	 * Index definition keys:
	 *   - name    (string) Index name (required)
	 *   - columns (array)  Column(s) to index (required)
	 *   - unique  (bool)   Whether this is a UNIQUE index (optional)
	 *
	 * @since 1.1.0
	 *
	 * @return array<string, mixed>
	 */
	public static function get_all_tables(): array {
		global $wpdb;

		$prefix = $wpdb->prefix;

		return [
			'cases'         => [
				'table_name' => $prefix . 'servicetracker_cases',
				'sql_base'   => 'CREATE TABLE {table} (',
				'columns'    => [
					[
						'name'       => 'id',
						'definition' => 'INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY',
					],
					[
						'name'       => 'id_user',
						'definition' => 'INT(20) NOT NULL',
					],
					[
						'name'       => 'owner_id',
						'definition' => 'INT(20) NULL',
					],
					[
						'name'       => 'created_at',
						'definition' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
					],
					[
						'name'       => 'status',
						'definition' => 'VARCHAR(255)',
					],
					[
						'name'       => 'title',
						'definition' => 'VARCHAR(255)',
					],
					[
						'name'       => 'description',
						'definition' => 'TEXT',
					],
					[
						'name'       => 'start_at',
						'definition' => 'DATETIME NULL',
					],
					[
						'name'       => 'due_at',
						'definition' => 'DATETIME NULL',
					],
				],
				'indexes'    => [
					[
						'name'    => 'id_user',
						'columns' => [ 'id_user' ],
					],
					[
						'name'    => 'owner_id',
						'columns' => [ 'owner_id' ],
					],
				],
			],

			'progress'      => [
				'table_name' => $prefix . 'servicetracker_progress',
				'sql_base'   => 'CREATE TABLE {table} (',
				'columns'    => [
					[
						'name'       => 'id',
						'definition' => 'INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY',
					],
					[
						'name'       => 'id_case',
						'definition' => 'INT(10) NOT NULL',
					],
					[
						'name'       => 'id_user',
						'definition' => 'INT(20) NOT NULL',
					],
					[
						'name'       => 'created_at',
						'definition' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
					],
					[
						'name'       => 'text',
						'definition' => 'TEXT',
					],
					[
						'name'       => 'attachments',
						'definition' => 'JSON NULL',
					],
				],
				'indexes'    => [
					[
						'name'    => 'id_case',
						'columns' => [ 'id_case' ],
					],
				],
			],

			'notifications' => [
				'table_name' => $prefix . 'servicetracker_notifications',
				'sql_base'   => 'CREATE TABLE {table} (',
				'columns'    => [
					[
						'name'       => 'id',
						'definition' => 'INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY',
					],
					[
						'name'       => 'id_user',
						'definition' => 'INT(20) NOT NULL',
					],
					[
						'name'       => 'id_case',
						'definition' => 'INT(10) NOT NULL',
					],
					[
						'name'       => 'id_progress',
						'definition' => 'INT(10) NULL',
					],
					[
						'name'       => 'actor_user_id',
						'definition' => 'INT(20) NULL',
					],
					[
						'name'       => 'channel',
						'definition' => 'VARCHAR(50) DEFAULT \'email\'',
					],
					[
						'name'       => 'status',
						'definition' => 'VARCHAR(20) DEFAULT \'attempted\'',
					],
					[
						'name'       => 'recipient',
						'definition' => 'VARCHAR(255) NOT NULL',
					],
					[
						'name'       => 'subject',
						'definition' => 'TEXT',
					],
					[
						'name'       => 'error_message',
						'definition' => 'TEXT NULL',
					],
					[
						'name'       => 'created_at',
						'definition' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
					],
				],
				'indexes'    => [
					[
						'name'    => 'id_user',
						'columns' => [ 'id_user' ],
					],
					[
						'name'    => 'id_case',
						'columns' => [ 'id_case' ],
					],
					[
						'name'    => 'actor_user_id',
						'columns' => [ 'actor_user_id' ],
					],
					[
						'name'    => 'status',
						'columns' => [ 'status' ],
					],
					[
						'name'    => 'created_at',
						'columns' => [ 'created_at' ],
					],
				],
			],

			'activity_log'  => [
				'table_name' => $prefix . 'servicetracker_activity_log',
				'sql_base'   => 'CREATE TABLE {table} (',
				'columns'    => [
					[
						'name'       => 'id',
						'definition' => 'INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY',
					],
					[
						'name'       => 'actor_user_id',
						'definition' => 'INT(20) NULL',
					],
					[
						'name'       => 'actor_email',
						'definition' => 'VARCHAR(255) NULL',
					],
					[
						'name'       => 'actor_name',
						'definition' => 'VARCHAR(255) NULL',
					],
					[
						'name'       => 'action_type',
						'definition' => 'VARCHAR(50) NOT NULL',
					],
					[
						'name'       => 'entity_type',
						'definition' => 'VARCHAR(50) NOT NULL',
					],
					[
						'name'       => 'entity_id',
						'definition' => 'INT(10) NULL',
					],
					[
						'name'       => 'target_user_id',
						'definition' => 'INT(20) NULL',
					],
					[
						'name'       => 'case_id',
						'definition' => 'INT(10) NULL',
					],
					[
						'name'       => 'progress_id',
						'definition' => 'INT(10) NULL',
					],
					[
						'name'       => 'metadata',
						'definition' => 'LONGTEXT NULL',
					],
					[
						'name'       => 'created_at',
						'definition' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
					],
				],
				'indexes'    => [
					[
						'name'    => 'actor_user_id',
						'columns' => [ 'actor_user_id' ],
					],
					[
						'name'    => 'action_type',
						'columns' => [ 'action_type' ],
					],
					[
						'name'    => 'entity_type',
						'columns' => [ 'entity_type' ],
					],
					[
						'name'    => 'created_at',
						'columns' => [ 'created_at' ],
					],
					[
						'name'    => 'target_user_id',
						'columns' => [ 'target_user_id' ],
					],
					[
						'name'    => 'case_id',
						'columns' => [ 'case_id' ],
					],
				],
			],
		];
	}

	/**
	 * Build a raw CREATE TABLE SQL string from a table definition.
	 *
	 * @since 1.1.0
	 *
	 * @param array<string, mixed> $table_def A single table definition from get_all_tables().
	 * @return string
	 */
	public static function build_create_table_sql( array $table_def ): string {
		$table_name = $table_def['table_name'];
		$columns    = $table_def['columns'];
		$indexes    = $table_def['indexes'] ?? [];

		$parts = [];

		foreach ( $columns as $col ) {
			$parts[] = '`' . $col['name'] . '` ' . $col['definition'];
		}

		foreach ( $indexes as $idx ) {
			$cols     = implode( ', ', array_map( fn ( $c ) => "`$c`", $idx['columns'] ) );
			$unique   = ! empty( $idx['unique'] ) ? 'UNIQUE ' : '';
			$parts[]  = "{$unique}INDEX `{$idx['name']}` ($cols)";
		}

		$columns_sql = implode( ",\n", $parts );

		return "CREATE TABLE `{$table_name}` (\n{$columns_sql}\n)";
	}

	/**
	 * Get the plain (unprefixed) table name.
	 *
	 * @since 1.1.0
	 *
	 * @param array<string, mixed> $table_def A single table definition.
	 * @return string
	 */
	public static function get_plain_table_name( array $table_def ): string {
		global $wpdb;

		$prefix = $wpdb->prefix;
		$full   = $table_def['table_name'];

		if ( str_starts_with( $full, $prefix ) ) {
			return substr( $full, strlen( $prefix ) );
		}

		return $full;
	}
}
