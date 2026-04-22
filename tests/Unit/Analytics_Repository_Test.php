<?php
/**
 * Analytics Repository Test
 *
 * Tests for the STOLMC_Service_Tracker_Analytics_Repository class.
 *
 * @package Service_Tracker
 */

namespace STOLMC_Service_Tracker\Tests\Unit;

use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Mockery;

/**
 * Analytics Repository Test Class.
 *
 * @group   unit
 * @group   repository
 * @group   analytics
 */
class Analytics_Repository_Test extends Unit_TestCase {

	/**
	 * Analytics Repository instance.
	 *
	 * @var \STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Analytics_Repository
	 */
	protected $analytics_orm;

	/**
	 * Mock users SQL helper.
	 *
	 * @var \Mockery\MockInterface
	 */
	protected $mock_users_sql;

	/**
	 * Mock cases SQL helper.
	 *
	 * @var \Mockery\MockInterface
	 */
	protected $mock_cases_sql;

	/**
	 * Mock progress SQL helper.
	 *
	 * @var \Mockery\MockInterface
	 */
	protected $mock_progress_sql;

	/**
	 * Mock notifications SQL helper.
	 *
	 * @var \Mockery\MockInterface
	 */
	protected $mock_notifications_sql;

	/**
	 * Mock activity log SQL helper.
	 *
	 * @var \Mockery\MockInterface
	 */
	protected $mock_activity_log_sql;

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

		// Create mock $wpdb.
		$this->mock_wpdb = Mockery::mock();
		$this->mock_wpdb->prefix = 'wp_';
		$this->mock_wpdb->users = 'wp_users';
		$GLOBALS['wpdb'] = $this->mock_wpdb;

		// Create mock SQL helpers.
		$this->mock_users_sql = Mockery::mock( \STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Sql::class );
		$this->mock_cases_sql = Mockery::mock( \STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Sql::class );
		$this->mock_progress_sql = Mockery::mock( \STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Sql::class );
		$this->mock_notifications_sql = Mockery::mock( \STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Sql::class );
		$this->mock_activity_log_sql = Mockery::mock( \STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Sql::class );

		// Create the Analytics Repository instance.
		$this->analytics_orm = new \STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Analytics_Repository();

		// Use the existing helper function to set private properties for testing.
		set_private_property( $this->analytics_orm, 'users_sql', $this->mock_users_sql );
		set_private_property( $this->analytics_orm, 'cases_sql', $this->mock_cases_sql );
		set_private_property( $this->analytics_orm, 'progress_sql', $this->mock_progress_sql );
		set_private_property( $this->analytics_orm, 'notifications_sql', $this->mock_notifications_sql );
		set_private_property( $this->analytics_orm, 'activity_log_sql', $this->mock_activity_log_sql );
	}

	/**
	 * Test constructor initializes SQL helpers with correct table names.
	 */
	public function test_constructor_initializes_sql_helpers(): void {
		// Create a fresh instance to test constructor.
		$analytics_orm = new \STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Analytics_Repository();

		// Verify the object is created.
		$this->assertInstanceOf(
			\STOLMC_Service_Tracker\includes\Repositories\STOLMC_Service_Tracker_Analytics_Repository::class,
			$analytics_orm
		);
	}

	/**
	 * Test get_summary_stats returns correct statistics.
	 */
	public function test_get_summary_stats_returns_correct_statistics(): void {
		// Mock the date function call.
		Functions\expect( 'gmdate' )->once()->with( 'Y-m-d H:i:s', Mockery::type( 'int' ) )->andReturn( '2024-01-01 00:00:00' );
		Functions\expect( 'strtotime' )->once()->with( '-30 days' )->andReturn( 1704067200 );

		// Set up mock return values.
		$this->mock_users_sql->shouldReceive( 'count_distinct' )
			->once()
			->with( 'ID' )
			->andReturn( 50 );

		$this->mock_cases_sql->shouldReceive( 'count_all' )
			->once()
			->andReturn( 100 );

		$this->mock_cases_sql->shouldReceive( 'count_by' )
			->once()
			->with( [ 'status' => 'open' ] )
			->andReturn( 30 );

		$this->mock_cases_sql->shouldReceive( 'count_by' )
			->once()
			->with( [ 'status' => 'close' ] )
			->andReturn( 70 );

		$this->mock_progress_sql->shouldReceive( 'count_all' )
			->once()
			->andReturn( 200 );

		$this->mock_notifications_sql->shouldReceive( 'count_all' )
			->once()
			->andReturn( 150 );

		$this->mock_notifications_sql->shouldReceive( 'count_by' )
			->once()
			->with( [ 'status' => 'sent' ] )
			->andReturn( 140 );

		$this->mock_notifications_sql->shouldReceive( 'count_by' )
			->once()
			->with( [ 'status' => 'failed' ] )
			->andReturn( 10 );

		$this->mock_activity_log_sql->shouldReceive( 'count_distinct' )
			->once()
			->with(
				'actor_user_id',
				[
					'actor_user_id !=' => null,
					'created_at >='    => '2024-01-01 00:00:00',
				]
			)
			->andReturn( 5 );

		// Call the method.
		$result = $this->analytics_orm->get_summary_stats();

		// Verify the result structure and values.
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'total_customers', $result );
		$this->assertArrayHasKey( 'total_cases', $result );
		$this->assertArrayHasKey( 'open_cases', $result );
		$this->assertArrayHasKey( 'closed_cases', $result );
		$this->assertArrayHasKey( 'total_progress_updates', $result );
		$this->assertArrayHasKey( 'notifications_attempted', $result );
		$this->assertArrayHasKey( 'notifications_sent', $result );
		$this->assertArrayHasKey( 'notifications_failed', $result );
		$this->assertArrayHasKey( 'active_admins_last_30_days', $result );

		$this->assertSame( 50, $result['total_customers'] );
		$this->assertSame( 100, $result['total_cases'] );
		$this->assertSame( 30, $result['open_cases'] );
		$this->assertSame( 70, $result['closed_cases'] );
		$this->assertSame( 200, $result['total_progress_updates'] );
		$this->assertSame( 150, $result['notifications_attempted'] );
		$this->assertSame( 140, $result['notifications_sent'] );
		$this->assertSame( 10, $result['notifications_failed'] );
		$this->assertSame( 5, $result['active_admins_last_30_days'] );
	}

	/**
	 * Test get_customer_stats returns empty array when no customers.
	 */
	public function test_get_customer_stats_returns_empty_array_when_no_customers(): void {
		$this->mock_users_sql->shouldReceive( 'get_all_with_columns' )
			->once()
			->with( [ 'ID', 'display_name', 'user_email' ], 'ID ASC' )
			->andReturn( [] );

		$result = $this->analytics_orm->get_customer_stats();

		$this->assertIsArray( $result );
		$this->assertEmpty( $result );
	}

	/**
	 * Test get_customer_stats returns customer statistics.
	 */
	public function test_get_customer_stats_returns_customer_statistics(): void {
		// Mock customers data.
		$customers = [
			(object) [
				'ID'           => 1,
				'display_name' => 'John Doe',
				'user_email'   => 'john@example.com',
			],
			(object) [
				'ID'           => 2,
				'display_name' => 'Jane Smith',
				'user_email'   => 'jane@example.com',
			],
		];

		$this->mock_users_sql->shouldReceive( 'get_all_with_columns' )
			->once()
			->with( [ 'ID', 'display_name', 'user_email' ], 'ID ASC' )
			->andReturn( $customers );

		// Mock counts for first customer (ID: 1).
		$this->mock_cases_sql->shouldReceive( 'count_by' )
			->once()
			->with( [ 'id_user' => 1 ] )
			->andReturn( 5 );

		$this->mock_cases_sql->shouldReceive( 'count_by' )
			->once()
			->with( [
				'id_user' => 1,
				'status'  => 'open',
			] )
			->andReturn( 2 );

		$this->mock_cases_sql->shouldReceive( 'count_by' )
			->once()
			->with( [
				'id_user' => 1,
				'status'  => 'close',
			] )
			->andReturn( 3 );

		$this->mock_progress_sql->shouldReceive( 'count_by' )
			->once()
			->with( [ 'id_user' => 1 ] )
			->andReturn( 10 );

		$this->mock_notifications_sql->shouldReceive( 'count_by' )
			->once()
			->with( [
				'id_user' => 1,
				'status'  => 'sent',
			] )
			->andReturn( 8 );

		$this->mock_cases_sql->shouldReceive( 'max_of' )
			->once()
			->with( 'created_at', [ 'id_user' => 1 ] )
			->andReturn( '2024-01-15 10:30:00' );

		$this->mock_progress_sql->shouldReceive( 'max_of' )
			->once()
			->with( 'created_at', [ 'id_user' => 1 ] )
			->andReturn( '2024-01-20 14:45:00' );

		// Mock counts for second customer (ID: 2) - no cases or progress.
		$this->mock_cases_sql->shouldReceive( 'count_by' )
			->once()
			->with( [ 'id_user' => 2 ] )
			->andReturn( 0 );

		$this->mock_cases_sql->shouldReceive( 'count_by' )
			->once()
			->with( [
				'id_user' => 2,
				'status'  => 'open',
			] )
			->andReturn( 0 );

		$this->mock_cases_sql->shouldReceive( 'count_by' )
			->once()
			->with( [
				'id_user' => 2,
				'status'  => 'close',
			] )
			->andReturn( 0 );

		$this->mock_progress_sql->shouldReceive( 'count_by' )
			->once()
			->with( [ 'id_user' => 2 ] )
			->andReturn( 0 );

		$this->mock_notifications_sql->shouldReceive( 'count_by' )
			->once()
			->with( [
				'id_user' => 2,
				'status'  => 'sent',
			] )
			->andReturn( 0 );

		$this->mock_cases_sql->shouldReceive( 'max_of' )
			->once()
			->with( 'created_at', [ 'id_user' => 2 ] )
			->andReturn( null );

		$this->mock_progress_sql->shouldReceive( 'max_of' )
			->once()
			->with( 'created_at', [ 'id_user' => 2 ] )
			->andReturn( null );

		// Second customer should be skipped since they have no cases or progress.

		$result = $this->analytics_orm->get_customer_stats();

		$this->assertIsArray( $result );
		$this->assertCount( 1, $result );

		$customer_stats = $result[0];
		$this->assertSame( 1, $customer_stats['user_id'] );
		$this->assertSame( 'John Doe', $customer_stats['name'] );
		$this->assertSame( 'john@example.com', $customer_stats['email'] );
		$this->assertSame( 5, $customer_stats['total_cases'] );
		$this->assertSame( 2, $customer_stats['open_cases'] );
		$this->assertSame( 3, $customer_stats['closed_cases'] );
		$this->assertSame( 10, $customer_stats['progress_updates'] );
		$this->assertSame( 8, $customer_stats['notifications_sent'] );
		$this->assertSame( '2024-01-20 14:45:00', $customer_stats['last_activity_at'] );
	}

	/**
	 * Test get_admin_stats returns empty array when no admin activity.
	 */
	public function test_get_admin_stats_returns_empty_array_when_no_admin_activity(): void {
		$this->mock_activity_log_sql->shouldReceive( 'get_distinct_values' )
			->once()
			->with( 'actor_user_id', [ 'actor_user_id !=' => null ], 'actor_user_id ASC' )
			->andReturn( [] );

		$result = $this->analytics_orm->get_admin_stats();

		$this->assertIsArray( $result );
		$this->assertEmpty( $result );
	}

	/**
	 * Test get_admin_stats returns admin statistics.
	 */
	public function test_get_admin_stats_returns_admin_statistics(): void {
		// Mock admin IDs.
		$admin_ids = [ 1, 2 ];

		$this->mock_activity_log_sql->shouldReceive( 'get_distinct_values' )
			->once()
			->with( 'actor_user_id', [ 'actor_user_id !=' => null ], 'actor_user_id ASC' )
			->andReturn( $admin_ids );

		// Mock user data for admin ID 1.
		$user_data = [
			(object) [
				'ID'           => 1,
				'display_name' => 'Admin User',
				'user_email'   => 'admin@example.com',
			],
		];

		$this->mock_users_sql->shouldReceive( 'get_by' )
			->once()
			->with( [ 'ID' => 1 ] )
			->andReturn( $user_data );

		// Mock activity counts for admin ID 1.
		$this->mock_activity_log_sql->shouldReceive( 'count_by' )
			->once()
			->with( [
				'actor_user_id' => 1,
				'entity_type'   => 'case',
				'action_type'   => 'created',
			] )
			->andReturn( 5 );

		$this->mock_activity_log_sql->shouldReceive( 'count_by' )
			->once()
			->with( [
				'actor_user_id' => 1,
				'entity_type'   => 'case',
				'action_type'   => 'updated',
			] )
			->andReturn( 3 );

		$this->mock_activity_log_sql->shouldReceive( 'count_by' )
			->once()
			->with( [
				'actor_user_id' => 1,
				'entity_type'   => 'case',
				'action_type'   => 'deleted',
			] )
			->andReturn( 1 );

		$this->mock_activity_log_sql->shouldReceive( 'count_by' )
			->once()
			->with( [
				'actor_user_id' => 1,
				'entity_type'   => 'progress',
				'action_type'   => 'created',
			] )
			->andReturn( 8 );

		$this->mock_activity_log_sql->shouldReceive( 'count_by' )
			->once()
			->with( [
				'actor_user_id' => 1,
				'entity_type'   => 'progress',
				'action_type'   => 'updated',
			] )
			->andReturn( 4 );

		$this->mock_activity_log_sql->shouldReceive( 'count_by' )
			->once()
			->with( [
				'actor_user_id' => 1,
				'entity_type'   => 'progress',
				'action_type'   => 'deleted',
			] )
			->andReturn( 2 );

		$this->mock_notifications_sql->shouldReceive( 'count_by' )
			->once()
			->with( [ 'actor_user_id' => 1 ] )
			->andReturn( 15 );

		$this->mock_activity_log_sql->shouldReceive( 'max_of' )
			->once()
			->with( 'created_at', [ 'actor_user_id' => 1 ] )
			->andReturn( '2024-01-25 16:30:00' );

		// Admin ID 2 should be skipped because user data is not found.
		$this->mock_users_sql->shouldReceive( 'get_by' )
			->once()
			->with( [ 'ID' => 2 ] )
			->andReturn( [] );

		$result = $this->analytics_orm->get_admin_stats();

		$this->assertIsArray( $result );
		$this->assertCount( 1, $result );

		$admin_stats = $result[0];
		$this->assertSame( 1, $admin_stats['user_id'] );
		$this->assertSame( 'Admin User', $admin_stats['display_name'] );
		$this->assertSame( 'admin@example.com', $admin_stats['email'] );
		$this->assertSame( 5, $admin_stats['cases_created'] );
		$this->assertSame( 3, $admin_stats['cases_updated'] );
		$this->assertSame( 1, $admin_stats['cases_deleted'] );
		$this->assertSame( 8, $admin_stats['progress_created'] );
		$this->assertSame( 4, $admin_stats['progress_updated'] );
		$this->assertSame( 2, $admin_stats['progress_deleted'] );
		$this->assertSame( 15, $admin_stats['notifications_triggered'] );
		$this->assertSame( '2024-01-25 16:30:00', $admin_stats['last_activity_at'] );
	}

	/**
	 * Test get_trends returns trend data with date filters.
	 */
	public function test_get_trends_returns_trend_data_with_date_filters(): void {
		$start_date = '2024-01-01';
		$end_date = '2024-01-31';

		// Mock daily counts.
		$cases_by_period = [
			'2024-01-01' => 5,
			'2024-01-02' => 3,
		];

		$progress_by_period = [
			'2024-01-01' => 10,
			'2024-01-02' => 8,
		];

		$notifications_by_period = [
			'2024-01-01' => 2,
			'2024-01-02' => 1,
		];

		$admin_actions_by_period = [
			'2024-01-01' => 15,
			'2024-01-02' => 12,
		];

		$this->mock_cases_sql->shouldReceive( 'get_daily_counts' )
			->once()
			->with(
				'created_at',
				[
					'created_at >=' => '2024-01-01',
					'created_at <=' => '2024-01-31',
				],
				5
			)
			->andReturn( $cases_by_period );

		$this->mock_progress_sql->shouldReceive( 'get_daily_counts' )
			->once()
			->with(
				'created_at',
				[
					'created_at >=' => '2024-01-01',
					'created_at <=' => '2024-01-31',
				],
				5
			)
			->andReturn( $progress_by_period );

		$this->mock_notifications_sql->shouldReceive( 'get_daily_counts' )
			->once()
			->with(
				'created_at',
				[
					'created_at >=' => '2024-01-01',
					'created_at <=' => '2024-01-31',
				],
				5
			)
			->andReturn( $notifications_by_period );

		$this->mock_activity_log_sql->shouldReceive( 'get_daily_counts' )
			->once()
			->with(
				'created_at',
				[
					'created_at >=' => '2024-01-01',
					'created_at <=' => '2024-01-31',
					'actor_user_id !=' => null,
				],
				5
			)
			->andReturn( $admin_actions_by_period );

		$result = $this->analytics_orm->get_trends( $start_date, $end_date );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'cases_created_by_period', $result );
		$this->assertArrayHasKey( 'progress_created_by_period', $result );
		$this->assertArrayHasKey( 'notifications_by_period', $result );
		$this->assertArrayHasKey( 'admin_actions_by_period', $result );

		$this->assertSame( $cases_by_period, $result['cases_created_by_period'] );
		$this->assertSame( $progress_by_period, $result['progress_created_by_period'] );
		$this->assertSame( $notifications_by_period, $result['notifications_by_period'] );
		$this->assertSame( $admin_actions_by_period, $result['admin_actions_by_period'] );
	}

	/**
	 * Test get_trends returns trend data without date filters.
	 */
	public function test_get_trends_returns_trend_data_without_date_filters(): void {
		// Mock daily counts with empty conditions.
		$cases_by_period = [
			'2024-01-01' => 5,
			'2024-01-02' => 3,
		];

		$progress_by_period = [
			'2024-01-01' => 10,
			'2024-01-02' => 8,
		];

		$notifications_by_period = [
			'2024-01-01' => 2,
			'2024-01-02' => 1,
		];

		$admin_actions_by_period = [
			'2024-01-01' => 15,
			'2024-01-02' => 12,
		];

		$this->mock_cases_sql->shouldReceive( 'get_daily_counts' )
			->once()
			->with( 'created_at', [], 5 )
			->andReturn( $cases_by_period );

		$this->mock_progress_sql->shouldReceive( 'get_daily_counts' )
			->once()
			->with( 'created_at', [], 5 )
			->andReturn( $progress_by_period );

		$this->mock_notifications_sql->shouldReceive( 'get_daily_counts' )
			->once()
			->with( 'created_at', [], 5 )
			->andReturn( $notifications_by_period );

		$this->mock_activity_log_sql->shouldReceive( 'get_daily_counts' )
			->once()
			->with(
				'created_at',
				[ 'actor_user_id !=' => null ],
				5
			)
			->andReturn( $admin_actions_by_period );

		$result = $this->analytics_orm->get_trends( null, null );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'cases_created_by_period', $result );
		$this->assertArrayHasKey( 'progress_created_by_period', $result );
		$this->assertArrayHasKey( 'notifications_by_period', $result );
		$this->assertArrayHasKey( 'admin_actions_by_period', $result );

		$this->assertSame( $cases_by_period, $result['cases_created_by_period'] );
		$this->assertSame( $progress_by_period, $result['progress_created_by_period'] );
		$this->assertSame( $notifications_by_period, $result['notifications_by_period'] );
		$this->assertSame( $admin_actions_by_period, $result['admin_actions_by_period'] );
	}

	/**
	 * Test get_trends returns trend data with only start date filter.
	 */
	public function test_get_trends_returns_trend_data_with_only_start_date_filter(): void {
		$start_date = '2024-01-01';

		// Mock daily counts with only start date condition.
		$cases_by_period = [
			'2024-01-01' => 5,
			'2024-01-02' => 3,
		];

		$this->mock_cases_sql->shouldReceive( 'get_daily_counts' )
			->once()
			->with(
				'created_at',
				[ 'created_at >=' => '2024-01-01' ],
				5
			)
			->andReturn( $cases_by_period );

		$this->mock_progress_sql->shouldReceive( 'get_daily_counts' )
			->once()
			->with(
				'created_at',
				[ 'created_at >=' => '2024-01-01' ],
				5
			)
			->andReturn( [] );

		$this->mock_notifications_sql->shouldReceive( 'get_daily_counts' )
			->once()
			->with(
				'created_at',
				[ 'created_at >=' => '2024-01-01' ],
				5
			)
			->andReturn( [] );

		$this->mock_activity_log_sql->shouldReceive( 'get_daily_counts' )
			->once()
			->with(
				'created_at',
				[
					'created_at >=' => '2024-01-01',
					'actor_user_id !=' => null,
				],
				5
			)
			->andReturn( [] );

		$result = $this->analytics_orm->get_trends( $start_date, null );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'cases_created_by_period', $result );
		$this->assertSame( $cases_by_period, $result['cases_created_by_period'] );
	}
}
