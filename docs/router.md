# Routing in Phuse

`Core\Router` maps incoming requests to controller actions. It supports both domain-based
deployments (`https://app.test/users`) and subdirectory deployments (`https://localhost/phuse/users`)
with automatic detection based on `HTTP_HOST`.

## Registering Routes

Routes are typically registered in `Config/Routes.php` against a shared `$router` instance.

```php
$router->get('/users', 'UsersController', 'index');
$router->post('/users/store', 'UsersController', 'store');
$router->put('/users/([a-zA-Z0-9\-]+)/update', 'UsersController', 'update');
$router->delete('/users/([a-zA-Z0-9\-]+)/delete', 'UsersController', 'destroy');
```

- The pattern is a raw regex fragment (no delimiters) - capture groups like `([a-zA-Z0-9\-]+)`
  become positional arguments passed to the action, always as strings.
- The controller is a class name resolved under `App\Controllers\Web\` by default. If it already
  starts with `App\` (e.g. `App\Modules\Blog\Controllers\Admin\PostsController`), it's used as-is -
  this lets module systems register controllers outside the default namespace.
- `$action` defaults to `'index'`.
- An optional `$middleware` array (GET/POST only) is a list of callables run before dispatch -
  see [Middleware](middleware.md) for how these differ from `Core\Middleware\MiddlewareInterface`.

## Route Groups

`group()` applies middleware to every route registered inside its callback:

```php
$router->group(['auth'], function () use ($router) {
    $router->get('/admin/dashboard', 'Admin\DashboardController', 'index');
    $router->get('/admin/settings', 'Admin\SettingsController', 'index');
});
```

Group middleware is merged with any route-specific middleware at match time.

## Named Routes (v1.2.7+)

Chain `->name()` onto any registration to give it a name, then use `route()` to generate a URL
without hardcoding paths in views:

```php
$router->get('/users/([a-zA-Z0-9\-]+)/edit', 'UsersController', 'edit')->name('users.edit');

// Later, anywhere you have access to the router instance:
$url = $router->route('users.edit', [$userId]); // '/phuse/users/<id>/edit' (or '/users/<id>/edit' on domain access)
```

`route()` fills each capture group in the original pattern with the corresponding positional value
from `$params`, in order, then prefixes the result with the base URL (empty for domain access, or
`/{baseName}` for subdirectory access - the same detection `preparePattern()` uses). It throws
`\InvalidArgumentException` if no route was registered under that name.

## Dispatching

`$router->run()` is the normal entry point (called once from `Config/Routes.php` or your bootstrap):

1. Flushes any pending route-cache write (see below).
2. Resolves the current request URL and method.
3. Redirects to HTTPS if configured and not already secure.
4. Matches the URL against registered routes and dispatches to the controller action, or renders a
   404 if nothing matches.

## Route Caching

Every unique route registered via `add()`/`get()`/`post()`/`put()`/`delete()` is kept in memory and
written to `Cache/routes.cache` (a PHP `serialize()` blob) so the next request's constructor can
load routes without re-running `Config/Routes.php`'s registration logic from scratch.

As of v1.2.7, the cache file is written **once per request** (inside `run()`), not once per route
registered - registering 50 routes no longer means 50 separate file writes during a cold cache.

**Invalidating the cache**: there's no automatic invalidation. If you change a route pattern,
controller, or action and see stale behavior, delete `Cache/routes.cache` manually (or wire up a
`Cache::clear()` call in your deploy script) - the router will fall back to fresh registration on
the next request and rewrite the file.

## Troubleshooting

- **404 on a route you just added**: delete `Cache/routes.cache` - it may still hold the
  pre-change route set for that exact pattern+method key.
- **`route()` throws "No route registered with name"**: the `name()` call must chain directly off
  the same `$router` instance used to register the route, and must have actually executed (e.g.
  not skipped inside a conditional) before `route()` is called.
