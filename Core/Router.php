<?php

declare(strict_types=1);

namespace Core;

use Core\Exception\Error as Error;
use Core\Folder as Folder;

class Router
{
    private array $routes = [];
    private array $action = [];
    private array $method = [];
    private $error;

    public function __construct()
    {
        $this->error = new Error();
    }

    public function add(string $requestMethod, string $pattern, callable|string $controller, string $action = 'index'): void
    {
        $pattern = match ($pattern) {
            '/' => '~^\/' . basename(strtolower(ROOT)) . '\/$~',
            default => '~^\/' . str_replace('/', '\/', basename(strtolower(ROOT)) . $pattern) . '$~',
        };

        //debug($action);
        $this->routes[$pattern] = $controller;
        $this->action[$pattern] = $action;
        $this->method[$pattern] = $requestMethod;
    }

    public function get(string $pattern, callable|string $controller, string $action = 'index'): void
    {
        $this->add('GET', $pattern, $controller, $action);
    }

    public function post(string $pattern, callable|string $controller, string $action = 'index'): void
    {
        $this->add('POST', $pattern, $controller, $action);
    }

    public function put(string $pattern, callable|string $controller, string $action = 'index'): void
    {
        $this->add('PUT', $pattern, $controller, $action);
    }

    public function delete(string $pattern, callable|string $controller, string $action = 'index'): void
    {
        $this->add('DELETE', $pattern, $controller, $action);
    }

    // This function runs the application and handles the routing
	public function run(): void
	{
		// Parse the URL from the request and get the path
		$url = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
		// Get the request method (GET, POST, etc.)
		$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

		// Split the URL into segments by the slash character
		$segments = explode('/', $url);

		// If the second segment is not set, use the basename of the root directory as the default
		$segments[1] ??= basename(strtolower(ROOT));

		// If the second segment is not the same as the basename of the root directory, prepend it to the URL
		$url = ($segments[1] !== basename(strtolower(ROOT))) ? DS . basename(strtolower(ROOT)) . $url : $url;
		
		$config = (new Config())->get();
		
		// Check if the request needs to be redirected to HTTPS
		$redirect = match (true) {
			// If the server name is not an IP address, not localhost, and not empty, and the request is not HTTPS
			(bool)ip2long($_SERVER['SERVER_NAME']) != 1 && $_SERVER['SERVER_NAME'] != 'localhost' && !empty($_SERVER['SERVER_NAME']) && (isset($config->https) && $config->https === false)
				&& !(isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') => 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
			// Otherwise, no redirection is needed
			default => null,
		};

		// If there is a redirect URL, send a 301 response and redirect the request
		if ($redirect) {
			header('HTTP/1.1 301 Moved Permanently');
			header('location: '.$redirect);
			exit();
		}

		// Loop through the routes defined in the application
		foreach ($this->routes as $pattern => $controller) {
			// If the URL matches the pattern of the route
			if (preg_match($pattern, $url, $matches)) {
				// If the request method is not allowed by the route, show a 404 error
				if (stripos($this->method[$pattern], $requestMethod) === false) {
					$this->error->show(404);
				}
				// Remove the first element of the matches array, which is the whole URL
				array_shift($matches);
				
				// If the controller and the action are both strings and not empty
				if (is_string($controller) && is_string($this->action[$pattern]) && !empty($this->action[$pattern])) {
					// Replace the slashes in the controller name with backslashes and prepend the namespace
					$controller = str_replace('/', '\\', Folder\Path::CONTROLLERS . $controller);
					// Create a new instance of the controller class
					$controller = new $controller();
					// Create a handler array with the controller object and the action name
					$handler = [$controller, $this->action[$pattern]];

					// If the controller has the action method and it is callable
					if (method_exists($controller, $this->action[$pattern]) && is_callable($handler)) {
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


				// Exit the loop and the function
				exit;
			}

		}

		// If no route matches the URL, show a 404 error
		$this->error->show(404);
	}
}
