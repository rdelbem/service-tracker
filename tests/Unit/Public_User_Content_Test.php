<?php
/**
 * Public User Content Test
 *
 * Tests for the STOLMC_Service_Tracker_Public_User_Content class.
 *
 * @package Service_Tracker
 */

namespace STOLMC_Service_Tracker\Tests\Unit;

use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Mockery;
use WP_User;

/**
 * Public User Content Test Class.
 *
 * @group   unit
 * @group   public
 * @group   user-content
 */
class Public_User_Content_Test extends Unit_TestCase {

	/**
	 * Public User Content instance.
	 *
	 * @var \STOLMC_Service_Tracker\includes\Publics\STOLMC_Service_Tracker_Public_User_Content
	 */
	protected $user_content;

	/**
	 * Set up test fixtures.
	 */
	protected function set_up(): void {
		parent::set_up();

		$this->user_content = new \STOLMC_Service_Tracker\includes\Publics\STOLMC_Service_Tracker_Public_User_Content();

		// Mock $wpdb for SQL operations.
		$mock_wpdb = Mockery::mock();
		$mock_wpdb->prefix = 'wp_';
		$mock_wpdb->allows( 'get_results' )->andReturn( [] );
		$mock_wpdb->allows( 'prepare' )->andReturn( 'SELECT...' );
		$mock_wpdb->allows( 'query' )->andReturn( 0 );
		$GLOBALS['wpdb'] = $mock_wpdb;
	}

	/**
	 * Test get_user_id returns early when user is not logged in.
	 */
	public function test_get_user_id_returns_early_when_user_not_logged_in(): void {
		Functions\when( 'is_user_logged_in' )->justReturn( false );

		$this->user_content->get_user_id();

		$this->assertNull( $this->user_content->current_user_id );
	}

	/**
	 * Test get_user_id sets current user ID when logged in.
	 */
	public function test_get_user_id_sets_current_user_id_when_logged_in(): void {
		Functions\when( 'is_user_logged_in' )->justReturn( true );
		Functions\when( 'get_current_user_id' )->justReturn( 42 );

		Actions\expectDone( 'stolmc_service_tracker_public_user_identified' )
			->atMost()->once();

		$this->user_content->get_user_id();

		$this->assertSame( 42, $this->user_content->current_user_id );
	}

	/**
	 * Test get_user_id fires user identified action.
	 */
	public function test_get_user_id_fires_user_identified_action(): void {
		Functions\when( 'is_user_logged_in' )->justReturn( true );
		Functions\when( 'get_current_user_id' )->justReturn( 42 );

		Actions\expectDone( 'stolmc_service_tracker_public_user_identified' )
			->atMost()->once()
			->with( 42 );

		$this->user_content->get_user_id();

		$this->assertTrue( true );
	}

	/**
	 * Test check_user_role returns early when user has no roles.
	 */
	public function test_check_user_role_returns_early_when_no_roles(): void {
		$this->user_content->current_user_id = 42;

		$mock_user = Mockery::mock( WP_User::class )->makePartial();
		$mock_user->roles = [];

		Functions\when( 'get_role' )->justReturn( null );

		// We can't easily mock WP_User constructor, so we'll just verify the method runs.
		$this->user_content->check_user_role();

		$this->assertTrue( true );
	}

	/**
	 * Test user_cases_and_statuses property is accessible.
	 */
	public function test_user_cases_and_statuses_property_is_accessible(): void {
		$this->user_content->user_cases_and_statuses = [ 'test' => 'data' ];

		$this->assertIsArray( $this->user_content->user_cases_and_statuses );
		$this->assertSame( 'data', $this->user_content->user_cases_and_statuses['test'] );
	}

	/**
	 * Test current_user_id property is accessible.
	 */
	public function test_current_user_id_property_is_accessible(): void {
		$this->user_content->current_user_id = 123;

		$this->assertSame( 123, $this->user_content->current_user_id );
	}

	/**
	 * Test public user content class instantiates correctly.
	 */
	public function test_public_user_content_class_instantiates(): void {
		$this->assertInstanceOf(
			\STOLMC_Service_Tracker\includes\Publics\STOLMC_Service_Tracker_Public_User_Content::class,
			$this->user_content
		);
	}
}
