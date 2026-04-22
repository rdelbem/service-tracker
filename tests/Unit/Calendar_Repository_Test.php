<?php
/**
 * Calendar Repository Test
 *
 * Tests for the STOLMC_Service_Tracker_Calendar_Repository class.
 *
 * @package Service_Tracker
 */

namespace STOLMC_Service_Tracker\Tests\Unit;

use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Mockery;

/**
 * Calendar Repository Test Class.
 *
 * @group   unit
 * @group   repository
 * @group   calendar
 */
class Calendar_Repository_Test extends Unit_TestCase {

	/**
	 * Calendar Repository instance.
	 *
	 * @var \STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Calendar_Repository
	 */
	protected $calendar_orm;

	/**
	 * Mock cases SQL handler.
	 *
	 * @var \Mockery\MockInterface
	 */
	protected $mock_cases_sql;

	/**
	 * Mock progress SQL handler.
	 *
	 * @var \Mockery\MockInterface
	 */
	protected $mock_progress_sql;

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
	 * Test start date.
	 *
	 * @var string
	 */
	protected const TEST_START_DATE = '2024-01-01';

	/**
	 * Test end date.
	 *
	 * @var string
	 */
	protected const TEST_END_DATE = '2024-01-31';

	/**
	 * Set up test fixtures.
	 */
	protected function set_up(): void {
		parent::set_up();

		// Create mock SQL handlers.
		$this->mock_cases_sql = Mockery::mock( \STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Sql::class );
		$this->mock_progress_sql = Mockery::mock( \STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Sql::class );

		// Create the Calendar Repository instance.
		$this->calendar_orm = new \STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Calendar_Repository();

		// Use the existing helper function to set private properties for testing.
		set_private_property( $this->calendar_orm, 'cases_sql', $this->mock_cases_sql );
		set_private_property( $this->calendar_orm, 'progress_sql', $this->mock_progress_sql );
	}

	/**
	 * Test constructor exists and class can be instantiated.
	 */
	public function test_constructor_exists_and_class_can_be_instantiated(): void {
		// We can't test the actual constructor because it requires $wpdb.
		// But we can verify the class exists and can be instantiated with our mocked dependencies.
		$this->assertInstanceOf(
			\STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Calendar_Repository::class,
			$this->calendar_orm
		);
	}

	/**
	 * Test get_calendar_data returns complete calendar data.
	 */
	public function test_get_calendar_data_returns_complete_calendar_data(): void {
		$mock_cases = [
			(object) [
				'id'          => self::TEST_CASE_ID,
				'id_user'     => self::TEST_USER_ID,
				'title'       => 'Test Case',
				'status'      => 'open',
				'description' => 'Test Description',
				'start_at'    => '2024-01-01 09:00:00',
				'due_at'      => '2024-01-31 17:00:00',
				'created_at'  => '2024-01-01 08:00:00',
			],
		];

		$mock_progress = [
			(object) [
				'id'         => self::TEST_PROGRESS_ID,
				'id_case'    => self::TEST_CASE_ID,
				'id_user'    => self::TEST_USER_ID,
				'text'       => 'Progress update',
				'created_at' => '2024-01-15 12:00:00',
			],
		];

		// No filters provided → uses get_all() for both tables.
		$this->mock_cases_sql->shouldReceive( 'get_all' )
			->once()
			->andReturn( $mock_cases );

		$this->mock_progress_sql->shouldReceive( 'get_all' )
			->once()
			->andReturn( $mock_progress );

		// Progress loop calls get_by() to resolve case titles.
		$this->mock_cases_sql->shouldReceive( 'get_by' )
			->once()
			->with( [ 'id' => self::TEST_CASE_ID ] )
			->andReturn( [ (object) [ 'title' => 'Test Case' ] ] );

		Functions\when( 'get_user_by' )->justReturn( (object) [ 'display_name' => 'Test User' ] );

		$result = $this->calendar_orm->get_calendar_data(
			self::TEST_START_DATE,
			self::TEST_END_DATE
		);

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'cases', $result );
		$this->assertArrayHasKey( 'progress', $result );
		$this->assertArrayHasKey( 'date_index', $result );

		$this->assertIsArray( $result['cases'] );
		$this->assertIsArray( $result['progress'] );
		$this->assertIsArray( $result['date_index'] );

		// Verify cases structure.
		if ( count( $result['cases'] ) > 0 ) {
			$case = $result['cases'][0];
			$this->assertArrayHasKey( 'id', $case );
			$this->assertArrayHasKey( 'id_user', $case );
			$this->assertArrayHasKey( 'title', $case );
			$this->assertArrayHasKey( 'status', $case );
			$this->assertArrayHasKey( 'description', $case );
			$this->assertArrayHasKey( 'start_at', $case );
			$this->assertArrayHasKey( 'due_at', $case );
			$this->assertArrayHasKey( 'client_name', $case );
		}

		// Verify progress structure.
		if ( count( $result['progress'] ) > 0 ) {
			$progress = $result['progress'][0];
			$this->assertArrayHasKey( 'id', $progress );
			$this->assertArrayHasKey( 'id_case', $progress );
			$this->assertArrayHasKey( 'id_user', $progress );
			$this->assertArrayHasKey( 'text', $progress );
			$this->assertArrayHasKey( 'created_at', $progress );
			$this->assertArrayHasKey( 'case_title', $progress );
			$this->assertArrayHasKey( 'client_name', $progress );
		}
	}

	/**
	 * Test get_calendar_data filters cases by user ID.
	 */
	public function test_get_calendar_data_filters_cases_by_user_id(): void {
		$id_user = self::TEST_USER_ID;

		// With user filter → uses get_by() for cases.
		$this->mock_cases_sql->shouldReceive( 'get_by' )
			->once()
			->with( [ 'id_user' => $id_user ] )
			->andReturn( [] );

		$this->mock_progress_sql->shouldReceive( 'get_all' )
			->once()
			->andReturn( [] );

		$result = $this->calendar_orm->get_calendar_data(
			self::TEST_START_DATE,
			self::TEST_END_DATE,
			$id_user
		);

		$this->assertIsArray( $result );
		$this->assertEmpty( $result['cases'] );
		$this->assertEmpty( $result['progress'] );
	}

	/**
	 * Test get_calendar_data filters cases by status.
	 */
	public function test_get_calendar_data_filters_cases_by_status(): void {
		$status = 'open';

		// With status filter → uses get_by() for cases.
		$this->mock_cases_sql->shouldReceive( 'get_by' )
			->once()
			->with( [ 'status' => $status ] )
			->andReturn( [] );

		$this->mock_progress_sql->shouldReceive( 'get_all' )
			->once()
			->andReturn( [] );

		$result = $this->calendar_orm->get_calendar_data(
			self::TEST_START_DATE,
			self::TEST_END_DATE,
			null,
			$status
		);

		$this->assertIsArray( $result );
		$this->assertEmpty( $result['cases'] );
		$this->assertEmpty( $result['progress'] );
	}

	/**
	 * Test get_calendar_data filters cases by user ID and status.
	 */
	public function test_get_calendar_data_filters_cases_by_user_id_and_status(): void {
		$id_user = self::TEST_USER_ID;
		$status = 'open';

		// With both filters → uses get_by() for cases.
		$this->mock_cases_sql->shouldReceive( 'get_by' )
			->once()
			->with( [ 'id_user' => $id_user, 'status' => $status ] )
			->andReturn( [] );

		$this->mock_progress_sql->shouldReceive( 'get_all' )
			->once()
			->andReturn( [] );

		$result = $this->calendar_orm->get_calendar_data(
			self::TEST_START_DATE,
			self::TEST_END_DATE,
			$id_user,
			$status
		);

		$this->assertIsArray( $result );
		$this->assertEmpty( $result['cases'] );
		$this->assertEmpty( $result['progress'] );
	}

	/**
	 * Test get_calendar_data returns empty arrays when no data.
	 */
	public function test_get_calendar_data_returns_empty_arrays_when_no_data(): void {
		// No filters → uses get_all() for both tables.
		$this->mock_cases_sql->shouldReceive( 'get_all' )
			->once()
			->andReturn( [] );

		$this->mock_progress_sql->shouldReceive( 'get_all' )
			->once()
			->andReturn( [] );

		$result = $this->calendar_orm->get_calendar_data(
			self::TEST_START_DATE,
			self::TEST_END_DATE
		);

		$this->assertIsArray( $result );
		$this->assertEmpty( $result['cases'] );
		$this->assertEmpty( $result['progress'] );
		$this->assertIsArray( $result['date_index'] );
	}

	/**
	 * Test get_calendar_data returns null when cases SQL returns null.
	 */
	public function test_get_calendar_data_returns_null_when_cases_sql_returns_null(): void {
		// No filters → uses get_all() for cases.
		$this->mock_cases_sql->shouldReceive( 'get_all' )
			->once()
			->andReturn( null );

		$this->mock_progress_sql->shouldReceive( 'get_all' )
			->once()
			->andReturn( [] );

		$result = $this->calendar_orm->get_calendar_data(
			self::TEST_START_DATE,
			self::TEST_END_DATE
		);

		$this->assertIsArray( $result );
		$this->assertEmpty( $result['cases'] );
		$this->assertEmpty( $result['progress'] );
	}

	/**
	 * Test get_calendar_data returns null when progress SQL returns null.
	 */
	public function test_get_calendar_data_returns_null_when_progress_sql_returns_null(): void {
		// No filters → uses get_all() for both tables.
		$this->mock_cases_sql->shouldReceive( 'get_all' )
			->once()
			->andReturn( [] );

		$this->mock_progress_sql->shouldReceive( 'get_all' )
			->once()
			->andReturn( null );

		$result = $this->calendar_orm->get_calendar_data(
			self::TEST_START_DATE,
			self::TEST_END_DATE
		);

		$this->assertIsArray( $result );
		$this->assertEmpty( $result['cases'] );
		$this->assertEmpty( $result['progress'] );
	}

	/**
	 * Test get_calendar_cases returns only cases data.
	 */
	public function test_get_calendar_cases_returns_only_cases_data(): void {
		$mock_cases = [
			(object) [
				'id'          => self::TEST_CASE_ID,
				'id_user'     => self::TEST_USER_ID,
				'title'       => 'Test Case',
				'status'      => 'open',
				'description' => 'Test Description',
				'start_at'    => '2024-01-01 09:00:00',
				'due_at'      => '2024-01-31 17:00:00',
				'created_at'  => '2024-01-01 08:00:00',
			],
		];

		// No filters → uses get_all() for cases.
		$this->mock_cases_sql->shouldReceive( 'get_all' )
			->once()
			->andReturn( $mock_cases );

		Functions\when( 'get_user_by' )->justReturn( (object) [ 'display_name' => 'Test User' ] );

		$result = $this->calendar_orm->get_calendar_cases(
			self::TEST_START_DATE,
			self::TEST_END_DATE
		);

		$this->assertIsArray( $result );
		$this->assertCount( 1, $result );

		if ( count( $result ) > 0 ) {
			$case = $result[0];
			$this->assertArrayHasKey( 'id', $case );
			$this->assertArrayHasKey( 'title', $case );
			$this->assertArrayHasKey( 'client_name', $case );
		}
	}

	/**
	 * Test get_calendar_progress returns only progress data.
	 */
	public function test_get_calendar_progress_returns_only_progress_data(): void {
		$mock_progress = [
			(object) [
				'id'         => self::TEST_PROGRESS_ID,
				'id_case'    => self::TEST_CASE_ID,
				'id_user'    => self::TEST_USER_ID,
				'text'       => 'Progress update',
				'created_at' => '2024-01-15 12:00:00',
			],
		];

		$this->mock_progress_sql->shouldReceive( 'get_all' )
			->once()
			->andReturn( $mock_progress );

		// Progress loop calls get_by() to resolve case titles.
		$this->mock_cases_sql->shouldReceive( 'get_by' )
			->once()
			->with( [ 'id' => self::TEST_CASE_ID ] )
			->andReturn( [ (object) [ 'title' => 'Test Case' ] ] );

		Functions\when( 'get_user_by' )->justReturn( (object) [ 'display_name' => 'Test User' ] );

		$result = $this->calendar_orm->get_calendar_progress(
			self::TEST_START_DATE,
			self::TEST_END_DATE
		);

		$this->assertIsArray( $result );
		$this->assertCount( 1, $result );

		if ( count( $result ) > 0 ) {
			$progress = $result[0];
			$this->assertArrayHasKey( 'id', $progress );
			$this->assertArrayHasKey( 'id_case', $progress );
			$this->assertArrayHasKey( 'text', $progress );
			$this->assertArrayHasKey( 'case_title', $progress );
			$this->assertArrayHasKey( 'client_name', $progress );
		}
	}

	/**
	 * Test get_calendar_progress filters by user ID.
	 */
	public function test_get_calendar_progress_filters_by_user_id(): void {
		$id_user = self::TEST_USER_ID;
		$mock_progress = [
			(object) [
				'id'         => self::TEST_PROGRESS_ID,
				'id_case'    => self::TEST_CASE_ID,
				'id_user'    => $id_user,
				'text'       => 'Progress update',
				'created_at' => '2024-01-15 12:00:00',
			],
			(object) [
				'id'         => self::TEST_PROGRESS_ID + 1,
				'id_case'    => self::TEST_CASE_ID + 1,
				'id_user'    => $id_user + 1, // Different user
				'text'       => 'Another progress',
				'created_at' => '2024-01-16 12:00:00',
			],
		];

		$this->mock_progress_sql->shouldReceive( 'get_all' )
			->once()
			->andReturn( $mock_progress );

		// Only the first progress entry matches the user filter.
		$this->mock_cases_sql->shouldReceive( 'get_by' )
			->once()
			->with( [ 'id' => self::TEST_CASE_ID ] )
			->andReturn( [ (object) [ 'title' => 'Test Case' ] ] );

		Functions\when( 'get_user_by' )->justReturn( (object) [ 'display_name' => 'Test User' ] );

		$result = $this->calendar_orm->get_calendar_progress(
			self::TEST_START_DATE,
			self::TEST_END_DATE,
			$id_user
		);

		$this->assertIsArray( $result );
		$this->assertCount( 1, $result );
		$this->assertSame( self::TEST_PROGRESS_ID, $result[0]['id'] );
	}

	/**
	 * Test get_date_index returns date index.
	 */
	public function test_get_date_index_returns_date_index(): void {
		$mock_index = [
			'2024-01-01' => [
				'starts' => [ self::TEST_CASE_ID ],
				'ends'   => [],
			],
		];

		// Mock CalendarIndex::get().
		Functions\when( 'get_option' )->justReturn( [ 'only_dates_index' => $mock_index ] );

		$result = $this->calendar_orm->get_date_index();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( '2024-01-01', $result );
		$this->assertArrayHasKey( 'starts', $result['2024-01-01'] );
		$this->assertArrayHasKey( 'ends', $result['2024-01-01'] );
	}

	/**
	 * Test rebuild_date_index method exists.
	 */
	public function test_rebuild_date_index_method_exists(): void {
		// We can't easily test static method calls that require $wpdb.
		// But we can verify the method exists.
		$this->assertTrue( method_exists( $this->calendar_orm, 'rebuild_date_index' ) );
	}

	/**
	 * Test clear_date_index method exists.
	 */
	public function test_clear_date_index_method_exists(): void {
		// We can't easily test static method calls.
		// But we can verify the method exists.
		$this->assertTrue( method_exists( $this->calendar_orm, 'clear_date_index' ) );
	}

	/**
	 * Test get_calendar_data handles cases without start_at or due_at.
	 */
	public function test_get_calendar_data_handles_cases_without_start_at_or_due_at(): void {
		$mock_cases = [
			(object) [
				'id'          => self::TEST_CASE_ID,
				'id_user'     => self::TEST_USER_ID,
				'title'       => 'Test Case',
				'status'      => 'open',
				'description' => 'Test Description',
				'start_at'    => null,
				'due_at'      => null,
				'created_at'  => '2024-01-01 08:00:00',
			],
		];

		// No filters → uses get_all() for cases.
		$this->mock_cases_sql->shouldReceive( 'get_all' )
			->once()
			->andReturn( $mock_cases );

		$this->mock_progress_sql->shouldReceive( 'get_all' )
			->once()
			->andReturn( [] );

		$result = $this->calendar_orm->get_calendar_data(
			self::TEST_START_DATE,
			self::TEST_END_DATE
		);

		$this->assertIsArray( $result );
		$this->assertEmpty( $result['cases'] ); // Case without start_at/due_at should be filtered out
		$this->assertEmpty( $result['progress'] );
	}

	/**
	 * Test get_calendar_data handles cases outside date range.
	 */
	public function test_get_calendar_data_handles_cases_outside_date_range(): void {
		$mock_cases = [
			(object) [
				'id'          => self::TEST_CASE_ID,
				'id_user'     => self::TEST_USER_ID,
				'title'       => 'Test Case',
				'status'      => 'open',
				'description' => 'Test Description',
				'start_at'    => '2023-12-01 09:00:00', // Before start date
				'due_at'      => '2023-12-31 17:00:00', // Before start date
				'created_at'  => '2023-12-01 08:00:00',
			],
		];

		// No filters → uses get_all() for cases.
		$this->mock_cases_sql->shouldReceive( 'get_all' )
			->once()
			->andReturn( $mock_cases );

		$this->mock_progress_sql->shouldReceive( 'get_all' )
			->once()
			->andReturn( [] );

		$result = $this->calendar_orm->get_calendar_data(
			self::TEST_START_DATE,
			self::TEST_END_DATE
		);

		$this->assertIsArray( $result );
		$this->assertEmpty( $result['cases'] ); // Case outside date range should be filtered out
		$this->assertEmpty( $result['progress'] );
	}

	/**
	 * Test get_calendar_data handles progress outside date range.
	 */
	public function test_get_calendar_data_handles_progress_outside_date_range(): void {
		$mock_progress = [
			(object) [
				'id'         => self::TEST_PROGRESS_ID,
				'id_case'    => self::TEST_CASE_ID,
				'id_user'    => self::TEST_USER_ID,
				'text'       => 'Progress update',
				'created_at' => '2023-12-31 12:00:00', // Before start date
			],
		];

		$this->mock_cases_sql->shouldReceive( 'get_all' )
			->once()
			->andReturn( [] );

		$this->mock_progress_sql->shouldReceive( 'get_all' )
			->once()
			->andReturn( $mock_progress );

		$result = $this->calendar_orm->get_calendar_data(
			self::TEST_START_DATE,
			self::TEST_END_DATE
		);

		$this->assertIsArray( $result );
		$this->assertEmpty( $result['cases'] );
		$this->assertEmpty( $result['progress'] ); // Progress outside date range should be filtered out
	}
}
