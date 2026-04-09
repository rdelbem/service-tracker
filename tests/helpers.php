<?php
/**
 * Test Helper Functions
 *
 * Utility functions for working with private/protected methods and properties,
 * and other common testing operations.
 *
 * @package Service_Tracker\Tests
 */

declare( strict_types=1 );

/**
 * Call a protected/private method of a class.
 *
 * Allows testing of private methods by making them accessible via reflection.
 *
 * @param class-string|object $object_or_class An instantiated object or class name.
 * @param string              $method_name     The method name to call.
 * @param array               $parameters      Array of parameters to pass into the method.
 *
 * @return mixed Method return value.
 *
 * @throws ReflectionException If the method does not exist.
 */
function call_private_method( string|object $object_or_class, string $method_name, array $parameters = [] ): mixed {
	$reflection = new ReflectionClass( is_string( $object_or_class ) ? $object_or_class : get_class( $object_or_class ) );

	if ( is_string( $object_or_class ) ) {
		$object_or_class = $reflection->newInstanceWithoutConstructor();
	}

	$method = $reflection->getMethod( $method_name );
	$method->setAccessible( true );

	return $method->invokeArgs( $object_or_class, $parameters );
}

/**
 * Get the value of a private/protected property or constant from a class.
 *
 * Works with both instance properties and class constants.
 *
 * @param class-string|object $object_or_class An instantiated object or class name.
 * @param string              $property        Property name or constant name.
 *
 * @return mixed Property or constant value.
 *
 * @throws ReflectionException  If the property does not exist.
 * @throws LogicException       If trying to get non-static property from class name.
 */
function get_private_property( string|object $object_or_class, string $property ): mixed {
	$reflection = new ReflectionClass( is_string( $object_or_class ) ? $object_or_class : get_class( $object_or_class ) );

	// Check if it's a class property.
	if ( $reflection->hasProperty( $property ) ) {
		$reflection_property = $reflection->getProperty( $property );
		$reflection_property->setAccessible( true );

		// Handle static properties.
		if ( $reflection_property->isStatic() ) {
			return $reflection_property->getValue();
		}

		// Non-static properties require an object instance.
		if ( is_string( $object_or_class ) ) {
			throw new LogicException(
				sprintf(
					'Getting a non-static property "%s" from a class name without an object instance is not allowed.',
					$property
				)
			);
		}

		return $reflection_property->getValue( $object_or_class );
	}

	// If not a property, try to get it as a constant.
	return $reflection->getConstant( $property );
}

/**
 * Set the value of a private/protected property on an object or class.
 *
 * Works with both instance properties and static class properties.
 *
 * @param class-string|object $object_or_class An instantiated object or class name.
 * @param string              $property        Property name to set.
 * @param mixed               $value           Value to set.
 *
 * @return void
 *
 * @throws ReflectionException  If the property does not exist.
 * @throws LogicException       If trying to set non-static property on class name.
 */
function set_private_property( string|object $object_or_class, string $property, mixed $value ): void {
	$reflection = new ReflectionClass( is_string( $object_or_class ) ? $object_or_class : get_class( $object_or_class ) );
	$reflection_property = $reflection->getProperty( $property );
	$reflection_property->setAccessible( true );

	// Handle static properties.
	if ( $reflection_property->isStatic() ) {
		$reflection_property->setValue( null, $value );
		return;
	}

	// Non-static properties require an object instance.
	if ( is_string( $object_or_class ) ) {
		throw new LogicException(
			sprintf(
				'Setting a non-static property "%s" on a class name without an object instance is not allowed.',
				$property
			)
		);
	}

	$reflection_property->setValue( $object_or_class, $value );
}

/**
 * Create a mock object without calling the constructor.
 *
 * Useful for testing classes where the constructor has side effects
 * or requires complex dependencies.
 *
 * @template T of object
 * @param class-string<T> $class_name The class name to instantiate.
 *
 * @return T The instantiated object without constructor side effects.
 */
function create_instance_without_constructor( string $class_name ): object {
	$reflection = new ReflectionClass( $class_name );
	return $reflection->newInstanceWithoutConstructor();
}

/**
 * Get all private properties of a class as an associative array.
 *
 * @param class-string|object $object_or_class An instantiated object or class name.
 *
 * @return array<string, mixed> Array of property names and their values.
 */
function get_all_private_properties( string|object $object_or_class ): array {
	$reflection = new ReflectionClass( is_string( $object_or_class ) ? $object_or_class : get_class( $object_or_class ) );
	$properties = $reflection->getProperties( ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PROTECTED );

	$result = [];
	foreach ( $properties as $property ) {
		$property->setAccessible( true );

		if ( $property->isStatic() ) {
			$result[ $property->getName() ] = $property->getValue();
		} elseif ( is_object( $object_or_class ) ) {
			$result[ $property->getName() ] = $property->getValue( $object_or_class );
		}
	}

	return $result;
}

/**
 * Check if a class has a specific method (including private/protected).
 *
 * @param class-string|object $object_or_class An instantiated object or class name.
 * @param string              $method_name     The method name to check.
 *
 * @return bool True if the method exists, false otherwise.
 */
function has_method( string|object $object_or_class, string $method_name ): bool {
	$reflection = new ReflectionClass( is_string( $object_or_class ) ? $object_or_class : get_class( $object_or_class ) );
	return $reflection->hasMethod( $method_name );
}

/**
 * Check if a class has a specific property (including private/protected).
 *
 * @param class-string|object $object_or_class An instantiated object or class name.
 * @param string              $property        The property name to check.
 *
 * @return bool True if the property exists, false otherwise.
 */
function has_property( string|object $object_or_class, string $property ): bool {
	$reflection = new ReflectionClass( is_string( $object_or_class ) ? $object_or_class : get_class( $object_or_class ) );
	return $reflection->hasProperty( $property );
}

/**
 * Get a class constant value.
 *
 * @param class-string|object $object_or_class An instantiated object or class name.
 * @param string              $constant        The constant name.
 *
 * @return mixed The constant value.
 *
 * @throws ReflectionException If the constant does not exist.
 */
function get_class_constant( string|object $object_or_class, string $constant ): mixed {
	$reflection = new ReflectionClass( is_string( $object_or_class ) ? $object_or_class : get_class( $object_or_class ) );
	return $reflection->getConstant( $constant );
}

/**
 * Create a closure that binds to an object instance, allowing access to $this.
 *
 * Useful for testing methods that need object context.
 *
 * @param object  $object   The object to bind to.
 * @param Closure $closure  The closure to bind.
 * @param string  $new_scope The class scope to bind to (defaults to object's class).
 *
 * @return Closure The bound closure.
 */
function bind_closure_to_object( object $object, Closure $closure, ?string $new_scope = null ): Closure {
	return Closure::bind( $closure, $object, $new_scope ?? get_class( $object ) );
}
