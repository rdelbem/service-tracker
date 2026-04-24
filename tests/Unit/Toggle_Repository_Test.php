<?php
/**
 * Toggle Repository Test
 *
 * Tests for the STOLMC_Service_Tracker_Toggle_Repository class.
 *
 * @package Service_Tracker
 */

namespace STOLMC_Service_Tracker\Tests\Unit;

use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Mockery;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Case_Dto;

/**
 * Toggle Repository Test Class.
 *
 * @group   unit
 * @group   repository
 * @group   toggle
 */
class Toggle_Repository_Test extends Unit_TestCase {

	/**
	 * Toggle Repository instance.
	 *
	 * @var \STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Toggle_Repository
	 */
	protected $toggle_repository;

	/**
	 * Mock cases SQL handler.
	 *
	 * @var \Mockery\MockInterface
	 */
	protected $mock_cases_sql;

	/**
	 * Mock Cases Repository.
	 *
	 * @var \Mockery\MockInterface
	 */
	protected $mock_cases_repository;

	/**
	 * Mock global $wpdb object.
	 *
	 * @var \Mockery\MockInterface
	 */
	protected $mock_wpdb;

	/**
	 * Test case ID.
	 *
	 * @var int
	 */
	protected const TEST_CASE_ID = 100;

	/**
	 * Test user ID.
	 *
	 * @var int
	 */
	protected const TEST_USER_ID = 1;

	/**
	 * Set up test fixtures.
	 */
	protected function set_up(): void {
		parent::set_up();

		// Create mock $wpdb.
		$this->mock_wpdb = Mockery::mock();
		$this->mock_wpdb->prefix = 'wp_';
		$this->mock_wpdb->insert_id = self::TEST_CASE_ID;
		$GLOBALS['wpdb'] = $this->mock_wpdb;

		// Create mock SQL handler and Cases Repository.
		$this->mock_cases_sql = Mockery::mock( \STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Sql::class );
		$this->mock_cases_repository = Mockery::mock( \STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Cases_Repository::class );

		// Create the Toggle Repository instance.
		$this->toggle_repository = new \STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Toggle_Repository();

		// Use the existing helper function to set private properties for testing.
		set_private_property( $this->toggle_repository, 'cases_sql', $this->mock_cases_sql );
		set_private_property( $this->toggle_repository, 'cases_repository', $this->mock_cases_repository );
	}

	/**
	 * Test constructor initializes SQL handler and Cases Repository.
	 */
	public function test_constructor_initializes_sql_handler_and_cases_repository(): void {
		// Create a fresh instance to test constructor.
		$toggle_repository = new \STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Toggle_Repository();

		$this->assertInstanceOf(
			\STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Toggle_Repository::class,
			$toggle_repository
		);
	}

	/**
	 * Test find_by_id delegates to cases repository.
	 */
	public function test_find_by_id_delegates_to_cases_repository(): void {
		$case_id = self::TEST_CASE_ID;
		$expected_case = new STOLMC_Service_Tracker_Case_Dto(
			$case_id,
			self::TEST_USER_ID,
			'Test Case',
			'open',
			'Test Description'
		);

		$this->mock_cases_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( $case_id )
			->andReturn( $expected_case );

		$result = $this->toggle_repository->find_by_id( $case_id );

		$this->assertSame( $expected_case, $result );
	}

	/**
	 * Test find_by_id returns null when cases repository returns null.
	 */
	public function test_find_by_id_returns_null_when_cases_repository_returns_null(): void {
		$case_id = self::TEST_CASE_ID;

		$this->mock_cases_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( $case_id )
			->andReturn( null );

		$result = $this->toggle_repository->find_by_id( $case_id );

		$this->assertNull( $result );
	}

	/**
	 * Test toggle_status toggles from open to close.
	 */
	public function test_toggle_status_toggles_from_open_to_close(): void {
		$case_id = self::TEST_CASE_ID;
		$case = new STOLMC_Service_Tracker_Case_Dto(
			$case_id,
			self::TEST_USER_ID,
			'Test Case',
			'open',
			'Test Description'
		);

		$this->mock_cases_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( $case_id )
			->andReturn( $case );

		$this->mock_cases_sql->shouldReceive( 'update' )
			->once()
			->with( [ 'status' => 'close' ], [ 'id' => $case_id ] )
			->andReturn( 1 );

		$result = $this->toggle_repository->toggle_status( $case_id );

		$this->assertSame( 1, $result );
	}

	/**
	 * Test toggle_status toggles from close to open.
	 */
	public function test_toggle_status_toggles_from_close_to_open(): void {
		$case_id = self::TEST_CASE_ID;
		$case = new STOLMC_Service_Tracker_Case_Dto(
			$case_id,
			self::TEST_USER_ID,
			'Test Case',
			'close',
			'Test Description'
		);

		$this->mock_cases_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( $case_id )
			->andReturn( $case );

		$this->mock_cases_sql->shouldReceive( 'update' )
			->once()
			->with( [ 'status' => 'open' ], [ 'id' => $case_id ] )
			->andReturn( 1 );

		$result = $this->toggle_repository->toggle_status( $case_id );

		$this->assertSame( 1, $result );
	}

	/**
	 * Test toggle_status returns false when case not found.
	 */
	public function test_toggle_status_returns_false_when_case_not_found(): void {
		$case_id = self::TEST_CASE_ID;

		$this->mock_cases_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( $case_id )
			->andReturn( null );

		$result = $this->toggle_repository->toggle_status( $case_id );

		$this->assertFalse( $result );
	}

	/**
	 * Test toggle_status returns null for invalid status.
	 */
	public function test_toggle_status_returns_null_for_invalid_status(): void {
		$case_id = self::TEST_CASE_ID;
		$case = new STOLMC_Service_Tracker_Case_Dto(
			$case_id,
			self::TEST_USER_ID,
			'Test Case',
			'invalid_status',
			'Test Description'
		);

		$this->mock_cases_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( $case_id )
			->andReturn( $case );

		$result = $this->toggle_repository->toggle_status( $case_id );

		$this->assertNull( $result );
	}

	/**
	 * Test close_case closes an open case.
	 */
	public function test_close_case_closes_an_open_case(): void {
		$case_id = self::TEST_CASE_ID;
		$case = new STOLMC_Service_Tracker_Case_Dto(
			$case_id,
			self::TEST_USER_ID,
			'Test Case',
			'open',
			'Test Description'
		);

		$this->mock_cases_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( $case_id )
			->andReturn( $case );

		$this->mock_cases_sql->shouldReceive( 'update' )
			->once()
			->with( [ 'status' => 'close' ], [ 'id' => $case_id ] )
			->andReturn( 1 );

		$result = $this->toggle_repository->close_case( $case_id );

		$this->assertSame( 1, $result );
	}

	/**
	 * Test close_case returns false when case not found.
	 */
	public function test_close_case_returns_false_when_case_not_found(): void {
		$case_id = self::TEST_CASE_ID;

		$this->mock_cases_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( $case_id )
			->andReturn( null );

		$result = $this->toggle_repository->close_case( $case_id );

		$this->assertFalse( $result );
	}

	/**
	 * Test close_case returns false when case already closed.
	 */
	public function test_close_case_returns_false_when_case_already_closed(): void {
		$case_id = self::TEST_CASE_ID;
		$case = new STOLMC_Service_Tracker_Case_Dto(
			$case_id,
			self::TEST_USER_ID,
			'Test Case',
			'close',
			'Test Description'
		);

		$this->mock_cases_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( $case_id )
			->andReturn( $case );

		$result = $this->toggle_repository->close_case( $case_id );

		$this->assertFalse( $result );
	}

	/**
	 * Test open_case opens a closed case.
	 */
	public function test_open_case_opens_a_closed_case(): void {
		$case_id = self::TEST_CASE_ID;
		$case = new STOLMC_Service_Tracker_Case_Dto(
			$case_id,
			self::TEST_USER_ID,
			'Test Case',
			'close',
			'Test Description'
		);

		$this->mock_cases_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( $case_id )
			->andReturn( $case );

		$this->mock_cases_sql->shouldReceive( 'update' )
			->once()
			->with( [ 'status' => 'open' ], [ 'id' => $case_id ] )
			->andReturn( 1 );

		$result = $this->toggle_repository->open_case( $case_id );

		$this->assertSame( 1, $result );
	}

	/**
	 * Test open_case returns false when case not found.
	 */
	public function test_open_case_returns_false_when_case_not_found(): void {
		$case_id = self::TEST_CASE_ID;

		$this->mock_cases_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( $case_id )
			->andReturn( null );

		$result = $this->toggle_repository->open_case( $case_id );

		$this->assertFalse( $result );
	}

	/**
	 * Test open_case returns false when case already open.
	 */
	public function test_open_case_returns_false_when_case_already_open(): void {
		$case_id = self::TEST_CASE_ID;
		$case = new STOLMC_Service_Tracker_Case_Dto(
			$case_id,
			self::TEST_USER_ID,
			'Test Case',
			'open',
			'Test Description'
		);

		$this->mock_cases_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( $case_id )
			->andReturn( $case );

		$result = $this->toggle_repository->open_case( $case_id );

		$this->assertFalse( $result );
	}

	/**
	 * Test can_toggle returns true for open case.
	 */
	public function test_can_toggle_returns_true_for_open_case(): void {
		$case_id = self::TEST_CASE_ID;
		$case = new STOLMC_Service_Tracker_Case_Dto(
			$case_id,
			self::TEST_USER_ID,
			'Test Case',
			'open',
			'Test Description'
		);

		$this->mock_cases_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( $case_id )
			->andReturn( $case );

		$result = $this->toggle_repository->can_toggle( $case_id );

		$this->assertTrue( $result );
	}

	/**
	 * Test can_toggle returns true for closed case.
	 */
	public function test_can_toggle_returns_true_for_closed_case(): void {
		$case_id = self::TEST_CASE_ID;
		$case = new STOLMC_Service_Tracker_Case_Dto(
			$case_id,
			self::TEST_USER_ID,
			'Test Case',
			'close',
			'Test Description'
		);

		$this->mock_cases_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( $case_id )
			->andReturn( $case );

		$result = $this->toggle_repository->can_toggle( $case_id );

		$this->assertTrue( $result );
	}

	/**
	 * Test can_toggle returns false when case not found.
	 */
	public function test_can_toggle_returns_false_when_case_not_found(): void {
		$case_id = self::TEST_CASE_ID;

		$this->mock_cases_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( $case_id )
			->andReturn( null );

		$result = $this->toggle_repository->can_toggle( $case_id );

		$this->assertFalse( $result );
	}

	/**
	 * Test can_toggle returns false for invalid status.
	 */
	public function test_can_toggle_returns_false_for_invalid_status(): void {
		$case_id = self::TEST_CASE_ID;
		$case = new STOLMC_Service_Tracker_Case_Dto(
			$case_id,
			self::TEST_USER_ID,
			'Test Case',
			'invalid_status',
			'Test Description'
		);

		$this->mock_cases_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( $case_id )
			->andReturn( $case );

		$result = $this->toggle_repository->can_toggle( $case_id );

		$this->assertFalse( $result );
	}

	/**
	 * Test get_status returns case status.
	 */
	public function test_get_status_returns_case_status(): void {
		$case_id = self::TEST_CASE_ID;
		$case = new STOLMC_Service_Tracker_Case_Dto(
			$case_id,
			self::TEST_USER_ID,
			'Test Case',
			'open',
			'Test Description'
		);

		$this->mock_cases_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( $case_id )
			->andReturn( $case );

		$result = $this->toggle_repository->get_status( $case_id );

		$this->assertSame( 'open', $result );
	}

	/**
	 * Test get_status returns null when case not found.
	 */
	public function test_get_status_returns_null_when_case_not_found(): void {
		$case_id = self::TEST_CASE_ID;

		$this->mock_cases_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( $case_id )
			->andReturn( null );

		$result = $this->toggle_repository->get_status( $case_id );

		$this->assertNull( $result );
	}

	/**
	 * Test get_by_id alias delegates to find_by_id.
	 */
	public function test_get_by_id_alias_delegates_to_find_by_id(): void {
		$case_id = self::TEST_CASE_ID;
		$expected_case = new STOLMC_Service_Tracker_Case_Dto(
			$case_id,
			self::TEST_USER_ID,
			'Test Case',
			'open',
			'Test Description'
		);

		$this->mock_cases_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( $case_id )
			->andReturn( $expected_case );

		$result = $this->toggle_repository->get_by_id( $case_id );

		$this->assertSame( $expected_case, $result );
	}

	/**
	 * Test toggle alias delegates to toggle_status.
	 */
	public function test_toggle_alias_delegates_to_toggle_status(): void {
		$case_id = self::TEST_CASE_ID;
		$case = new STOLMC_Service_Tracker_Case_Dto(
			$case_id,
			self::TEST_USER_ID,
			'Test Case',
			'open',
			'Test Description'
		);

		$this->mock_cases_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( $case_id )
			->andReturn( $case );

		$this->mock_cases_sql->shouldReceive( 'update' )
			->once()
			->with( [ 'status' => 'close' ], [ 'id' => $case_id ] )
			->andReturn( 1 );

		$result = $this->toggle_repository->toggle( $case_id );

		$this->assertSame( 1, $result );
	}
}
