<?php
/**
 * Case Progress Repository Test
 *
 * Tests for the STOLMC_Service_Tracker_Case_Progress_Repository class.
 *
 * @package Service_Tracker
 */

namespace STOLMC_Service_Tracker\Tests\Unit;

use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Mockery;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Progress_Dto;

/**
 * Case Progress Repository Test Class.
 *
 * @group   unit
 * @group   repository
 * @group   case_progress
 */
class Case_Progress_Repository_Test extends Unit_TestCase {

	/**
	 * Case Progress Repository instance.
	 *
	 * @var \STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Case_Progress_Repository
	 */
	protected $case_progress_orm;

	/**
	 * Mock Progress Repository dependency.
	 *
	 * @var \Mockery\MockInterface
	 */
	protected $mock_progress_orm;

	/**
	 * Test case ID.
	 *
	 * @var int
	 */
	protected const TEST_CASE_ID = 100;

	/**
	 * Test progress ID.
	 *
	 * @var int
	 */
	protected const TEST_PROGRESS_ID = 200;

	/**
	 * Set up test fixtures.
	 */
	protected function set_up(): void {
		parent::set_up();

		// Create mock Progress Repository.
		$this->mock_progress_orm = Mockery::mock( \STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Progress_Repository::class );

		// Create the Case Progress Repository instance with test case ID.
		$this->case_progress_orm = new \STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Case_Progress_Repository(
			self::TEST_CASE_ID,
			$this->mock_progress_orm
		);
	}

	/**
	 * Test constructor sets case ID and progress Repository.
	 */
	public function test_constructor_sets_case_id_and_progress_orm(): void {
		$case_id = 123;
		$progress_orm = Mockery::mock( \STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Progress_Repository::class );

		$case_progress_orm = new \STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Case_Progress_Repository(
			$case_id,
			$progress_orm
		);

		$this->assertInstanceOf(
			\STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Case_Progress_Repository::class,
			$case_progress_orm
		);
	}

	/**
	 * Test read_all returns progress entries for bound case.
	 */
	public function test_read_all_returns_progress_entries_for_bound_case(): void {
		$expected_progress = [
			(object) [
				'id'      => self::TEST_PROGRESS_ID,
				'id_case' => self::TEST_CASE_ID,
				'text'    => 'Progress update 1',
				'created_at' => '2024-01-01 10:00:00',
			],
			(object) [
				'id'      => self::TEST_PROGRESS_ID + 1,
				'id_case' => self::TEST_CASE_ID,
				'text'    => 'Progress update 2',
				'created_at' => '2024-01-02 11:00:00',
			],
		];

		$this->mock_progress_orm->shouldReceive( 'find_by_case_id' )
			->once()
			->with( self::TEST_CASE_ID )
			->andReturn( $expected_progress );

		$result = $this->case_progress_orm->read_all();

		$this->assertSame( $expected_progress, $result );
	}

	/**
	 * Test read_all returns null when no progress entries.
	 */
	public function test_read_all_returns_null_when_no_progress_entries(): void {
		$this->mock_progress_orm->shouldReceive( 'find_by_case_id' )
			->once()
			->with( self::TEST_CASE_ID )
			->andReturn( null );

		$result = $this->case_progress_orm->read_all();

		$this->assertNull( $result );
	}

	/**
	 * Test create adds case ID to data and calls progress Repository insert.
	 */
	public function test_create_adds_case_id_to_data_and_calls_progress_orm_insert(): void {
		$progress_data = [
			'id_user' => 1,
			'text'    => 'Test progress update',
		];

		$expected_data = $progress_data;
		$expected_data['id_case'] = self::TEST_CASE_ID;

		$this->mock_progress_orm->shouldReceive( 'create' )
			->once()
			->with( $expected_data )
			->andReturn( 'Success, data was inserted' );

		$result = $this->case_progress_orm->create( $progress_data );

		$this->assertSame( 'Success, data was inserted', $result );
	}

	/**
	 * Test create returns false when progress Repository insert fails.
	 */
	public function test_create_returns_false_when_progress_orm_insert_fails(): void {
		$progress_data = [
			'id_user' => 1,
			'text'    => 'Test progress update',
		];

		$expected_data = $progress_data;
		$expected_data['id_case'] = self::TEST_CASE_ID;

		$this->mock_progress_orm->shouldReceive( 'create' )
			->once()
			->with( $expected_data )
			->andReturn( false );

		$result = $this->case_progress_orm->create( $progress_data );

		$this->assertFalse( $result );
	}

	/**
	 * Test update_by_id calls progress Repository update_by_id_for_case with correct parameters.
	 */
	public function test_update_by_id_calls_progress_orm_update_by_id_for_case_with_correct_parameters(): void {
		$progress_id = self::TEST_PROGRESS_ID;
		$update_data = [
			'text' => 'Updated progress text',
		];

		$this->mock_progress_orm->shouldReceive( 'update_by_id_for_case' )
			->once()
			->with( $progress_id, self::TEST_CASE_ID, $update_data )
			->andReturn( 1 );

		$result = $this->case_progress_orm->update_by_id( $progress_id, $update_data );

		$this->assertSame( 1, $result );
	}

	/**
	 * Test update_by_id returns false when progress Repository update fails.
	 */
	public function test_update_by_id_returns_false_when_progress_orm_update_fails(): void {
		$progress_id = self::TEST_PROGRESS_ID;
		$update_data = [
			'text' => 'Updated progress text',
		];

		$this->mock_progress_orm->shouldReceive( 'update_by_id_for_case' )
			->once()
			->with( $progress_id, self::TEST_CASE_ID, $update_data )
			->andReturn( false );

		$result = $this->case_progress_orm->update_by_id( $progress_id, $update_data );

		$this->assertFalse( $result );
	}

	/**
	 * Test delete_by_id calls progress Repository delete_by_id_for_case with correct parameters.
	 */
	public function test_delete_by_id_calls_progress_orm_delete_by_id_for_case_with_correct_parameters(): void {
		$progress_id = self::TEST_PROGRESS_ID;

		$this->mock_progress_orm->shouldReceive( 'delete_by_id_for_case' )
			->once()
			->with( $progress_id, self::TEST_CASE_ID )
			->andReturn( 1 );

		$result = $this->case_progress_orm->delete_by_id( $progress_id );

		$this->assertSame( 1, $result );
	}

	/**
	 * Test delete_by_id returns false when progress Repository delete fails.
	 */
	public function test_delete_by_id_returns_false_when_progress_orm_delete_fails(): void {
		$progress_id = self::TEST_PROGRESS_ID;

		$this->mock_progress_orm->shouldReceive( 'delete_by_id_for_case' )
			->once()
			->with( $progress_id, self::TEST_CASE_ID )
			->andReturn( false );

		$result = $this->case_progress_orm->delete_by_id( $progress_id );

		$this->assertFalse( $result );
	}

	/**
	 * Test delete_all calls progress Repository delete_by_case_id with correct case ID.
	 */
	public function test_delete_all_calls_progress_orm_delete_by_case_id_with_correct_case_id(): void {
		$this->mock_progress_orm->shouldReceive( 'delete_by_case_id' )
			->once()
			->with( self::TEST_CASE_ID )
			->andReturn( 2 );

		$result = $this->case_progress_orm->delete_all();

		$this->assertSame( 2, $result );
	}

	/**
	 * Test delete_all returns false when progress Repository delete fails.
	 */
	public function test_delete_all_returns_false_when_progress_orm_delete_fails(): void {
		$this->mock_progress_orm->shouldReceive( 'delete_by_case_id' )
			->once()
			->with( self::TEST_CASE_ID )
			->andReturn( false );

		$result = $this->case_progress_orm->delete_all();

		$this->assertFalse( $result );
	}

	/**
	 * Test create handles empty data array.
	 */
	public function test_create_handles_empty_data_array(): void {
		$progress_data = [];

		$expected_data = [ 'id_case' => self::TEST_CASE_ID ];

		$this->mock_progress_orm->shouldReceive( 'create' )
			->once()
			->with( $expected_data )
			->andReturn( 'Success, data was inserted' );

		$result = $this->case_progress_orm->create( $progress_data );

		$this->assertSame( 'Success, data was inserted', $result );
	}

	/**
	 * Test update_by_id handles empty data array.
	 */
	public function test_update_by_id_handles_empty_data_array(): void {
		$progress_id = self::TEST_PROGRESS_ID;
		$update_data = [];

		$this->mock_progress_orm->shouldReceive( 'update_by_id_for_case' )
			->once()
			->with( $progress_id, self::TEST_CASE_ID, $update_data )
			->andReturn( 0 );

		$result = $this->case_progress_orm->update_by_id( $progress_id, $update_data );

		$this->assertSame( 0, $result );
	}

	/**
	 * Test read_all returns object when single progress entry.
	 */
	public function test_read_all_returns_object_when_single_progress_entry(): void {
		$expected_progress = new STOLMC_Service_Tracker_Progress_Dto(
			self::TEST_PROGRESS_ID,
			1,
			self::TEST_CASE_ID,
			'Single progress update',
			null,
			'2024-01-01 10:00:00'
		);

		$this->mock_progress_orm->shouldReceive( 'find_by_case_id' )
			->once()
			->with( self::TEST_CASE_ID )
			->andReturn( $expected_progress );

		$result = $this->case_progress_orm->read_all();

		$this->assertSame( $expected_progress, $result );
	}
}
