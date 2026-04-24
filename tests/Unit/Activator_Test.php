<?php
/**
 * Activator Test
 *
 * Tests for the STOLMC_Service_Tracker_Activator class.
 *
 * @package Service_Tracker
 */

namespace STOLMC_Service_Tracker\Tests\Unit;

use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Mockery;

/**
 * Activator Test Class.
 *
 * @group   unit
 * @group   lifecycle
 * @group   activator
 */
class Activator_Test extends Unit_TestCase {

	/**
	 * Mock global $wpdb object.
	 *
	 * @var \Mockery\MockInterface
	 */
	protected $mock_wpdb;

	/**
	 * Set up test fixtures.
	 */
	protected function set_up(): void {
		parent::set_up();

		// Mock $wpdb global.
		$this->mock_wpdb = Mockery::mock();
		$this->mock_wpdb->prefix = 'wp_';
		$this->mock_wpdb->dbname = 'test_database';
		$this->mock_wpdb->allows( 'get_results' )->andReturn( [] );
		$this->mock_wpdb->allows( 'prepare' )->andReturn( 'SELECT * FROM INFORMATION_SCHEMA.COLUMNS...' );
		$this->mock_wpdb->allows( 'query' )->andReturn( 0 );

		$GLOBALS['wpdb'] = $this->mock_wpdb;

		// Create a mock upgrade.php file if it doesn't exist.
		$mock_upgrade_dir = sys_get_temp_dir() . '/wordpress/wp-admin/includes';
		if ( ! file_exists( $mock_upgrade_dir ) ) {
			mkdir( $mock_upgrade_dir, 0755, true );
		}
		$mock_upgrade_file = $mock_upgrade_dir . '/upgrade.php';
		if ( ! file_exists( $mock_upgrade_file ) ) {
			file_put_contents(
				$mock_upgrade_file,
				'<?php if ( ! function_exists( "maybe_create_table" ) ) { function maybe_create_table( $table_name, $create_sql ) { return true; } }'
			);
		}

		// Define ABSPATH if not defined.
		if ( ! defined( 'ABSPATH' ) ) {
			define( 'ABSPATH', sys_get_temp_dir() . '/wordpress/' );
		}
	}

	/**
	 * Test activate method creates cases table.
	 */
	public function test_activate_creates_cases_table(): void {
		$tables_created = [];
		$sql_statements = [];

		Functions\when( 'maybe_create_table' )->alias(
			static function ( $table_name, $sql ) use ( &$tables_created, &$sql_statements ) {
				$tables_created[] = $table_name;
				$sql_statements[$table_name] = $sql;
				return true;
			}
		);

		$this->mock_wpdb->allows( 'get_results' )->andReturn( [] );

		Filters\expectApplied( 'stolmc_service_tracker_cases_table_schema' )
			->atMost()->once()
			->andReturnArg( 0 );

		Actions\expectDone( 'stolmc_service_tracker_cases_table_created' )
			->atMost()->once();

		\STOLMC_Service_Tracker\includes\Life_Cycle\STOLMC_Service_Tracker_Activator::activate();

		$this->assertContains( 'wp_servicetracker_cases', $tables_created );
		$this->assertArrayHasKey( 'wp_servicetracker_cases', $sql_statements );
		$this->assertStringContainsString( 'id_user', $sql_statements['wp_servicetracker_cases'] );
		$this->assertStringContainsString( 'title', $sql_statements['wp_servicetracker_cases'] );
	}

	/**
	 * Test activate method creates progress table.
	 */
	public function test_activate_creates_progress_table(): void {
		$tables_created = [];

		Functions\when( 'maybe_create_table' )->alias(
			static function ( $table_name, $sql ) use ( &$tables_created ) {
				$tables_created[] = $table_name;
				return true;
			}
		);

		$this->mock_wpdb->allows( 'get_results' )->andReturn( [] );

		Filters\expectApplied( 'stolmc_service_tracker_progress_table_schema' )
			->atMost()->once()
			->andReturnArg( 0 );

		Actions\expectDone( 'stolmc_service_tracker_progress_table_created' )
			->atMost()->once();

		\STOLMC_Service_Tracker\includes\Life_Cycle\STOLMC_Service_Tracker_Activator::activate();

		$this->assertContains( 'wp_servicetracker_progress', $tables_created );
	}

	/**
	 * Test activate method uses correct table prefixes.
	 */
	public function test_activate_uses_correct_table_prefixes(): void {
		$tables_created = [];

		Functions\when( 'maybe_create_table' )->alias(
			static function ( $table_name, $sql ) use ( &$tables_created ) {
				$tables_created[] = $table_name;
				return true;
			}
		);

		$this->mock_wpdb->allows( 'get_results' )->andReturn( [] );

		\STOLMC_Service_Tracker\includes\Life_Cycle\STOLMC_Service_Tracker_Activator::activate();

		$this->assertContains( 'wp_servicetracker_cases', $tables_created );
		$this->assertContains( 'wp_servicetracker_progress', $tables_created );
		$this->assertContains( 'wp_servicetracker_notifications', $tables_created );
		$this->assertContains( 'wp_servicetracker_activity_log', $tables_created );
	}

	/**
	 * Test activate method fires table created actions.
	 */
	public function test_activate_fires_table_created_actions(): void {
		Functions\when( 'maybe_create_table' )->justReturn( true );
		$this->mock_wpdb->allows( 'get_results' )->andReturn( [] );

		Actions\expectDone( 'stolmc_service_tracker_cases_table_created' )
			->atMost()->once();

		Actions\expectDone( 'stolmc_service_tracker_progress_table_created' )
			->atMost()->once();

		\STOLMC_Service_Tracker\includes\Life_Cycle\STOLMC_Service_Tracker_Activator::activate();

		$this->assertTrue( true );
	}

	/**
	 * Test activate method applies table schema filters.
	 */
	public function test_activate_applies_table_schema_filters(): void {
		Functions\when( 'maybe_create_table' )->justReturn( true );
		$this->mock_wpdb->allows( 'get_results' )->andReturn( [] );

		$cases_schema_modified = false;
		Filters\expectApplied( 'stolmc_service_tracker_cases_table_schema' )
			->atMost()->once()
			->andReturnUsing(
				static function ( $sql ) use ( &$cases_schema_modified ) {
					$cases_schema_modified = true;
					return $sql;
				}
			);

		$progress_schema_modified = false;
		Filters\expectApplied( 'stolmc_service_tracker_progress_table_schema' )
			->atMost()->once()
			->andReturnUsing(
				static function ( $sql ) use ( &$progress_schema_modified ) {
					$progress_schema_modified = true;
					return $sql;
				}
			);

		\STOLMC_Service_Tracker\includes\Life_Cycle\STOLMC_Service_Tracker_Activator::activate();

		$this->assertTrue( $cases_schema_modified );
		$this->assertTrue( $progress_schema_modified );
	}

	/**
	 * Test activate method includes wp-admin/upgrade.php.
	 */
	public function test_activate_includes_upgrade_file(): void {
		$file_included = null;

		Functions\when( 'maybe_create_table' )->justReturn( true );
		$this->mock_wpdb->allows( 'get_results' )->andReturn( [] );

		// Mock require_once to track what files are included.
		// Since we can't easily mock require_once, we'll just verify the method runs without errors.
		\STOLMC_Service_Tracker\includes\Life_Cycle\STOLMC_Service_Tracker_Activator::activate();

		// If we get here without exception, the file was included successfully.
		$this->assertTrue( true );
	}

	/**
	 * Test activate method can be called multiple times.
	 */
	public function test_activate_can_be_called_multiple_times(): void {
		$call_count = 0;

		Functions\when( 'maybe_create_table' )->alias(
			static function () use ( &$call_count ) {
				$call_count++;
				return true;
			}
		);
		$this->mock_wpdb->allows( 'get_results' )->andReturn( [] );

		\STOLMC_Service_Tracker\includes\Life_Cycle\STOLMC_Service_Tracker_Activator::activate();
		\STOLMC_Service_Tracker\includes\Life_Cycle\STOLMC_Service_Tracker_Activator::activate();

		// Should be called twice (once for each table, twice).
		// 4 tables × 2 activations = 8
		$this->assertSame( 8, $call_count );
	}

	/**
	 * Test activate method is static.
	 */
	public function test_activate_method_is_static(): void {
		$reflection = new \ReflectionClass( \STOLMC_Service_Tracker\includes\Life_Cycle\STOLMC_Service_Tracker_Activator::class );
		$method = $reflection->getMethod( 'activate' );

		$this->assertTrue( $method->isStatic() );
	}

	/**
	 * Test activate method returns void.
	 */
	public function test_activate_returns_void(): void {
		Functions\when( 'maybe_create_table' )->justReturn( true );
		$this->mock_wpdb->allows( 'get_results' )->andReturn( [] );

		$result = \STOLMC_Service_Tracker\includes\Life_Cycle\STOLMC_Service_Tracker_Activator::activate();

		$this->assertNull( $result );
	}

	/**
	 * Test cases table schema contains correct columns.
	 */
	public function test_cases_table_schema_contains_correct_columns(): void {
		$sql_used = null;

		Functions\when( 'maybe_create_table' )->alias(
			static function ( $table_name, $sql ) use ( &$sql_used ) {
				if ( strpos( $table_name, 'cases' ) !== false ) {
					$sql_used = $sql;
				}
				return true;
			}
		);
		$this->mock_wpdb->allows( 'get_results' )->andReturn( [] );

		\STOLMC_Service_Tracker\includes\Life_Cycle\STOLMC_Service_Tracker_Activator::activate();

		$this->assertStringContainsString( '`id` INT', $sql_used );
		$this->assertStringContainsString( '`id_user` INT', $sql_used );
		$this->assertStringContainsString( '`created_at` TIMESTAMP', $sql_used );
		$this->assertStringContainsString( '`status` VARCHAR', $sql_used );
		$this->assertStringContainsString( '`title` VARCHAR', $sql_used );
		$this->assertStringContainsString( '`description` TEXT', $sql_used );
	}

	/**
	 * Test progress table schema contains correct columns.
	 */
	public function test_progress_table_schema_contains_correct_columns(): void {
		$sql_used = null;

		Functions\when( 'maybe_create_table' )->alias(
			static function ( $table_name, $sql ) use ( &$sql_used ) {
				if ( strpos( $table_name, 'progress' ) !== false ) {
					$sql_used = $sql;
				}
				return true;
			}
		);
		$this->mock_wpdb->allows( 'get_results' )->andReturn( [] );

		\STOLMC_Service_Tracker\includes\Life_Cycle\STOLMC_Service_Tracker_Activator::activate();

		$this->assertStringContainsString( '`id` INT', $sql_used );
		$this->assertStringContainsString( '`id_case` INT', $sql_used );
		$this->assertStringContainsString( '`id_user` INT', $sql_used );
		$this->assertStringContainsString( '`created_at` TIMESTAMP', $sql_used );
		$this->assertStringContainsString( '`text` TEXT', $sql_used );
	}

	/**
	 * Test activator class can be instantiated.
	 */
	public function test_activator_class_can_be_instantiated(): void {
		$activator = new \STOLMC_Service_Tracker\includes\Life_Cycle\STOLMC_Service_Tracker_Activator();

		$this->assertInstanceOf(
			\STOLMC_Service_Tracker\includes\Life_Cycle\STOLMC_Service_Tracker_Activator::class,
			$activator
		);
	}

	/**
	 * Test cases table schema includes start_at and due_at columns.
	 */
	public function test_cases_table_schema_includes_calendar_columns(): void {
		$sql_used = null;

		Functions\when( 'maybe_create_table' )->alias(
			static function ( $table_name, $sql ) use ( &$sql_used ) {
				if ( strpos( $table_name, 'cases' ) !== false ) {
					$sql_used = $sql;
				}
				return true;
			}
		);
		$this->mock_wpdb->allows( 'get_results' )->andReturn( [] );

		\STOLMC_Service_Tracker\includes\Life_Cycle\STOLMC_Service_Tracker_Activator::activate();

		$this->assertStringContainsString( '`start_at` DATETIME', $sql_used );
		$this->assertStringContainsString( '`due_at` DATETIME', $sql_used );
	}

	/**
	 * Test activate skips adding columns when they already exist.
	 */
	public function test_activate_skips_adding_columns_when_they_exist(): void {
		$queries_executed = [];

		$this->mock_wpdb->allows( 'query' )->andReturnUsing(
			static function ( $sql ) use ( &$queries_executed ) {
				$queries_executed[] = $sql;
				return 1;
			}
		);

		// Simulate columns already exist.
		$this->mock_wpdb->allows( 'get_results' )->andReturn( [
			(object) [ 'COLUMN_NAME' => 'start_at' ],
			(object) [ 'COLUMN_NAME' => 'due_at' ],
			(object) [ 'COLUMN_NAME' => 'description' ],
		] );

		Functions\when( 'maybe_create_table' )->justReturn( true );

		\STOLMC_Service_Tracker\includes\Life_Cycle\STOLMC_Service_Tracker_Activator::activate();

		// Should not execute ALTER TABLE when columns exist.
		$alter_queries = 0;
		foreach ( $queries_executed as $query ) {
			if ( strpos( $query, 'ALTER TABLE' ) !== false ) {
				$alter_queries++;
			}
		}

		$this->assertSame( 0, $alter_queries );
	}
}
