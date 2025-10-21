# Dependency Injection Container and Middleware System in Phuse

Phuse now includes a Dependency Injection (DI) container and a middleware system to improve code organization, testability, and maintainability. These features allow for better separation of concerns and more flexible request handling.

## Dependency Injection Container

The DI container manages class dependencies and shared instances, enabling automatic dependency resolution and inversion of control.

### Features

- **Automatic Dependency Resolution**: Uses reflection to automatically inject dependencies based on type hints.
- **Shared Instances**: Supports singleton/shared instances for services that should maintain state.
- **Flexible Registration**: Register services by class name, interface, or closure.
- **Type Safety**: Leverages PHP 8.2+ type declarations for better IDE support and error detection.

### Basic Usage

#### Registering Services

```php
// In your application bootstrap or configuration
$container = new Container();

// Register a service as shared (singleton)
$container->set('database', Database::class, true);

// Register a service with a closure for custom instantiation
$container->set('logger', function($container) {
    return new Logger($container->get('database'));
});

// Register an interface with its implementation
$container->set('App\Services\MailerInterface', 'App\Services\SendGridMailer');
```

#### Resolving Services

```php
// Get a service instance
$database = $container->get('database');

// The container will automatically resolve dependencies
class UserController
{
    public function __construct(
        private Database $database,
        private Logger $logger
    ) {}

    // Use $this->database and $this->logger
}
```

### Advanced Usage

#### Custom Service Definitions

```php
// Register with parameters
$container->set('config', function() {
    return new Config(['env' => 'production']);
}, true);

// Override existing services
$container->set('database', CustomDatabase::class);
```

#### Checking Service Registration

```php
if ($container->has('logger')) {
    $logger = $container->get('logger');
}
```

## Middleware System

The middleware system allows you to process HTTP requests and responses through a stack of middleware components, enabling features like authentication, logging, and CSRF protection.

### Features

- **Stack-Based Processing**: Middleware is processed in Last-In-First-Out (LIFO) order.
- **Request/Response Modification**: Middleware can modify requests before and responses after processing.
- **Integration with DI Container**: Middleware can be resolved through the container for dependency injection.
- **Extensible**: Easy to add custom middleware for specific needs.

### Creating Middleware

Middleware must implement the `MiddlewareInterface`:

```php
<?php

use Core\Middleware\MiddlewareInterface;

class AuthenticationMiddleware implements MiddlewareInterface
{
    public function __construct(private UserService $userService) {}

    public function process(callable $next): mixed
    {
        // Pre-processing: Check authentication
        if (!$this->userService->isAuthenticated()) {
            return redirect('/login');
        }

        // Call the next middleware or handler
        $response = $next();

        // Post-processing: Add headers, logging, etc.
        if ($response instanceof Response) {
            $response->setHeader('X-User', $this->userService->getCurrentUser()->name);
        }

        return $response;
    }
}
```

### Registering Middleware

```php
// In your application bootstrap
$middlewareStack = new MiddlewareStack(function() {
    // This is the final handler (e.g., your router)
    $router = new Router();
    return $router->dispatch();
});

// Add middleware (last added runs first)
$middlewareStack->add(new AuthenticationMiddleware($container->get('userService')));
$middlewareStack->add(new CSRFMiddleware());
$middlewareStack->add(new LoggingMiddleware($container->get('logger')));

// Process the request
$middlewareStack->process();
```

### Integration with Phuse

The middleware system is integrated into the `Base` class, which automatically creates a middleware stack with routing as the final handler. You can add middleware in your application bootstrap:

```php
// In your application configuration
class App extends Base
{
    public function run(): void
    {
        $middlewareStack = new MiddlewareStack(function() {
            $routes = require_once Path::CONFIG . 'Routes.php';
            return $routes->run();
        });

        // Add your custom middleware here
        $middlewareStack->add(new App\Middleware\CustomMiddleware());

        $middlewareStack->process();
    }
}
```

## Best Practices

### Dependency Injection

1. **Prefer Interfaces**: Register services by interface to allow easy swapping of implementations.
2. **Use Shared Instances Wisely**: Only use shared instances for stateless services or when you need to maintain state across requests.
3. **Keep Constructors Simple**: Let the container handle complex dependency resolution.
4. **Avoid Circular Dependencies**: Structure your code to prevent circular dependency issues.

### Middleware

1. **Keep Middleware Focused**: Each middleware should have a single responsibility.
2. **Order Matters**: Add middleware in the correct order since they process in reverse order.
3. **Handle Errors**: Implement proper error handling in middleware to prevent breaking the chain.
4. **Performance**: Avoid heavy operations in middleware that runs on every request.

## Examples

### Example DI Container Setup

```php
$container = new Container();

// Core services
$container->set('config', Config::class, true);
$container->set('database', Database::class, true);
$container->set('logger', Logger::class, true);

// Application services
$container->set('userService', UserService::class);
$container->set('mailer', Mailer::class);

// Interfaces
$container->set('App\Contracts\CacheInterface', 'App\Services\RedisCache');
```

### Example Middleware Stack

```php
$middlewareStack = new MiddlewareStack(function() {
    // Final handler - your application logic
    return $this->handleRequest();
});

// Security middleware (runs first)
$middlewareStack->add(new CSRFMiddleware());

// Authentication middleware
$middlewareStack->add(new AuthenticationMiddleware($container->get('userService')));

// Logging middleware (runs last)
$middlewareStack->add(new LoggingMiddleware($container->get('logger')));
```

## Benefits

- **Decoupling**: Services are loosely coupled, making the code more maintainable and testable.
- **Testability**: Easy to mock dependencies in unit tests.
- **Flexibility**: Change implementations without modifying dependent code.
- **Reusability**: Middleware can be reused across different parts of the application.
- **Performance**: Shared instances reduce object creation overhead.

This DI container and middleware system provide a solid foundation for building scalable and maintainable PHP applications with Phuse.
