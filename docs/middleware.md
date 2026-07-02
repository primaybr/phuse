# Route Middleware

Phuse has two middleware mechanisms that solve different problems - this doc covers the
Router's lightweight per-route/group middleware. For the `Core\Middleware\MiddlewareInterface` +
`MiddlewareStack` system (used for app-wide request/response processing with a `process(callable
$next)` pipeline), see [Dependency Injection & Middleware](dependency-injection-middleware.md).

## Router Middleware

Route-level middleware registered via `Core\Router` are plain callables, not classes implementing
an interface. Each one runs in order before the route's controller action, and can halt the
request by returning a truthy value (which is echoed as the response and stops dispatch).

```php
$requireAdmin = function () {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        return 'Forbidden';
    }
    return null; // falsy -> continue to the next middleware / the controller
};

$router->get('/admin/users', 'Admin\UsersController', 'index', [$requireAdmin]);
```

### Applying Middleware to a Group of Routes

`Router::group()` applies one or more middleware callables to every route registered inside its
callback, merged with any middleware the individual route also declares:

```php
$router->group([$requireAdmin], function () use ($router) {
    $router->get('/admin/dashboard', 'Admin\DashboardController', 'index');
    $router->get('/admin/settings', 'Admin\SettingsController', 'index');
});
```

### How It's Evaluated

At match time (`Router::match()`), the matched route's own middleware array is merged with any
active group middleware, then run in order via `handleMiddleware()`:

```php
foreach ($middleware as $mw) {
    if (is_callable($mw)) {
        $response = $mw();
        if ($response) {
            return $response; // halts - the controller action never runs
        }
    }
}
```

Keep route middleware focused and fast - it runs on every matching request, before route caching
or controller instantiation happen.

## Built-in MiddlewareStack Implementations (v1.2.8+)

`Core\Middleware\` ships four ready-to-use `MiddlewareInterface` implementations for the
`MiddlewareStack` pipeline (see [dependency-injection-middleware.md](dependency-injection-middleware.md)
for how to build a stack). Each is added the same way:

```php
use Core\Middleware\MiddlewareStack;
use Core\Middleware\TrimStrings;
use Core\Middleware\ConvertEmptyStringsToNull;
use Core\Middleware\LogRequest;
use Core\Middleware\RateLimitMiddleware;

$stack = new MiddlewareStack(function () {
    $routes = require Path::CONFIG . 'Routes.php';
    return $routes->run();
});

$stack->add(new TrimStrings());
$stack->add(new ConvertEmptyStringsToNull());
$stack->add(new LogRequest());
$stack->add(new RateLimitMiddleware(key: 'global:' . (new \Core\Http\Client())->getIpAddress(), maxAttempts: 120, windowSeconds: 60));

$stack->process();
```

### `TrimStrings`

Trims leading/trailing whitespace from every string value in `$_GET` and `$_POST` (recursively,
including nested arrays) before the request reaches the application.

### `ConvertEmptyStringsToNull`

Converts every empty-string value in `$_GET` and `$_POST` to `null` - useful ahead of database
inserts where an empty string and "no value" should be treated the same way.

### `LogRequest`

Logs the method/URI on entry and exit (with elapsed time in ms) to a dedicated `requests` log via
`Core\Log`. Accepts an optional `Log` instance in its constructor for testing/injection.

### `RateLimitMiddleware`

Limits how many times a keyed action may run within a rolling time window, backed by
`Core\Cache\CacheManager` for counter storage:

```php
new RateLimitMiddleware(
    key: "login:{$ipAddress}",   // unique identifier for what's being limited
    maxAttempts: 5,               // allowed attempts per window
    windowSeconds: 300,           // 5-minute window
    cacheName: 'rate_limit'       // named CacheManager instance for counter storage
);
```

When the limit is exceeded it does **not** call `$next()` - it sets a `429` status, a `Retry-After`
header, and returns `'Too Many Requests'` directly. Combine with `RateLimitMiddleware`'s IP-derived
key and `Core\Http\Client::getIpAddress()` (see [security.md](security.md)) to rate-limit by real
client IP even behind a trusted proxy.

## Choosing Between the Two Systems

- **Route middleware** (this doc): quick per-route/group guards - auth checks, feature flags,
  simple redirects - registered inline where the route itself is defined.
- **`MiddlewareStack`** ([dependency-injection-middleware.md](dependency-injection-middleware.md)):
  app-wide pipelines that need to run for every request regardless of which route matches, wrap
  the response after the handler runs, or need constructor-injected dependencies from the
  `Container`.
