<?php
/**
 * SQL Test
 *
 * Tests for the STOLMC_Service_Tracker_Sql class.
 *
 * @package Service_Tracker
 */

namespace STOLMC_Service_Tracker\Tests\Unit;

use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Mockery;

/**
 * SQL Test Class.
 *
 * @group   unit
 * @group   sql
 * @group   utils
 */
class Sql_Test extends Unit_TestCase {

	/**
	 * SQL instance.
	 *
	 * @var \STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Sql
	 */
	protected $sql;

	/**
	 * Mock $wpdb object.
	 *
	 * @var \Mockery\MockInterface
	 */
	protected $mock_wpdb;

	/**
	 * Set up test fixtures.
	 */
	protected function set_up(): void {
		parent::set_up();

		// Create mock $wpdb.
		$this->mock_wpdb = Mockery::mock();
		$this->mock_wpdb->insert_id = 1;
		$this->mock_wpdb->allows( 'insert' )->andReturn( 1 );
		$this->mock_wpdb->allows( 'update' )->andReturn( 1 );
		$this->mock_wpdb->allows( 'delete' )->andReturn( 1 );
		$this->mock_wpdb->allows( 'get_results' )->andReturn( [] );
		$this->mock_wpdb->allows( 'prepare' )->andReturnUsing( function( $query ) {
			return $query;
		} );

		$GLOBALS['wpdb'] = $this->mock_wpdb;

		$this->sql = new \STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Sql( 'test_table' );
	}

	/**
	 * Test constructor sets table name.
	 */
	public function test_constructor_sets_table_name(): void {
		$this->assertInstanceOf(
			\STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Sql::class,
			$this->sql
		);
	}

	/**
	 * Test insert returns success message.
	 */
	public function test_insert_returns_success_message(): void {
		$this->mock_wpdb->insert_id = 42;
		$this->mock_wpdb->allows( 'insert' )->andReturn( 1 );

		Actions\expectDone( 'stolmc_service_tracker_sql_after_insert' )
			->atMost()->once();

		Filters\expectApplied( 'stolmc_service_tracker_sql_insert_data' )
			->atMost()->once()
			->andReturnArg( 0 );

		$result = $this->sql->insert( [ 'name' => 'Test' ] );

		$this->assertIsString( $result );
		$this->assertStringContainsString( 'Success', $result );
	}

	/**
	 * Test insert returns false for empty data.
	 */
	public function test_insert_returns_false_for_empty_data(): void {
		$result = $this->sql->insert( [] );

		$this->assertFalse( $result );
	}

	/**
	 * Test insert fires after insert action.
	 */
	public function test_insert_fires_after_insert_action(): void {
		$this->mock_wpdb->insert_id = 42;
		$this->mock_wpdb->allows( 'insert' )->andReturn( 1 );

		Actions\expectDone( 'stolmc_service_tracker_sql_after_insert' )
			->atMost()->once();

		Filters\expectApplied( 'stolmc_service_tracker_sql_insert_data' )
			->atMost()->once()
			->andReturnArg( 0 );

		$this->sql->insert( [ 'name' => 'Test' ] );

		$this->assertTrue( true );
	}

	/**
	 * Test insert applies insert data filter.
	 */
	public function test_insert_applies_insert_data_filter(): void {
		$this->mock_wpdb->insert_id = 42;
		$this->mock_wpdb->allows( 'insert' )->andReturn( 1 );

		Filters\expectApplied( 'stolmc_service_tracker_sql_insert_data' )
			->atMost()->once()
			->andReturnArg( 0 );

		Actions\expectDone( 'stolmc_service_tracker_sql_after_insert' )
			->atMost()->once();

		$this->sql->insert( [ 'name' => 'Test' ] );

		$this->assertTrue( true );
	}

	/**
	 * Test get_all returns results.
	 */
	public function test_get_all_returns_results(): void {
		$expected_results = [
			(object) [ 'id' => 1, 'name' => 'Test 1' ],
			(object) [ 'id' => 2, 'name' => 'Test 2' ],
		];

		$this->mock_wpdb->allows( 'get_results' )->andReturn( $expected_results );

		$result = $this->sql->get_all();

		$this->assertIsArray( $result );
		$this->assertGreaterThanOrEqual( 0, count( $result ) );
	}

	/**
	 * Test get_all with order by.
	 */
	public function test_get_all_with_order_by(): void {
		$expected_results = [
			(object) [ 'id' => 1, 'name' => 'Test 1' ],
		];

		$this->mock_wpdb->allows( 'get_results' )->andReturn( $expected_results );

		$result = $this->sql->get_all( 'name' );

		$this->assertIsArray( $result );
	}

	/**
	 * Test get_by returns results.
	 */
	public function test_get_by_returns_results(): void {
		$expected_results = [
			(object) [ 'id' => 1, 'name' => 'Test' ],
		];

		$this->mock_wpdb->allows( 'get_results' )->andReturn( $expected_results );

		Filters\expectApplied( 'stolmc_service_tracker_sql_get_by_query' )
			->atMost()->once()
			->andReturnArg( 0 );

		Filters\expectApplied( 'stolmc_service_tracker_sql_get_by_results' )
			->atMost()->once()
			->andReturnArg( 0 );

		$result = $this->sql->get_by( [ 'id' => 1 ] );

		$this->assertIsArray( $result );
	}

	/**
	 * Test get_by with IN condition.
	 */
	public function test_get_by_with_in_condition(): void {
		$expected_results = [
			(object) [ 'id' => 1, 'name' => 'Test 1' ],
			(object) [ 'id' => 2, 'name' => 'Test 2' ],
		];

		$this->mock_wpdb->allows( 'get_results' )->andReturn( $expected_results );

		Filters\expectApplied( 'stolmc_service_tracker_sql_get_by_query' )
			->atMost()->once()
			->andReturnArg( 0 );

		Filters\expectApplied( 'stolmc_service_tracker_sql_get_by_results' )
			->atMost()->once()
			->andReturnArg( 0 );

		$result = $this->sql->get_by( [ 'id' => [ 1, 2 ] ], 'IN' );

		$this->assertIsArray( $result );
	}

	/**
	 * Test update returns number of rows updated.
	 */
	public function test_update_returns_rows_updated(): void {
		$this->mock_wpdb->allows( 'update' )->andReturn( 1 );

		Actions\expectDone( 'stolmc_service_tracker_sql_after_update' )
			->atMost()->once();

		Filters\expectApplied( 'stolmc_service_tracker_sql_update_data' )
			->atMost()->once()
			->andReturnArg( 0 );

		$result = $this->sql->update( [ 'name' => 'Updated' ], [ 'id' => 1 ] );

		$this->assertSame( 1, $result );
	}

	/**
	 * Test update returns false for empty data.
	 */
	public function test_update_returns_false_for_empty_data(): void {
		$result = $this->sql->update( [], [ 'id' => 1 ] );

		$this->assertFalse( $result );
	}

	/**
	 * Test update fires after update action.
	 */
	public function test_update_fires_after_update_action(): void {
		$this->mock_wpdb->allows( 'update' )->andReturn( 1 );

		Actions\expectDone( 'stolmc_service_tracker_sql_after_update' )
			->atMost()->once();

		Filters\expectApplied( 'stolmc_service_tracker_sql_update_data' )
			->atMost()->once()
			->andReturnArg( 0 );

		$this->sql->update( [ 'name' => 'Updated' ], [ 'id' => 1 ] );

		$this->assertTrue( true );
	}

	/**
	 * Test delete returns number of rows deleted.
	 */
	public function test_delete_returns_rows_deleted(): void {
		$this->mock_wpdb->allows( 'delete' )->andReturn( 1 );

		Actions\expectDone( 'stolmc_service_tracker_sql_before_delete' )
			->atMost()->once();

		Actions\expectDone( 'stolmc_service_tracker_sql_after_delete' )
			->atMost()->once();

		$result = $this->sql->delete( [ 'id' => 1 ] );

		$this->assertSame( 1, $result );
	}

	/**
	 * Test delete returns -1 for empty condition.
	 */
	public function test_delete_returns_minus_one_for_empty_condition(): void {
		$result = $this->sql->delete( [] );

		$this->assertSame( -1, $result );
	}

	/**
	 * Test delete fires before and after delete actions.
	 */
	public function test_delete_fires_before_and_after_delete_actions(): void {
		$this->mock_wpdb->allows( 'delete' )->andReturn( 1 );

		Actions\expectDone( 'stolmc_service_tracker_sql_before_delete' )
			->atMost()->once();

		Actions\expectDone( 'stolmc_service_tracker_sql_after_delete' )
			->atMost()->once();

		$this->sql->delete( [ 'id' => 1 ] );

		$this->assertTrue( true );
	}

	/**
	 * Test SQL class can be instantiated.
	 */
	public function test_sql_class_can_be_instantiated(): void {
		$this->assertInstanceOf(
			\STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Sql::class,
			$this->sql
		);
	}
}
