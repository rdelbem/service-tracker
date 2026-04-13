<?php
/**
 * Loader Test
 *
 * Tests for the STOLMC_Service_Tracker_Loader class.
 *
 * @package Service_Tracker
 */

namespace STOLMC_Service_Tracker\Tests\Unit;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

/**
 * Loader Test Class.
 *
 * @group   unit
 * @group   loader
 * @group   utils
 */
class Loader_Test extends Unit_TestCase {

	/**
	 * Loader instance.
	 *
	 * @var \STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Loader
	 */
	protected $loader;

	/**
	 * Set up test fixtures.
	 */
	protected function set_up(): void {
		parent::set_up();

		$this->loader = new \STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Loader();
	}

	/**
	 * Test constructor initializes empty actions and filters arrays.
	 */
	public function test_constructor_initializes_empty_arrays(): void {
		$this->assertInstanceOf(
			\STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Loader::class,
			$this->loader
		);
	}

	/**
	 * Test add_action adds action to collection.
	 */
	public function test_add_action_adds_action_to_collection(): void {
		$component = new \stdClass();
		$component->test_method = function() { return true; };

		$this->loader->add_action( 'init', $component, 'test_method' );

		// Use reflection to check protected property.
		$reflection = new \ReflectionClass( $this->loader );
		$property = $reflection->getProperty( 'actions' );
		$property->setAccessible( true );
		$actions = $property->getValue( $this->loader );

		$this->assertIsArray( $actions );
		$this->assertCount( 1, $actions );
		$this->assertSame( 'init', $actions[0]['hook'] );
		$this->assertSame( 'test_method', $actions[0]['callback'] );
	}

	/**
	 * Test add_action uses default priority and accepted_args.
	 */
	public function test_add_action_uses_default_priority_and_accepted_args(): void {
		$component = new \stdClass();
		$component->test_method = function() { return true; };

		$this->loader->add_action( 'init', $component, 'test_method' );

		$reflection = new \ReflectionClass( $this->loader );
		$property = $reflection->getProperty( 'actions' );
		$property->setAccessible( true );
		$actions = $property->getValue( $this->loader );

		$this->assertSame( 10, $actions[0]['priority'] );
		$this->assertSame( 1, $actions[0]['accepted_args'] );
	}

	/**
	 * Test add_action allows custom priority and accepted_args.
	 */
	public function test_add_action_allows_custom_priority_and_accepted_args(): void {
		$component = new \stdClass();
		$component->test_method = function() { return true; };

		$this->loader->add_action( 'init', $component, 'test_method', 20, 3 );

		$reflection = new \ReflectionClass( $this->loader );
		$property = $reflection->getProperty( 'actions' );
		$property->setAccessible( true );
		$actions = $property->getValue( $this->loader );

		$this->assertSame( 20, $actions[0]['priority'] );
		$this->assertSame( 3, $actions[0]['accepted_args'] );
	}

	/**
	 * Test add_filter adds filter to collection.
	 */
	public function test_add_filter_adds_filter_to_collection(): void {
		$component = new \stdClass();
		$component->test_method = function() { return true; };

		$this->loader->add_filter( 'the_content', $component, 'test_method' );

		$reflection = new \ReflectionClass( $this->loader );
		$property = $reflection->getProperty( 'filters' );
		$property->setAccessible( true );
		$filters = $property->getValue( $this->loader );

		$this->assertIsArray( $filters );
		$this->assertCount( 1, $filters );
		$this->assertSame( 'the_content', $filters[0]['hook'] );
		$this->assertSame( 'test_method', $filters[0]['callback'] );
	}

	/**
	 * Test add_filter uses default priority and accepted_args.
	 */
	public function test_add_filter_uses_default_priority_and_accepted_args(): void {
		$component = new \stdClass();
		$component->test_method = function() { return true; };

		$this->loader->add_filter( 'the_content', $component, 'test_method' );

		$reflection = new \ReflectionClass( $this->loader );
		$property = $reflection->getProperty( 'filters' );
		$property->setAccessible( true );
		$filters = $property->getValue( $this->loader );

		$this->assertSame( 10, $filters[0]['priority'] );
		$this->assertSame( 1, $filters[0]['accepted_args'] );
	}

	/**
	 * Test multiple actions can be added.
	 */
	public function test_multiple_actions_can_be_added(): void {
		$component = new \stdClass();
		$component->method1 = function() { return true; };
		$component->method2 = function() { return true; };

		$this->loader->add_action( 'init', $component, 'method1' );
		$this->loader->add_action( 'wp_loaded', $component, 'method2' );

		$reflection = new \ReflectionClass( $this->loader );
		$property = $reflection->getProperty( 'actions' );
		$property->setAccessible( true );
		$actions = $property->getValue( $this->loader );

		$this->assertCount( 2, $actions );
		$this->assertSame( 'init', $actions[0]['hook'] );
		$this->assertSame( 'wp_loaded', $actions[1]['hook'] );
	}

	/**
	 * Test multiple filters can be added.
	 */
	public function test_multiple_filters_can_be_added(): void {
		$component = new \stdClass();
		$component->method1 = function() { return true; };
		$component->method2 = function() { return true; };

		$this->loader->add_filter( 'the_content', $component, 'method1' );
		$this->loader->add_filter( 'the_title', $component, 'method2' );

		$reflection = new \ReflectionClass( $this->loader );
		$property = $reflection->getProperty( 'filters' );
		$property->setAccessible( true );
		$filters = $property->getValue( $this->loader );

		$this->assertCount( 2, $filters );
		$this->assertSame( 'the_content', $filters[0]['hook'] );
		$this->assertSame( 'the_title', $filters[1]['hook'] );
	}

	/**
	 * Test run method returns void.
	 */
	public function test_run_method_returns_void(): void {
		Functions\when( 'add_action' )->justReturn( true );
		Functions\when( 'add_filter' )->justReturn( true );

		$result = $this->loader->run();

		$this->assertNull( $result );
	}

	/**
	 * Test loader class can be instantiated.
	 */
	public function test_loader_class_can_be_instantiated(): void {
		$this->assertInstanceOf(
			\STOLMC_Service_Tracker\includes\Utils\STOLMC_Service_Tracker_Loader::class,
			$this->loader
		);
	}
}
