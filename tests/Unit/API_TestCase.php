<?php
/**
 * API Test Case Class
 *
 * Base test class for API tests with pre-configured WordPress and database stubs.
 *
 * @package Service_Tracker
 */

namespace STOLMC_Service_Tracker\Tests\Unit;

use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Mockery\MockInterface;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Sql;

/**
 * API Test Case Class.
 *
 * Provides common stubs and mocks for all API endpoint tests.
 */
abstract class API_TestCase extends Unit_TestCase {

	/**
	 * Mock SQL instance for cases table.
	 *
	 * @var STOLMC_Service_Tracker_Sql|MockInterface
	 */
	protected $mock_sql;

	/**
	 * Mock SQL instance for progress table.
	 *
	 * @var STOLMC_Service_Tracker_Sql|MockInterface
	 */
	protected $mock_progress_sql;

	/**
	 * Mock global $wpdb object.
	 *
	 * @var object
	 */
	protected $mock_wpdb;

	/**
	 * Default user ID for testing.
	 *
	 * @var int
	 */
	protected const TEST_USER_ID = 1;

	/**
	 * Default case ID for testing.
	 *
	 * @var int
	 */
	protected const TEST_CASE_ID = 100;

	/**
	 * Mock user object.
	 *
	 * @var object
	 */
	protected $mock_user;

	/**
	 * Set up test fixtures.
	 */
	protected function set_up(): void {
		parent::set_up();

		$this->setup_common_stubs();
	}

	/**
	 * Set up common stubs for all API tests.
	 *
	 * This method mocks all WordPress functions and database interactions
	 * that are commonly used across all API endpoints.
	 */
	protected function setup_common_stubs(): void {
		// Mock $wpdb global.
		$this->mock_wpdb = (object) [
			'prefix'    => 'wp_',
			'insert_id' => self::TEST_CASE_ID,
		];

		// Make $wpdb available globally.
		$GLOBALS['wpdb'] = $this->mock_wpdb;

		// Mock WordPress authentication and authorization.
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'get_current_user_id' )->justReturn( self::TEST_USER_ID );
		Functions\when( 'is_user_logged_in' )->justReturn( true );

		// Mock user retrieval.
		$this->mock_user = (object) [
			'ID'         => self::TEST_USER_ID,
			'user_email' => 'test@example.com',
			'display_name' => 'Test User',
		];
		Functions\when( 'get_user_by' )->justReturn( $this->mock_user );

		// Mock nonce verification (always valid).
		Functions\when( 'wp_verify_nonce' )->justReturn( true );

		// Mock translation functions - return the first argument (the text).
		Functions\when( '__' )->returnArg( 1 );
		Functions\when( '_e' )->returnArg( 1 );
		Functions\when( 'esc_html__' )->returnArg( 1 );
		Functions\when( 'esc_attr__' )->returnArg( 1 );

		// Mock REST route registration.
		Functions\when( 'register_rest_route' )->justReturn( true );

		// Mock wp_mail.
		Functions\when( 'wp_mail' )->justReturn( true );

		// Mock WordPress sanitization functions.
		Functions\when( 'sanitize_user' )->returnArg( 1 );
		Functions\when( 'sanitize_email' )->returnArg( 1 );
		Functions\when( 'sanitize_text_field' )->returnArg( 1 );

		// Mock WordPress password generation.
		Functions\when( 'wp_generate_password' )->justReturn( 'test_password_123' );

		// Mock WordPress user functions.
		Functions\when( 'wp_insert_user' )->justReturn( 100 );
		Functions\when( 'update_user_meta' )->justReturn( true );
		Functions\when( 'get_user_meta' )->justReturn( '' );
	}

	/**
	 * Create a mock WP_REST_Request object.
	 *
	 * @param array $params Request parameters.
	 * @param array $headers Request headers.
	 * @param string $body Request body.
	 * @return WP_REST_Request
	 */
	protected function create_mock_request(
		array $params = [],
		array $headers = [ 'x_wp_nonce' => [ 'valid_nonce' ] ],
		string $body = ''
	): WP_REST_Request {
		return new WP_REST_Request( $params, $body, $headers );
	}

	/**
	 * Create a mock SQL instance.
	 *
	 * @return STOLMC_Service_Tracker_Sql|MockInterface
	 */
	protected function create_mock_sql(): MockInterface {
		$sql = \Mockery::mock( STOLMC_Service_Tracker_Sql::class );

		// Default behavior for insert - success.
		$sql->allows( 'insert' )->andReturn( 'Success, data was inserted' );

		// Default behavior for get_by - empty array.
		$sql->allows( 'get_by' )->andReturn( [] );

		// Default behavior for update - success.
		$sql->allows( 'update' )->andReturn( 1 );

		// Default behavior for delete - success.
		$sql->allows( 'delete' )->andReturn( 1 );

		return $sql;
	}

	/**
	 * Set up SQL mock expectations for insert operations.
	 *
	 * @param MockInterface $sql_mock The SQL mock instance.
	 * @param array $data Expected data to be inserted.
	 * @param int $insert_id The insert ID to return.
	 */
	protected function expect_sql_insert(
		MockInterface $sql_mock,
		array $data = [],
		int $insert_id = 1
	): void {
		$this->mock_wpdb->insert_id = $insert_id;

		$sql_mock->shouldReceive( 'insert' )
			->once()
			->with( \Mockery::type( 'array' ) )
			->andReturn( 'Success, data was inserted' );
	}

	/**
	 * Set up SQL mock expectations for get_by operations.
	 *
	 * @param MockInterface $sql_mock The SQL mock instance.
	 * @param array $results Results to return.
	 */
	protected function expect_sql_get_by(
		MockInterface $sql_mock,
		array $results = []
	): void {
		$sql_mock->shouldReceive( 'get_by' )
			->once()
			->with( \Mockery::type( 'array' ) )
			->andReturn( $results );
	}

	/**
	 * Set up SQL mock expectations for update operations.
	 *
	 * @param MockInterface $sql_mock The SQL mock instance.
	 * @param int $rows_affected Number of rows affected.
	 */
	protected function expect_sql_update(
		MockInterface $sql_mock,
		int $rows_affected = 1
	): void {
		$sql_mock->shouldReceive( 'update' )
			->once()
			->with( \Mockery::type( 'array' ), \Mockery::type( 'array' ) )
			->andReturn( $rows_affected );
	}

	/**
	 * Set up SQL mock expectations for delete operations.
	 *
	 * @param MockInterface $sql_mock The SQL mock instance.
	 * @param int $rows_affected Number of rows affected.
	 */
	protected function expect_sql_delete(
		MockInterface $sql_mock,
		int $rows_affected = 1
	): void {
		$sql_mock->shouldReceive( 'delete' )
			->once()
			->with( \Mockery::type( 'array' ) )
			->andReturn( $rows_affected );
	}

	/**
	 * Create a sample case data array.
	 *
	 * @param array $overrides Override default values.
	 * @return array
	 */
	protected function create_sample_case_data( array $overrides = [] ): array {
		return array_merge(
			[
				'id_user'     => self::TEST_USER_ID,
				'title'       => 'Test Case',
				'status'      => 'open',
				'description' => 'Test Description',
			],
			$overrides
		);
	}

	/**
	 * Create a sample case object (as returned from database).
	 *
	 * @param array $overrides Override default values.
	 * @return object
	 */
	protected function create_sample_case_object( array $overrides = [] ): object {
		return (object) array_merge(
			[
				'id'          => self::TEST_CASE_ID,
				'id_user'     => self::TEST_USER_ID,
				'title'       => 'Test Case',
				'status'      => 'open',
				'description' => 'Test Description',
				'created_at'  => '2024-01-01 12:00:00',
			],
			$overrides
		);
	}

	/**
	 * Create sample progress data.
	 *
	 * @param array $overrides Override default values.
	 * @return array
	 */
	protected function create_sample_progress_data( array $overrides = [] ): array {
		return array_merge(
			[
				'id_user' => self::TEST_USER_ID,
				'id_case' => self::TEST_CASE_ID,
				'text'    => 'Progress update text',
			],
			$overrides
		);
	}

	/**
	 * Create a mock WP_REST_Response object.
	 *
	 * @param mixed $data Response data.
	 * @param int $status HTTP status code.
	 * @return WP_REST_Response|MockInterface
	 */
	protected function create_rest_response(
		mixed $data = null,
		int $status = 200
	): MockInterface {
		$response = \Mockery::mock( WP_REST_Response::class );
		$response->allows( 'get_data' )->andReturn( $data );
		$response->allows( 'get_status' )->andReturn( $status );
		return $response;
	}

	/**
	 * Expect hooks to be fired (do_action).
	 *
	 * @param string $hook_name The hook name.
	 * @param int $times Number of times to expect the hook.
	 */
	protected function expect_action_hook( string $hook_name, int $times = 1 ): void {
		Actions\expectDone( $hook_name )
			->times( $times );
	}

	/**
	 * Expect filters to be applied (apply_filters).
	 *
	 * @param string $filter_name The filter name.
	 * @param mixed $value The value to return.
	 * @param int $times Number of times to expect the filter.
	 */
	protected function expect_filter( string $filter_name, mixed $value = null, int $times = 1 ): void {
		Filters\expectApplied( $filter_name )
			->times( $times )
			->withAnyArgs()
			->andReturn( $value );
	}
}
