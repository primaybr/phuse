# Testing in Phuse Framework

Phuse includes comprehensive testing support using PHPUnit to ensure code quality, reliability, and maintainability. This guide covers setting up and writing tests for your Phuse applications.

## Overview

Testing is crucial for:
- Verifying that code works as expected
- Preventing regressions when making changes
- Ensuring compatibility across PHP versions
- Improving code quality through Test-Driven Development (TDD)

## PHPUnit Setup

### Installation

PHPUnit is configured via Composer. If you haven't installed PHPUnit yet:

```bash
composer require --dev phpunit/phpunit ^10.0
```

### Configuration

Phuse includes a `phpunit.xml` configuration file that:
- Specifies the test bootstrap file
- Configures test suites and coverage reporting
- Sets up the testing environment

### Running Tests

```bash
# Run all tests
php vendor/bin/phpunit

# Run tests with coverage (requires Xdebug or PCOV)
php vendor/bin/phpunit --coverage-html tests/coverage/html

# Run specific test file
php vendor/bin/phpunit tests/Core/ControllerTest.php

# Run tests in verbose mode
php vendor/bin/phpunit --verbose
```

## Test Structure

Tests are organized in the `tests/` directory, mirroring the `Core/` and `App/` structure:

```
tests/
├── Core/
│   ├── ControllerTest.php
│   ├── Security/
│   │   └── CSRFTest.php
│   ├── ContainerTest.php
│   └── Middleware/
│       └── MiddlewareStackTest.php
└── App/
    └── (Your application tests)
```

## Writing Tests

### Basic Test Structure

```php
<?php

declare(strict_types=1);

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Core\YourClass;

class YourClassTest extends TestCase
{
    public function testSomething(): void
    {
        // Arrange
        $object = new YourClass();

        // Act
        $result = $object->someMethod();

        // Assert
        $this->assertEquals('expected', $result);
    }
}
```

### Testing Controllers

Controllers can be tested by instantiating them and checking their properties and methods:

```php
public function testControllerHasCSRF(): void
{
    $controller = new Controller();
    $this->assertInstanceOf(CSRF::class, $controller->csrf);
}
```

### Testing Services with Dependency Injection

```php
public function testServiceWithDependencies(): void
{
    $container = new Container();

    // Register dependencies
    $container->set('dependency', function() {
        return new DependencyClass();
    });

    $container->set('service', function($container) {
        return new YourService($container->get('dependency'));
    });

    $service = $container->get('service');
    $this->assertInstanceOf(YourService::class, $service);
}
```

### Testing Middleware

```php
public function testMiddlewareExecution(): void
{
    $executed = false;

    $middleware = new class implements MiddlewareInterface {
        public function process(callable $next): mixed {
            $GLOBALS['executed'] = true;
            return $next();
        }
    };

    $stack = new MiddlewareStack(function() {
        return 'result';
    });

    $stack->add($middleware);
    $result = $stack->process();

    $this->assertTrue($executed);
    $this->assertEquals('result', $result);
}
```

## Best Practices

### 1. Use Descriptive Test Names

```php
// Good
public function testUserCanBeCreatedWithValidData(): void

// Avoid
public function testCreateUser(): void
```

### 2. Follow Arrange-Act-Assert Pattern

```php
public function testUserRegistration(): void
{
    // Arrange - Set up test data and objects
    $userService = new UserService();

    // Act - Perform the action being tested
    $result = $userService->register($validData);

    // Assert - Verify the result
    $this->assertTrue($result->success);
}
```

### 3. Use Data Providers for Multiple Test Cases

```php
/**
 * @dataProvider validUserDataProvider
 */
public function testValidUserCreation(array $data): void
{
    $user = new User($data);
    $this->assertInstanceOf(User::class, $user);
}

public function validUserDataProvider(): array
{
    return [
        [['name' => 'John', 'email' => 'john@example.com']],
        [['name' => 'Jane', 'email' => 'jane@example.com']],
    ];
}
```

### 4. Mock External Dependencies

For testing components that depend on external services (databases, APIs), use mocks:

```php
public function testServiceWithDatabase(): void
{
    $databaseMock = $this->createMock(Database::class);
    $databaseMock->method('query')->willReturn([]);

    $service = new YourService($databaseMock);
    // Test without hitting real database
}
```

### 5. Test Edge Cases and Error Conditions

```php
public function testInvalidInputThrowsException(): void
{
    $this->expectException(InvalidArgumentException::class);
    new User(['invalid' => 'data']);
}
```

## Coverage Reporting

Generate coverage reports to see which parts of your code are tested:

```bash
phpunit --coverage-html tests/coverage/html
```

The report will be in `tests/coverage/html/index.html`.

## Continuous Integration

Set up PHPUnit in your CI pipeline:

```yaml
# .github/workflows/tests.yml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: phpunit
```

## Common Testing Patterns

### Testing CSRF Protection

```php
public function testCSRFTokenGeneration(): void
{
    $_SESSION = [];
    $csrf = new CSRF();

    $token = $csrf->generateToken();
    $this->assertNotEmpty($token);
    $this->assertTrue($csrf->validateToken($token));
}
```

### Testing Middleware Stacks

```php
public function testMiddlewareStackProcessesCorrectly(): void
{
    $stack = new MiddlewareStack(function() {
        return 'final';
    });

    $middleware = new TestMiddleware();
    $stack->add($middleware);

    $result = $stack->process();
    $this->assertEquals('final', $result);
}
```

## Troubleshooting

### Common Issues

1. **Session Issues in Tests**: Sessions don't persist between tests. Use `$_SESSION` directly or mock the Session class.

2. **Database Tests**: Use in-memory databases or proper fixtures for database tests.

3. **File System Tests**: Mock file operations or use temporary directories.

4. **Time-Dependent Tests**: Mock time functions for tests that depend on current time.

### Debugging Tests

Use PHPUnit's verbose output and debugging features:

```bash
phpunit --debug
phpunit --verbose
```

## Next Steps

- Add integration tests for full request/response cycles
- Set up database testing with migrations
- Implement behavior-driven development (BDD) with tools like Behat
- Add performance tests for critical paths

Testing is an ongoing process. Start with unit tests for new features, then add integration tests as your application grows.
