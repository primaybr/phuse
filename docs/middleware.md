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

## Choosing Between the Two Systems

- **Route middleware** (this doc): quick per-route/group guards - auth checks, feature flags,
  simple redirects - registered inline where the route itself is defined.
- **`MiddlewareStack`** ([dependency-injection-middleware.md](dependency-injection-middleware.md)):
  app-wide pipelines that need to run for every request regardless of which route matches, wrap
  the response after the handler runs, or need constructor-injected dependencies from the
  `Container`.
