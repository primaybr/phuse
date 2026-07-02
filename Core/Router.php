<?php

declare(strict_types=1);

namespace Core;

use Core\Exception\Error as Error;
use Core\Folder as Folder;
use Core\Log as Log;

/**
 * URL separator constant - URLs should always use forward slashes regardless of OS
 */
const URL_SEPARATOR = '/';

/**
 * Class Router
 *
 * Handles the routing of HTTP requests to the appropriate controllers and actions.
 *
 * @package Core
 * @author  Prima Yoga
 */
class Router
{
    /**
     * @var array $routes The routes registered with the router.
     */
    private array $routes = [];
    /**
     * @var array $actions The actions associated with each route.
     */
    private array $actions = [];
    /**
     * @var array $methods The HTTP methods associated with each route.
     */
    private array $methods = [];
    /**
     * @var array $middlewares The middleware associated with each route.
     */
    private array $middlewares = [];
    /**
     * @var array $middlewareGroups The middleware groups.
     */
    private array $middlewareGroups = []; // To hold middleware groups
    /**
     * @var Error $error The error handler instance.
     */
    private Error $error;
    /**
     * @var Log $log The log instance.
     */
    private Log $log; // Log instance
    /**
     * @var array $cachedRoutes The cached routes.
     */
    private array $cachedRoutes = [];
    /**
     * @var bool $routesCacheDirty Whether routes changed since the cache file was last written.
     */
    private bool $routesCacheDirty = false;
    /**
     * @var array<string,string> $rawPatterns Maps a route key to its original (pre-regex) pattern, for reverse routing.
     */
    private array $rawPatterns = [];
    /**
     * @var string|null $lastRouteKey The route key most recently registered via add(), so name() knows what to bind to.
     */
    private ?string $lastRouteKey = null;
    /**
     * @var array<string,string> $routeNames Maps a route name to its route key.
     */
    private array $routeNames = [];

    /**
     * Router constructor.
     *
     * Initializes the router instance and sets up error handling and logging.
     */
    public function __construct()
    {
        $this->error = new Error();
        $this->log = new Log(); // Initialize the log
        $this->loadRoutes(); // Load cached routes if available
    }

    /**
     * Loads the cached routes if available.
     *
     * @return void
     */
    private function loadRoutes(): void
    {
        // Load routes from cache file
        $cacheFile = Folder\Path::CACHE . 'routes.cache';
        if (file_exists($cacheFile)) {
            $cachedRoutes = file_get_contents($cacheFile);
            if ($cachedRoutes) {
                $this->cachedRoutes = unserialize($cachedRoutes);
                $this->routes = $this->cachedRoutes['routes'];
                $this->actions = $this->cachedRoutes['actions'];
                $this->methods = $this->cachedRoutes['methods'];
                $this->middlewares = $this->cachedRoutes['middlewares'];
            }
        }
    }

    /**
     * Adds a new route to the router.
     *
     * @param string $requestMethod The HTTP method (GET, POST, etc.).
     * @param string $pattern The URL pattern for the route.
     * @param callable|string $controller The controller to handle the route.
     * @param string $action The action method to call on the controller.
     * @param array $middleware Optional middleware for the route.
     * @return static Fluent - chain ->name() to register a name for reverse routing.
     */
    public function add(string $requestMethod, string $pattern, callable|string $controller, string $action = 'index', array $middleware = []): static
    {
        $preparedPattern = $this->preparePattern($pattern);
        $routeKey = $this->getRouteKey($preparedPattern, $requestMethod);

        // Check if the route already exists
        if (!isset($this->routes[$routeKey])) {
            $this->routes[$routeKey] = $controller;
            $this->actions[$routeKey] = $action;
            $this->methods[$routeKey] = $requestMethod;
            $this->middlewares[$routeKey] = $middleware;

            $this->cachedRoutes = [
                'routes' => $this->routes,
                'actions' => $this->actions,
                'methods' => $this->methods,
                'middlewares' => $this->middlewares,
            ];

            // Defer the cache file write until flushRouteCache() runs once, instead
            // of writing the file after every single route registration.
            $this->routesCacheDirty = true;
        }

        // Tracked unconditionally (cheap, in-memory only) so name()/route() keep
        // working even on requests where the route above was skipped because it
        // was already present from a loaded route cache.
        $this->rawPatterns[$routeKey] = $pattern;
        $this->lastRouteKey = $routeKey;

        return $this;
    }

    /**
     * Names the most recently registered route, for reverse URL generation via route().
     *
     * @param string $name The route name.
     * @return static
     */
    public function name(string $name): static
    {
        if ($this->lastRouteKey !== null) {
            $this->routeNames[$name] = $this->lastRouteKey;
        }

        return $this;
    }

    /**
     * Builds the URL for a named route, substituting params into its capture groups in order.
     *
     * @param string $name The route name registered via name().
     * @param array $params Positional values substituted into the route's capture groups, in order.
     * @return string The generated URL path.
     */
    public function route(string $name, array $params = []): string
    {
        if (!isset($this->routeNames[$name])) {
            throw new \InvalidArgumentException("No route registered with name \"$name\".");
        }

        $routeKey = $this->routeNames[$name];
        $pattern = $this->rawPatterns[$routeKey] ?? explode('@', $routeKey)[0];

        $params = array_values($params);
        $i = 0;
        $path = preg_replace_callback('/\([^()]*\)/', function () use ($params, &$i) {
            return isset($params[$i]) ? (string) $params[$i++] : '';
        }, $pattern);

        return $this->buildBaseUrl() . $path;
    }

    /**
     * Builds the base URL prefix (empty for domain access, "/{baseName}" for subdirectory access).
     *
     * @return string
     */
    private function buildBaseUrl(): string
    {
        $baseName = basename(strtolower(ROOT));
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $isDomainAccess = !str_contains($host, 'localhost') && !str_contains($host, '127.0.0.1');

        return $isDomainAccess ? '' : URL_SEPARATOR . $baseName;
    }

    /**
     * Writes the route cache file if routes changed since the last write.
     *
     * Registration typically adds many routes in a row (once per app bootstrap);
     * calling this once after registration avoids a redundant file write per route.
     *
     * @return void
     */
    public function flushRouteCache(): void
    {
        if (!$this->routesCacheDirty) {
            return;
        }

        $this->cacheRoutes();
        $this->routesCacheDirty = false;
    }

    /**
     * Caches the routes to a file.
     *
     * @return void
     */
    private function cacheRoutes(): void
    {
        if(!is_dir(Folder\Path::CACHE)) {
            mkdir(Folder\Path::CACHE, 0777, true);
        }

        $cacheFile = Folder\Path::CACHE . 'routes.cache';
        file_put_contents($cacheFile, serialize($this->cachedRoutes));
    }

    /**
     * Adds a new GET route to the router.
     *
     * @param string $pattern The URL pattern for the route.
     * @param callable|string $controller The controller to handle the route.
     * @param string $action The action method to call on the controller.
     * @param array $middleware Optional middleware for the route.
     * @return static Fluent - chain ->name() to register a name for reverse routing.
     */
    public function get(string $pattern, callable|string $controller, string $action = 'index', array $middleware = []): static
    {
        return $this->add('GET', $pattern, $controller, $action, $middleware);
    }

    /**
     * Adds a new POST route to the router.
     *
     * @param string $pattern The URL pattern for the route.
     * @param callable|string $controller The controller to handle the route.
     * @param string $action The action method to call on the controller.
     * @param array $middleware Optional middleware for the route.
     * @return static Fluent - chain ->name() to register a name for reverse routing.
     */
    public function post(string $pattern, callable|string $controller, string $action = 'index', array $middleware = []): static
    {
        return $this->add('POST', $pattern, $controller, $action, $middleware);
    }

    /**
     * Adds a new PUT route to the router.
     *
     * @param string $pattern The URL pattern for the route.
     * @param callable|string $controller The controller to handle the route.
     * @param string $action The action method to call on the controller.
     * @return static Fluent - chain ->name() to register a name for reverse routing.
     */
    public function put(string $pattern, callable|string $controller, string $action = 'index'): static
    {
        return $this->add('PUT', $pattern, $controller, $action);
    }

    /**
     * Adds a new DELETE route to the router.
     *
     * @param string $pattern The URL pattern for the route.
     * @param callable|string $controller The controller to handle the route.
     * @param string $action The action method to call on the controller.
     * @return static Fluent - chain ->name() to register a name for reverse routing.
     */
    public function delete(string $pattern, callable|string $controller, string $action = 'index'): static
    {
        return $this->add('DELETE', $pattern, $controller, $action);
    }

    /**
     * Method to define middleware groups
     *
     * @param array $middleware The middleware to handle.
     * @param callable $callback The callback to execute.
     * @return void
     */
    public function group(array $middleware, callable $callback): void
    {
        $this->middlewareGroups[] = $middleware; // Store the middleware group
        call_user_func($callback); // Execute the callback to add routes
        array_pop($this->middlewareGroups); // Remove the group after execution
    }

    /**
     * Runs the router and dispatches the request to the appropriate controller and action.
     *
     * @return void
     */
    public function run(): void
    {
        $this->flushRouteCache();

        $url = $this->getUrl();
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $this->redirectIfNeeded();

        $this->match($url, $requestMethod);
    }

    /**
     * Matches the request to a route.
     *
     * @param string $requestUri The request URI.
     * @param string $requestMethod The request method.
     * @return void
     */
    public function match(string $requestUri, string $requestMethod): void
    {
        $startTime = microtime(true); // Start profiling

        // Iterate through registered routes to find a match
        foreach ($this->routes as $routeKey => $controller) {
            $pattern = explode('@', $routeKey)[0]; // Get the pattern from the route key
            if (preg_match($pattern, $requestUri, $matches)) {
                if (stripos($this->methods[$routeKey], $requestMethod) === false) {
                    continue; // Try next route if method doesn't match
                }

                array_shift($matches);

                // Execute middleware if any
                $middleware = $this->middlewares[$routeKey] ?? [];
                if (!empty($this->middlewareGroups)) {
                    // Merge group middleware with route-specific middleware
                    $middleware = array_merge($this->middlewareGroups, $middleware);
                }
                $response = $this->handleMiddleware($middleware);
                if ($response) {
                    echo $response; // Output response if middleware halts execution
                    return;
                }

                $this->log->write("Accessing route: $requestUri with method: $requestMethod");
                $endTime = microtime(true); // End profiling
                $this->log->write('Route matching took: ' . ($endTime - $startTime) . ' seconds');

                // Dispatch the request to the appropriate controller and action
                $this->handleRequest($controller, $matches, $routeKey);
                return; // Exit after handling the request
            }
        }

        $this->log->write("No matching route found for URL: $requestUri");
        $endTime = microtime(true); // End profiling
        $this->log->write('No matching route took: ' . ($endTime - $startTime) . ' seconds');
        

        // Handle the case where no route matches
        http_response_code(404);
        $this->error->show(404);
        exit;
    }

    /**
     * Handles middleware for a given route.
     *
     * @param array $middleware The middleware to handle.
     * @return string|null The response from the middleware, or null if no response is returned.
     */
    private function handleMiddleware(array $middleware): ?string
    {
        foreach ($middleware as $mw) {
            if (is_callable($mw)) {
                $response = $mw(); // Call middleware
                if ($response) {
                    return $response; // Return response if middleware halts execution
                }
            }
        }
        return null; // No response from middleware
    }

    /**
     * Handles the request and dispatches it to the appropriate controller and action.
     *
     * @param callable|string $controller The controller to handle the request.
     * @param array $matches The matches from the route pattern.
     * @param string $pattern The route pattern.
     * @return void
     */
    private function handleRequest(callable|string $controller, array $matches, string $pattern): void
    {
        // If the controller and the action are both strings and not empty
		if (is_string($controller) && is_string($this->actions[$pattern]) && !empty($this->actions[$pattern])) {

            // If the controller is already a FQCN (e.g. "App\Modules\Blog\Controllers\Admin\PostsController")
            // use it as-is; otherwise prepend the default App\Controllers\ namespace.
            if (!str_starts_with($controller, 'App\\')) {
                $controllerPath = str_replace(ROOT, '', Folder\Path::CONTROLLERS);
                $controller = str_replace('/', '\\', $controllerPath . $controller);
            }
			// Create a new instance of the controller class
			$controller = new $controller();
			// Create a handler array with the controller object and the action name
			$handler = [$controller, $this->actions[$pattern]];
			// If the controller has the action method and it is callable
			if (method_exists($controller, $this->actions[$pattern]) && is_callable($handler)) {
				// Route captures are always passed as strings (IDs are UUIDs, not ints)
				$matches = array_map('strval', $matches);
				$handler(...$matches);
			} else {
				// If the controller does not have the action method or it is not callable, show a 404 error
				$this->error->show(404);
			}
		} else {
			// If the controller is not a string, assume it is a callable function and call it with the matches array as arguments
			$controller(...array_values($matches));
		}
    }

    /**
     * Prepares the pattern for a given route.
     *
     * @param string $pattern The URL pattern for the route.
     * @return string The prepared pattern.
     */
    private function preparePattern(string $pattern): string
    {
        $baseName = basename(strtolower(ROOT));
        
        // Check if we're accessing via domain (phuse.test) or subdirectory (localhost/phuse)
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $isDomainAccess = !str_contains($host, 'localhost') && !str_contains($host, '127.0.0.1');
        
        if ($isDomainAccess) {
            // Domain access: patterns don't include base name, trailing slash optional
            return match ($pattern) {
                '/' => '~^' . URL_SEPARATOR . '?$~',
                default => '~^' . $pattern . URL_SEPARATOR . '?$~',
            };
        } else {
            // Subdirectory access: include base name, trailing slash optional
            return match ($pattern) {
                '/' => '~^' . URL_SEPARATOR . $baseName . URL_SEPARATOR . '?$~',
                default => '~^' . URL_SEPARATOR . $baseName . $pattern . URL_SEPARATOR . '?$~',
            };
        }
    }

    /**
     * Gets the URL for the current request.
     *
     * @return string The URL for the current request.
     */
    private function getUrl(): string
    {
        $url = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $url = ($url != '/' && $_SERVER['HTTP_HOST'] != 'localhost') ? rtrim($url, '/') : $url;
        
        // Split the URL into segments by the slash character
        $segments = explode('/', $url);
        $baseName = basename(strtolower(ROOT));

        // Check if we're accessing via domain (phuse.test) or subdirectory (localhost/phuse)
        $isDomainAccess = !isset($segments[1]) || $segments[1] !== $baseName;
        
        if ($isDomainAccess) {
            // Domain access: use URL as-is (no base name needed)
            $url = $url;
        } else {
            // Subdirectory access: URL already contains base name, use as-is
            $url = $url;
        }

        return $url;
    }

    /**
     * Redirects the request to HTTPS if necessary.
     *
     * @return void
     */
    private function redirectIfNeeded(): void
    {
        $config = (new Config())->get();
        // Check if the request needs to be redirected to HTTPS
		$redirect = match (true) {
			// If the server name is not an IP address, not localhost, and not empty, and the request is not HTTPS
			(bool)ip2long($_SERVER['SERVER_NAME']) != 1 && $_SERVER['SERVER_NAME'] != 'localhost' && !empty($_SERVER['SERVER_NAME']) && (isset($config->https) && $config->https === false)
				&& !(isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') => 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
			// Otherwise, no redirection is needed
			default => null,
		};

        if ($redirect) {
            $this->log->write("Redirecting to: " . $redirect);
            header('HTTP/1.1 301 Moved Permanently');
            header('location: ' . $redirect);
            exit;
        } else {
            $this->log->write("No redirect needed");
        }
    }

    /**
     * Gets the route key for a given pattern and request method.
     *
     * @param string $pattern The URL pattern for the route.
     * @param string $method The HTTP method (GET, POST, etc.).
     * @return string The route key.
     */
    private function getRouteKey(string $pattern, string $method): string
    {
        return $pattern . '@' . $method;
    }
}
