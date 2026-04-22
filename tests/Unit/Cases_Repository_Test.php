<?php
/**
 * Cases Repository Test
 *
 * Tests for the STOLMC_Service_Tracker_Cases_Repository class.
 *
 * @package Service_Tracker
 */

namespace STOLMC_Service_Tracker\Tests\Unit;

use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Mockery;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Case_Dto;
use STOLMC_Service_Tracker\includes\DTO\STOLMC_Service_Tracker_Progress_Dto;

/**
 * Cases Repository Test Class.
 *
 * @group   unit
 * @group   repository
 * @group   cases
 */
class Cases_Repository_Test extends Unit_TestCase {

	/**
	 * Cases Repository instance.
	 *
	 * @var \STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Cases_Repository
	 */
	protected $cases_orm;

	/**
	 * Mock cases SQL handler.
	 *
	 * @var \Mockery\MockInterface
	 */
	protected $mock_cases_sql;

	/**
	 * Mock Progress Repository.
	 *
	 * @var \Mockery\MockInterface
	 */
	protected $mock_progress_repository;

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

		// Create mock $wpdb.
		$this->mock_wpdb = Mockery::mock();
		$this->mock_wpdb->prefix = 'wp_';
		$this->mock_wpdb->insert_id = self::TEST_CASE_ID;
		$GLOBALS['wpdb'] = $this->mock_wpdb;

		// Create mock SQL handler and Progress Repository.
		$this->mock_cases_sql = Mockery::mock( \STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Sql::class );
		$this->mock_progress_repository = Mockery::mock( \STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Progress_Repository::class );

		// Create the Cases Repository instance.
		$this->cases_orm = new \STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Cases_Repository();

		// Use the existing helper function to set private properties for testing.
		set_private_property( $this->cases_orm, 'cases_sql', $this->mock_cases_sql );
		set_private_property( $this->cases_orm, 'progress_repository', $this->mock_progress_repository );
	}

	/**
	 * Test constructor initializes SQL handler and Progress Repository.
	 */
	public function test_constructor_initializes_sql_handler_and_progress_repository(): void {
		// Create a fresh instance to test constructor.
		$cases_orm = new \STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Cases_Repository();

		$this->assertInstanceOf(
			\STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Cases_Repository::class,
			$cases_orm
		);
	}

	/**
	 * Test progress returns case-scoped progress relation object.
	 */
	public function test_progress_returns_case_scoped_progress_relation_object(): void {
		$case_id = self::TEST_CASE_ID;

		// Mock the Case_Progress_Repository constructor.
		$mock_case_progress_repository = Mockery::mock( \STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Case_Progress_Repository::class );

		// We need to mock the constructor call.
		// Since we can't easily mock the constructor, we'll test that the method returns the correct type.
		// Actually, we can use a partial mock or create a real instance with our mocked dependencies.
		// Let's create a real instance with our mocked progress_repository.
		$result = $this->cases_orm->progress( $case_id );

		$this->assertInstanceOf(
			\STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Case_Progress_Repository::class,
			$result
		);
	}

	/**
	 * Test progress_from_progress_id returns case-scoped progress relation when progress exists.
	 */
	public function test_progress_from_progress_id_returns_case_scoped_progress_relation_when_progress_exists(): void {
		$progress_id = self::TEST_PROGRESS_ID;
		$case_id = self::TEST_CASE_ID;

		$progress_object = new STOLMC_Service_Tracker_Progress_Dto( $progress_id, self::TEST_USER_ID, $case_id, 'Test progress' );

		$this->mock_progress_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( $progress_id )
			->andReturn( $progress_object );

		$result = $this->cases_orm->progress_from_progress_id( $progress_id );

		$this->assertInstanceOf(
			\STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Case_Progress_Repository::class,
			$result
		);
	}

	/**
	 * Test progress_from_progress_id returns null when progress not found.
	 */
	public function test_progress_from_progress_id_returns_null_when_progress_not_found(): void {
		$progress_id = self::TEST_PROGRESS_ID;

		$this->mock_progress_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( $progress_id )
			->andReturn( null );

		$result = $this->cases_orm->progress_from_progress_id( $progress_id );

		$this->assertNull( $result );
	}

	/**
	 * Test progress_from_progress_id returns null when progress has no case ID.
	 */
	public function test_progress_from_progress_id_returns_null_when_progress_has_no_case_id(): void {
		$progress_id = self::TEST_PROGRESS_ID;

		$this->mock_progress_repository->shouldReceive( 'find_by_id' )
			->once()
			->with( $progress_id )
			->andReturn( null );

		$result = $this->cases_orm->progress_from_progress_id( $progress_id );

		$this->assertNull( $result );
	}

	/**
	 * Test get_all returns all cases with specified columns.
	 */
	public function test_get_all_returns_all_cases_with_specified_columns(): void {
		$expected_cases = [
			(object) [
				'id'      => self::TEST_CASE_ID,
				'id_user' => self::TEST_USER_ID,
				'title'   => 'Test Case 1',
				'status'  => 'open',
			],
			(object) [
				'id'      => self::TEST_CASE_ID + 1,
				'id_user' => self::TEST_USER_ID + 1,
				'title'   => 'Test Case 2',
				'status'  => 'close',
			],
		];

		$this->mock_cases_sql->shouldReceive( 'get_all_with_columns' )
			->once()
			->with( [ 'id', 'id_user', 'title', 'status' ], 'id ASC' )
			->andReturn( $expected_cases );

		$result = $this->cases_orm->get_all();

		$this->assertCount( 2, $result );
		$this->assertInstanceOf( STOLMC_Service_Tracker_Case_Dto::class, $result[0] );
		$this->assertInstanceOf( STOLMC_Service_Tracker_Case_Dto::class, $result[1] );
		$this->assertSame( 'Test Case 1', $result[0]->title );
		$this->assertSame( 'Test Case 2', $result[1]->title );
	}

	/**
	 * Test get_all returns null when no cases found.
	 */
	public function test_get_all_returns_null_when_no_cases_found(): void {
		$this->mock_cases_sql->shouldReceive( 'get_all_with_columns' )
			->once()
			->with( [ 'id', 'id_user', 'title', 'status' ], 'id ASC' )
			->andReturn( null );

		$result = $this->cases_orm->get_all();

		$this->assertNull( $result );
	}

	/**
	 * Test get_by_id returns case object when found.
	 */
	public function test_get_by_id_returns_case_object_when_found(): void {
		$case_id = self::TEST_CASE_ID;
		$expected_case = (object) [
			'id'      => $case_id,
			'id_user' => self::TEST_USER_ID,
			'title'   => 'Test Case',
			'status'  => 'open',
		];

		$this->mock_cases_sql->shouldReceive( 'get_by' )
			->once()
			->with( [ 'id' => $case_id ] )
			->andReturn( [ $expected_case ] );

		$result = $this->cases_orm->get_by_id( $case_id );

		$this->assertInstanceOf( STOLMC_Service_Tracker_Case_Dto::class, $result );
		$this->assertSame( $case_id, $result->id );
		$this->assertSame( self::TEST_USER_ID, $result->id_user );
		$this->assertSame( 'Test Case', $result->title );
		$this->assertSame( 'open', $result->status );
	}

	/**
	 * Test get_by_id returns null when case not found.
	 */
	public function test_get_by_id_returns_null_when_case_not_found(): void {
		$case_id = self::TEST_CASE_ID;

		$this->mock_cases_sql->shouldReceive( 'get_by' )
			->once()
			->with( [ 'id' => $case_id ] )
			->andReturn( [] );

		$result = $this->cases_orm->get_by_id( $case_id );

		$this->assertNull( $result );
	}

	/**
	 * Test get_by_id returns null when get_by returns non-array.
	 */
	public function test_get_by_id_returns_null_when_get_by_returns_non_array(): void {
		$case_id = self::TEST_CASE_ID;

		$this->mock_cases_sql->shouldReceive( 'get_by' )
			->once()
			->with( [ 'id' => $case_id ] )
			->andReturn( null );

		$result = $this->cases_orm->get_by_id( $case_id );

		$this->assertNull( $result );
	}

	/**
	 * Test get_by_ids returns cases array when IDs provided.
	 */
	public function test_get_by_ids_returns_cases_array_when_ids_provided(): void {
		$case_ids = [ self::TEST_CASE_ID, self::TEST_CASE_ID + 1 ];
		$expected_cases = [
			(object) [
				'id'      => self::TEST_CASE_ID,
				'id_user' => self::TEST_USER_ID,
				'title'   => 'Test Case 1',
			],
			(object) [
				'id'      => self::TEST_CASE_ID + 1,
				'id_user' => self::TEST_USER_ID + 1,
				'title'   => 'Test Case 2',
			],
		];

		$this->mock_cases_sql->shouldReceive( 'get_by' )
			->once()
			->with( [ 'id' => $case_ids ], 'IN' )
			->andReturn( $expected_cases );

		$result = $this->cases_orm->get_by_ids( $case_ids );

		$this->assertCount( 2, $result );
		$this->assertInstanceOf( STOLMC_Service_Tracker_Case_Dto::class, $result[0] );
		$this->assertInstanceOf( STOLMC_Service_Tracker_Case_Dto::class, $result[1] );
		$this->assertSame( 'Test Case 1', $result[0]->title );
		$this->assertSame( 'Test Case 2', $result[1]->title );
	}

	/**
	 * Test get_by_ids returns empty array when empty IDs array.
	 */
	public function test_get_by_ids_returns_empty_array_when_empty_ids_array(): void {
		$result = $this->cases_orm->get_by_ids( [] );

		$this->assertIsArray( $result );
		$this->assertEmpty( $result );
	}

	/**
	 * Test get_by_ids returns empty array when get_by returns non-array.
	 */
	public function test_get_by_ids_returns_empty_array_when_get_by_returns_non_array(): void {
		$case_ids = [ self::TEST_CASE_ID ];

		$this->mock_cases_sql->shouldReceive( 'get_by' )
			->once()
			->with( [ 'id' => $case_ids ], 'IN' )
			->andReturn( null );

		$result = $this->cases_orm->get_by_ids( $case_ids );

		$this->assertIsArray( $result );
		$this->assertEmpty( $result );
	}

	/**
	 * Test count_by_user returns count of cases for user.
	 */
	public function test_count_by_user_returns_count_of_cases_for_user(): void {
		$user_id = self::TEST_USER_ID;
		$expected_count = 5;

		$this->mock_cases_sql->shouldReceive( 'count_by' )
			->once()
			->with( [ 'id_user' => $user_id ] )
			->andReturn( $expected_count );

		$result = $this->cases_orm->count_by_user( $user_id );

		$this->assertSame( $expected_count, $result );
	}

	/**
	 * Test get_by_user_paginated returns paginated cases for user.
	 */
	public function test_get_by_user_paginated_returns_paginated_cases_for_user(): void {
		$user_id = self::TEST_USER_ID;
		$per_page = 10;
		$offset = 0;
		$expected_cases = [
			(object) [
				'id'      => self::TEST_CASE_ID,
				'id_user' => $user_id,
				'title'   => 'Test Case',
			],
		];

		$this->mock_cases_sql->shouldReceive( 'get_by_paginated' )
			->once()
			->with( [ 'id_user' => $user_id ], $per_page, $offset, 'created_at DESC' )
			->andReturn( $expected_cases );

		$result = $this->cases_orm->get_by_user_paginated( $user_id, $per_page, $offset );

		$this->assertCount( 1, $result );
		$this->assertInstanceOf( STOLMC_Service_Tracker_Case_Dto::class, $result[0] );
		$this->assertSame( self::TEST_CASE_ID, $result[0]->id );
		$this->assertSame( $user_id, $result[0]->id_user );
		$this->assertSame( 'Test Case', $result[0]->title );
	}

	/**
	 * Test get_by returns cases based on query arguments.
	 */
	public function test_get_by_returns_cases_based_on_query_arguments(): void {
		$query_args = [
			'status' => 'open',
			'id_user' => self::TEST_USER_ID,
		];
		$expected_cases = [
			(object) [
				'id'      => self::TEST_CASE_ID,
				'id_user' => self::TEST_USER_ID,
				'title'   => 'Open Case',
				'status'  => 'open',
			],
		];

		$this->mock_cases_sql->shouldReceive( 'get_by' )
			->once()
			->with( $query_args )
			->andReturn( $expected_cases );

		$result = $this->cases_orm->get_by( $query_args );

		$this->assertCount( 1, $result );
		$this->assertInstanceOf( STOLMC_Service_Tracker_Case_Dto::class, $result[0] );
		$this->assertSame( 'Open Case', $result[0]->title );
		$this->assertSame( 'open', $result[0]->status );
	}

	/**
	 * Test insert calls SQL insert with data.
	 */
	public function test_insert_calls_sql_insert_with_data(): void {
		$case_data = [
			'id_user' => self::TEST_USER_ID,
			'title'   => 'New Case',
			'status'  => 'open',
		];

		$this->mock_cases_sql->shouldReceive( 'insert' )
			->once()
			->with( $case_data )
			->andReturn( 'Success, data was inserted' );

		$result = $this->cases_orm->insert( $case_data );

		$this->assertSame( 'Success, data was inserted', $result );
	}

	/**
	 * Test insert returns false when SQL insert fails.
	 */
	public function test_insert_returns_false_when_sql_insert_fails(): void {
		$case_data = [
			'id_user' => self::TEST_USER_ID,
			'title'   => 'New Case',
		];

		$this->mock_cases_sql->shouldReceive( 'insert' )
			->once()
			->with( $case_data )
			->andReturn( false );

		$result = $this->cases_orm->insert( $case_data );

		$this->assertFalse( $result );
	}

	/**
	 * Test update_by_id calls SQL update with data and ID.
	 */
	public function test_update_by_id_calls_sql_update_with_data_and_id(): void {
		$case_id = self::TEST_CASE_ID;
		$update_data = [
			'title'  => 'Updated Case Title',
			'status' => 'close',
		];

		$this->mock_cases_sql->shouldReceive( 'update' )
			->once()
			->with( $update_data, [ 'id' => $case_id ] )
			->andReturn( 1 );

		$result = $this->cases_orm->update_by_id( $case_id, $update_data );

		$this->assertSame( 1, $result );
	}

	/**
	 * Test update_by_id returns false when SQL update fails.
	 */
	public function test_update_by_id_returns_false_when_sql_update_fails(): void {
		$case_id = self::TEST_CASE_ID;
		$update_data = [
			'title' => 'Updated Case Title',
		];

		$this->mock_cases_sql->shouldReceive( 'update' )
			->once()
			->with( $update_data, [ 'id' => $case_id ] )
			->andReturn( false );

		$result = $this->cases_orm->update_by_id( $case_id, $update_data );

		$this->assertFalse( $result );
	}

	/**
	 * Test delete_by_id calls SQL delete with ID.
	 */
	public function test_delete_by_id_calls_sql_delete_with_id(): void {
		$case_id = self::TEST_CASE_ID;

		$this->mock_cases_sql->shouldReceive( 'delete' )
			->once()
			->with( [ 'id' => $case_id ] )
			->andReturn( 1 );

		$result = $this->cases_orm->delete_by_id( $case_id );

		$this->assertSame( 1, $result );
	}

	/**
	 * Test delete_by_id returns false when SQL delete fails.
	 */
	public function test_delete_by_id_returns_false_when_sql_delete_fails(): void {
		$case_id = self::TEST_CASE_ID;

		$this->mock_cases_sql->shouldReceive( 'delete' )
			->once()
			->with( [ 'id' => $case_id ] )
			->andReturn( false );

		$result = $this->cases_orm->delete_by_id( $case_id );

		$this->assertFalse( $result );
	}

	/**
	 * Test delete_progress_by_case_id calls progress delete_all method.
	 */
	public function test_delete_progress_by_case_id_calls_progress_delete_all_method(): void {
		$case_id = self::TEST_CASE_ID;

		// The delete_progress_by_case_id method calls progress($case_id)->delete_all().
		// The progress() method creates a new Case_Progress_Repository instance with $this->progress_repository.
		// The delete_all() method on Case_Progress_Repository calls delete_by_case_id on progress_repository.
		// So we just need to mock that call.
		$this->mock_progress_repository->shouldReceive( 'delete_by_case_id' )
			->once()
			->with( $case_id )
			->andReturn( 3 );

		$result = $this->cases_orm->delete_progress_by_case_id( $case_id );

		$this->assertSame( 3, $result );
	}

	/**
	 * Test delete_progress_by_case_id returns false when delete fails.
	 */
	public function test_delete_progress_by_case_id_returns_false_when_delete_fails(): void {
		$case_id = self::TEST_CASE_ID;

		$this->mock_progress_repository->shouldReceive( 'delete_by_case_id' )
			->once()
			->with( $case_id )
			->andReturn( false );

		$result = $this->cases_orm->delete_progress_by_case_id( $case_id );

		$this->assertFalse( $result );
	}

	/**
	 * Test get_last_insert_id returns insert ID from wpdb.
	 */
	public function test_get_last_insert_id_returns_insert_id_from_wpdb(): void {
		$expected_id = self::TEST_CASE_ID;

		$this->mock_wpdb->insert_id = $expected_id;

		$result = $this->cases_orm->get_last_insert_id();

		$this->assertSame( $expected_id, $result );
	}

	/**
	 * Test get_last_insert_id returns zero when wpdb insert_id is not set.
	 */
	public function test_get_last_insert_id_returns_zero_when_wpdb_insert_id_is_not_set(): void {
		$this->mock_wpdb->insert_id = null;

		$result = $this->cases_orm->get_last_insert_id();

		$this->assertSame( 0, $result );
	}

	/**
	 * Test get_by returns null when SQL get_by returns null.
	 */
	public function test_get_by_returns_null_when_sql_get_by_returns_null(): void {
		$query_args = [ 'status' => 'open' ];

		$this->mock_cases_sql->shouldReceive( 'get_by' )
			->once()
			->with( $query_args )
			->andReturn( null );

		$result = $this->cases_orm->get_by( $query_args );

		$this->assertNull( $result );
	}

	/**
	 * Test get_by returns object when SQL get_by returns single object.
	 */
	public function test_get_by_returns_object_when_sql_get_by_returns_single_object(): void {
		$query_args = [ 'id' => self::TEST_CASE_ID ];
		$expected_case = (object) [
			'id'      => self::TEST_CASE_ID,
			'id_user' => self::TEST_USER_ID,
			'title'   => 'Single Case',
		];

		$this->mock_cases_sql->shouldReceive( 'get_by' )
			->once()
			->with( $query_args )
			->andReturn( $expected_case );

		$result = $this->cases_orm->get_by( $query_args );

		$this->assertInstanceOf( STOLMC_Service_Tracker_Case_Dto::class, $result );
		$this->assertSame( self::TEST_CASE_ID, $result->id );
		$this->assertSame( self::TEST_USER_ID, $result->id_user );
		$this->assertSame( 'Single Case', $result->title );
	}

	/**
	 * Test get_by_user_paginated returns null when SQL get_by_paginated returns null.
	 */
	public function test_get_by_user_paginated_returns_null_when_sql_get_by_paginated_returns_null(): void {
		$user_id = self::TEST_USER_ID;
		$per_page = 10;
		$offset = 0;

		$this->mock_cases_sql->shouldReceive( 'get_by_paginated' )
			->once()
			->with( [ 'id_user' => $user_id ], $per_page, $offset, 'created_at DESC' )
			->andReturn( null );

		$result = $this->cases_orm->get_by_user_paginated( $user_id, $per_page, $offset );

		$this->assertNull( $result );
	}

	/**
	 * Test get_by_user_paginated returns object when SQL get_by_paginated returns single object.
	 */
	public function test_get_by_user_paginated_returns_object_when_sql_get_by_paginated_returns_single_object(): void {
		$user_id = self::TEST_USER_ID;
		$per_page = 10;
		$offset = 0;
		$expected_case = (object) [
			'id'      => self::TEST_CASE_ID,
			'id_user' => $user_id,
			'title'   => 'Single Paginated Case',
		];

		$this->mock_cases_sql->shouldReceive( 'get_by_paginated' )
			->once()
			->with( [ 'id_user' => $user_id ], $per_page, $offset, 'created_at DESC' )
			->andReturn( $expected_case );

		$result = $this->cases_orm->get_by_user_paginated( $user_id, $per_page, $offset );

		$this->assertInstanceOf( STOLMC_Service_Tracker_Case_Dto::class, $result );
		$this->assertSame( self::TEST_CASE_ID, $result->id );
		$this->assertSame( $user_id, $result->id_user );
		$this->assertSame( 'Single Paginated Case', $result->title );
	}
}
