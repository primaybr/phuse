<?php

declare(strict_types=1);

namespace Core;

use Core\Exception\Error as Error;
use Core\Folder as Folder;
use Core\Log as Log;

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
     * @return void
     */
    public function add(string $requestMethod, string $pattern, callable|string $controller, string $action = 'index', array $middleware = []): void
    {
        $pattern = $this->preparePattern($pattern);
        $routeKey = $this->getRouteKey($pattern, $requestMethod);
        
        // Check if the route already exists
        if (!isset($this->routes[$routeKey])) {
            $this->routes[$routeKey] = $controller;
            $this->actions[$routeKey] = $action;
            $this->methods[$routeKey] = $requestMethod;
            $this->middlewares[$routeKey] = $middleware;
            
            // Cache routes after adding
            $this->cacheRoutes();
        }
    }

    /**
     * Caches the routes to a file.
     *
     * @return void
     */
    private function cacheRoutes(): void
    {
        // Cache the routes to file
        $this->cachedRoutes = [
            'routes' => $this->routes,
            'actions' => $this->actions,
            'methods' => $this->methods,
            'middlewares' => $this->middlewares,
        ];

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
     * @return void
     */
    public function get(string $pattern, callable|string $controller, string $action = 'index', array $middleware = []): void
    {
        $this->add('GET', $pattern, $controller, $action, $middleware);
    }

    /**
     * Adds a new POST route to the router.
     *
     * @param string $pattern The URL pattern for the route.
     * @param callable|string $controller The controller to handle the route.
     * @param string $action The action method to call on the controller.
     * @param array $middleware Optional middleware for the route.
     * @return void
     */
    public function post(string $pattern, callable|string $controller, string $action = 'index', array $middleware = []): void
    {
        $this->add('POST', $pattern, $controller, $action, $middleware);
    }

    /**
     * Adds a new PUT route to the router.
     *
     * @param string $pattern The URL pattern for the route.
     * @param callable|string $controller The controller to handle the route.
     * @param string $action The action method to call on the controller.
     * @return void
     */
    public function put(string $pattern, callable|string $controller, string $action = 'index'): void
    {
        $this->add('PUT', $pattern, $controller, $action);
    }

    /**
     * Adds a new DELETE route to the router.
     *
     * @param string $pattern The URL pattern for the route.
     * @param callable|string $controller The controller to handle the route.
     * @param string $action The action method to call on the controller.
     * @return void
     */
    public function delete(string $pattern, callable|string $controller, string $action = 'index'): void
    {
        $this->add('DELETE', $pattern, $controller, $action);
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
			
            // Get the relative path of the controller (e.g., "Controllers/Welcome.php")
            $controllerPath = str_replace(ROOT, '', Folder\Path::CONTROLLERS); 
            // Replace the slashes in the controller name with backslashes and prepend the namespace
			$controller = str_replace('/', '\\', $controllerPath . $controller);
			// Create a new instance of the controller class
			$controller = new $controller();
			// Create a handler array with the controller object and the action name
			$handler = [$controller, $this->actions[$pattern]];
			// If the controller has the action method and it is callable
			if (method_exists($controller, $this->actions[$pattern]) && is_callable($handler)) {
				// Call the handler with the matches array as arguments
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
        return match ($pattern) {
            '/' => '~^\/' . basename(strtolower(ROOT)) . '\/$~',
            default => '~^\/' . str_replace('/', '\/', basename(strtolower(ROOT)) . $pattern) . '$~',
        };
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

		// If the second segment is not set, use the basename of the root directory as the default
		$segments[1] ??= basename(strtolower(ROOT));

		// If the second segment is not the same as the basename of the root directory, prepend it to the URL
		$url = ($segments[1] !== basename(strtolower(ROOT))) ? DS . basename(strtolower(ROOT)) . $url : $url;
		

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
            header('HTTP/1.1 301 Moved Permanently');
            header('location: ' . $redirect);
            exit;
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
