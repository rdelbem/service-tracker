# Service Tracker Tests

This directory contains automated tests for the Service Tracker WordPress plugin.

## Directory Structure

```
tests/
├── Unit/                          # Unit tests (isolated, mocked dependencies)
│   ├── Unit_TestCase.php          # Base class for unit tests
│   ├── API_TestCase.php           # Base class for API tests with pre-configured stubs
│   ├── helpers.php                # Test helper functions
│   ├── Helpers_Test.php           # Tests for helper functions
│   ├── Sample_Test.php            # Sample test demonstrating setup
│   ├── Permalink_Validator_Test.php
│   ├── Api_Test.php               # Base API class tests
│   ├── Api_Cases_Test.php         # Cases API tests
│   ├── Api_Progress_Test.php      # Progress API tests
│   ├── Api_Toggle_Test.php        # Toggle API tests
│   └── Api_Users_Test.php         # Users API tests
├── Integration/                   # Integration tests (WordPress environment simulated)
├── bootstrap.php                  # Test bootstrap file
└── helpers.php                    # Global test helper functions
```

## Running Tests

### All Tests
```bash
composer test
```

### Unit Tests Only
```bash
composer test:unit
```

### Integration Tests Only
```bash
composer test:integration
```

### With Coverage
```bash
composer phpunit:coverage
```

### Direct PHPUnit Usage
```bash
./vendor/bin/phpunit
./vendor/bin/phpunit --testsuite Unit
./vendor/bin/phpunit --testsuite Integration
./vendor/bin/phpunit tests/Unit/Api_Cases_Test.php
```

## Test Helper Functions

The `tests/helpers.php` file provides utility functions for working with private/protected methods and properties.

### Available Helpers

#### `call_private_method( $object, $method_name, $parameters = [] )`
Call protected/private methods of a class.

```php
// Call instance method
$result = call_private_method( $object, 'private_method', [ 'arg1', 'arg2' ] );

// Call static method from class name
$result = call_private_method( MyClass::class, 'private_static_method' );
```

#### `get_private_property( $object, $property )`
Get the value of a private/protected property or constant.

```php
// Get instance property
$value = get_private_property( $object, 'private_property' );

// Get class constant
$const = get_private_property( MyClass::class, 'MY_CONSTANT' );

// Get static property
$value = get_private_property( MyClass::class, 'static_property' );
```

#### `set_private_property( $object, $property, $value )`
Set the value of a private/protected property.

```php
// Set instance property
set_private_property( $object, 'private_property', 'new_value' );

// Set static property
set_private_property( MyClass::class, 'static_property', 'new_value' );
```

#### `create_instance_without_constructor( $class_name )`
Create an object without calling the constructor.

```php
$object = create_instance_without_constructor( MyClass::class );
```

#### `get_all_private_properties( $object )`
Get all private properties as an associative array.

```php
$props = get_all_private_properties( $object );
// Returns: ['private_prop' => 'value', 'protected_prop' => 'value']
```

#### `has_method( $object, $method_name )`
Check if a class has a specific method.

```php
if ( has_method( $object, 'private_method' ) ) {
    // Method exists
}
```

#### `has_property( $object, $property )`
Check if a class has a specific property.

```php
if ( has_property( $object, 'private_property' ) ) {
    // Property exists
}
```

#### `get_class_constant( $object, $constant )`
Get a class constant value.

```php
$const = get_class_constant( MyClass::class, 'MY_CONSTANT' );
```

#### `bind_closure_to_object( $object, $closure )`
Bind a closure to an object instance for accessing `$this`.

```php
$closure = function() { return $this->private_property; };
$bound = bind_closure_to_object( $object, $closure );
$value = $bound();
```

## Writing Tests

### Unit Tests

Unit tests extend the `Unit_TestCase` base class and use BrainMonkey to mock WordPress functions:

```php
<?php
namespace STOLMC_Service_Tracker\Tests\Unit;

use Brain\Monkey\Functions;

class MyClass_Test extends Unit_TestCase {
    
    public function test_my_method(): void {
        // Mock WordPress functions
        Functions\when( 'get_option' )->justReturn( 'value' );
        
        // Your test code
        $this->assertSame( 'expected', $result );
    }
}
```

### API Tests

API tests extend `API_TestCase` which provides pre-configured stubs:

```php
<?php
namespace STOLMC_Service_Tracker\Tests\Unit;

use Brain\Monkey\Functions;

class Api_Cases_Test extends API_TestCase {
    
    protected function set_up(): void {
        parent::set_up();
        
        // Inject mock SQL using helper
        $this->mock_sql = $this->create_mock_sql();
        set_private_property( $this->api, 'sql', $this->mock_sql );
    }
    
    public function test_create_case(): void {
        $request = $this->create_mock_request( [], [], $body );
        
        $this->expect_sql_insert( $this->mock_sql, $data, 1 );
        $this->expect_action_hook( 'stolmc_service_tracker_case_created' );
        
        $response = $this->api->create( $request );
        $this->assertSame( 201, $response->get_status() );
    }
}
```

### Using Test Helpers

```php
<?php
namespace STOLMC_Service_Tracker\Tests\Unit;

class My_Class_Test extends Unit_TestCase {
    
    public function test_private_method(): void {
        $object = new My_Class();
        
        // Call private method directly
        $result = call_private_method( $object, 'private_method', [ 'arg1' ] );
        $this->assertSame( 'expected', $result );
    }
    
    public function test_private_property(): void {
        $object = new My_Class();
        
        // Get private property
        $value = get_private_property( $object, 'private_prop' );
        $this->assertSame( 'initial', $value );
        
        // Set private property
        set_private_property( $object, 'private_prop', 'new_value' );
        $new_value = get_private_property( $object, 'private_prop' );
        $this->assertSame( 'new_value', $new_value );
    }
}
```

## Test Conventions

1. **Naming**: Test files should be named `ClassName_Test.php`
2. **Methods**: Test methods should be descriptive: `test_method_does_something()`
3. **Groups**: Use `@group` annotations for categorization
4. **Assertions**: Use specific assertions (`assertSame`, `assertTrue`, etc.)

## Adding New Tests

1. Create a new file in the appropriate test directory
2. Extend the appropriate base class (`Unit_TestCase` or `API_TestCase`)
3. Follow the naming conventions
4. Run `composer test` to verify your tests pass

## CI Integration

The tests can be run in CI environments using:

```bash
composer install --no-interaction
composer test
```

For code coverage in CI:
```bash
composer phpunit:coverage -- --coverage-clover=clover.xml
```
