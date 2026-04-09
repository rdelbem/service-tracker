<?php
/**
 * Test Helpers Test
 *
 * Tests for the test helper functions themselves.
 *
 * @package Service_Tracker
 */

namespace STOLMC_Service_Tracker\Tests\Unit;

/**
 * Sample class for testing helpers.
 */
class Test_Helper_Class {
	private const PRIVATE_CONST = 'private_const_value';
	protected const PROTECTED_CONST = 'protected_const_value';
	
	private static string $private_static_prop = 'static_initial';
	protected static string $protected_static_prop = 'static_initial';
	
	private string $private_prop;
	protected string $protected_prop;
	
	public function __construct( string $private = 'private_initial', string $protected = 'protected_initial' ) {
		$this->private_prop = $private;
		$this->protected_prop = $protected;
	}
	
	private function private_method( string $param1, string $param2 = 'default' ): string {
		return $this->private_prop . ' - ' . $param1 . ' - ' . $param2;
	}
	
	protected function protected_method( int $value ): int {
		return $value * 2;
	}
	
	private static function private_static_method(): string {
		return self::$private_static_prop;
	}
}

/**
 * Test Helpers Test Class.
 *
 * @group   unit
 * @group   helpers
 */
class Helpers_Test extends Unit_TestCase {

	/**
	 * Test call_private_method with instance method.
	 */
	public function test_call_private_method_with_instance(): void {
		$object = new Test_Helper_Class();

		$result = call_private_method( $object, 'private_method', [ 'arg1', 'arg2' ] );

		$this->assertSame( 'private_initial - arg1 - arg2', $result );
	}

	/**
	 * Test call_private_method with default parameter.
	 */
	public function test_call_private_method_with_default_param(): void {
		$object = new Test_Helper_Class();

		$result = call_private_method( $object, 'private_method', [ 'arg1' ] );

		$this->assertSame( 'private_initial - arg1 - default', $result );
	}

	/**
	 * Test call_private_method with class name.
	 */
	public function test_call_private_method_with_class_name(): void {
		$result = call_private_method( Test_Helper_Class::class, 'private_static_method' );

		$this->assertSame( 'static_initial', $result );
	}

	/**
	 * Test call_protected_method.
	 */
	public function test_call_protected_method(): void {
		$object = new Test_Helper_Class();

		$result = call_private_method( $object, 'protected_method', [ 5 ] );

		$this->assertSame( 10, $result );
	}

	/**
	 * Test get_private_property with instance property.
	 */
	public function test_get_private_property_with_instance(): void {
		$object = new Test_Helper_Class( 'custom_private', 'custom_protected' );

		$private_value = get_private_property( $object, 'private_prop' );
		$protected_value = get_private_property( $object, 'protected_prop' );

		$this->assertSame( 'custom_private', $private_value );
		$this->assertSame( 'custom_protected', $protected_value );
	}

	/**
	 * Test get_private_property with private constant.
	 */
	public function test_get_private_property_with_constant(): void {
		$const_value = get_private_property( Test_Helper_Class::class, 'PRIVATE_CONST' );

		$this->assertSame( 'private_const_value', $const_value );
	}

	/**
	 * Test get_private_property with protected constant.
	 */
	public function test_get_private_property_with_protected_constant(): void {
		$const_value = get_private_property( Test_Helper_Class::class, 'PROTECTED_CONST' );

		$this->assertSame( 'protected_const_value', $const_value );
	}

	/**
	 * Test get_private_property with static property.
	 */
	public function test_get_private_property_with_static(): void {
		$value = get_private_property( Test_Helper_Class::class, 'private_static_prop' );

		$this->assertSame( 'static_initial', $value );
	}

	/**
	 * Test set_private_property with instance property.
	 */
	public function test_set_private_property_with_instance(): void {
		$object = new Test_Helper_Class();

		set_private_property( $object, 'private_prop', 'new_private_value' );
		$result = get_private_property( $object, 'private_prop' );

		$this->assertSame( 'new_private_value', $result );
	}

	/**
	 * Test set_private_property with static property.
	 */
	public function test_set_private_property_with_static(): void {
		set_private_property( Test_Helper_Class::class, 'private_static_prop', 'new_static_value' );
		$result = get_private_property( Test_Helper_Class::class, 'private_static_prop' );

		$this->assertSame( 'new_static_value', $result );

		// Reset to original value.
		set_private_property( Test_Helper_Class::class, 'private_static_prop', 'static_initial' );
	}

	/**
	 * Test get_all_private_properties.
	 */
	public function test_get_all_private_properties(): void {
		$object = new Test_Helper_Class( 'private_val', 'protected_val' );

		$props = get_all_private_properties( $object );

		$this->assertArrayHasKey( 'private_prop', $props );
		$this->assertArrayHasKey( 'protected_prop', $props );
		$this->assertSame( 'private_val', $props['private_prop'] );
		$this->assertSame( 'protected_val', $props['protected_prop'] );
	}

	/**
	 * Test has_method.
	 */
	public function test_has_method(): void {
		$object = new Test_Helper_Class();

		$this->assertTrue( has_method( $object, 'private_method' ) );
		$this->assertTrue( has_method( $object, 'protected_method' ) );
		$this->assertTrue( has_method( $object, 'private_static_method' ) );
		$this->assertFalse( has_method( $object, 'non_existent_method' ) );
	}

	/**
	 * Test has_property.
	 */
	public function test_has_property(): void {
		$object = new Test_Helper_Class();

		$this->assertTrue( has_property( $object, 'private_prop' ) );
		$this->assertTrue( has_property( $object, 'protected_prop' ) );
		$this->assertFalse( has_property( $object, 'non_existent_prop' ) );
	}

	/**
	 * Test get_class_constant.
	 */
	public function test_get_class_constant(): void {
		$const = get_class_constant( Test_Helper_Class::class, 'PRIVATE_CONST' );

		$this->assertSame( 'private_const_value', $const );
	}

	/**
	 * Test create_instance_without_constructor.
	 */
	public function test_create_instance_without_constructor(): void {
		$object = create_instance_without_constructor( Test_Helper_Class::class );

		// Object should be created without calling constructor.
		$this->assertInstanceOf( Test_Helper_Class::class, $object );
	}

	/**
	 * Test bind_closure_to_object.
	 */
	public function test_bind_closure_to_object(): void {
		$object = new Test_Helper_Class( 'bound_value' );

		$closure = function () {
			return $this->private_prop;
		};

		$bound_closure = bind_closure_to_object( $object, $closure );

		$this->assertSame( 'bound_value', $bound_closure() );
	}

	/**
	 * Test get_private_property throws exception for non-static from class name.
	 */
	public function test_get_private_property_throws_exception_for_non_static_from_class_name(): void {
		$this->expectException( \LogicException::class );
		$this->expectExceptionMessage( 'Getting a non-static property "private_prop" from a class name without an object instance is not allowed.' );

		get_private_property( Test_Helper_Class::class, 'private_prop' );
	}

	/**
	 * Test set_private_property throws exception for non-static to class name.
	 */
	public function test_set_private_property_throws_exception_for_non_static_to_class_name(): void {
		$this->expectException( \LogicException::class );
		$this->expectExceptionMessage( 'Setting a non-static property "private_prop" on a class name without an object instance is not allowed.' );

		set_private_property( Test_Helper_Class::class, 'private_prop', 'value' );
	}
}
