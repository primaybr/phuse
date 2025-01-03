<?php

declare(strict_types=1);

namespace Core;

use Core\Exception\Error as Error;
use Core\Folder as Folder;
use Core\Log as Logger;

class Router
{
    private array $routes = [];
    private array $action = [];
    private array $method = [];
    private array $middleware = [];
    private Error $error;
    private Logger $logger; // Logger instance

    public function __construct()
    {
        $this->error = new Error();
        $this->logger = new Logger(); // Initialize the logger
    }

    public function add(string $requestMethod, string $pattern, callable|string $controller, string $action = 'index', array $middleware = []): void
    {
        $pattern = $this->preparePattern($pattern);
        $this->routes[$pattern] = $controller;
        $this->action[$pattern] = $action;
        $this->method[$pattern] = $requestMethod;
        $this->middleware[$pattern] = $middleware; // Store middleware
    }

    public function get(string $pattern, callable|string $controller, string $action = 'index', array $middleware = []): void
    {
        $this->add('GET', $pattern, $controller, $action, $middleware);
    }

    public function post(string $pattern, callable|string $controller, string $action = 'index', array $middleware = []): void
    {
        $this->add('POST', $pattern, $controller, $action, $middleware);
    }

    public function put(string $pattern, callable|string $controller, string $action = 'index'): void
    {
        $this->add('PUT', $pattern, $controller, $action);
    }

    public function delete(string $pattern, callable|string $controller, string $action = 'index'): void
    {
        $this->add('DELETE', $pattern, $controller, $action);
    }

    public function run(): void
	{
		$url = $this->getUrl();
		$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

		$this->redirectIfNeeded();

		foreach ($this->routes as $pattern => $controller) {
			if (preg_match($pattern, $url, $matches)) {
				if (stripos($this->method[$pattern], $requestMethod) === false) {
					$this->logger->write("Method not allowed for URL: $url");
					$this->error->show(405);
					return; // Exit after logging
				}
				array_shift($matches);

				// Handle middleware
				if (!empty($this->middleware[$pattern])) {
					$response = $this->handleMiddleware($this->middleware[$pattern]);
					if ($response) {
						echo $response; // Output response if middleware halts execution
						return;
					}
				}

				$this->logger->write("Accessing route: $url with method: $requestMethod");
				$this->handleRequest($controller, $matches, $pattern);
				exit;
			}
		}

		$this->logger->write("No matching route found for URL: $url");
		$this->error->show(404); // Show the 404 error page
		exit; // Stop further execution
	}

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

    private function handleRequest(callable|string $controller, array $matches, string $pattern): void
    {
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
    }

    private function preparePattern(string $pattern): string
    {
        return match ($pattern) {
            '/' => '~^\/' . basename(strtolower(ROOT)) . '\/$~',
            default => '~^\/' . str_replace('/', '\/', basename(strtolower(ROOT)) . $pattern) . '$~',
        };
    }

    private function getUrl(): string
    {
        $url = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // Split the URL into segments by the slash character
		$segments = explode('/', $url);

		// If the second segment is not set, use the basename of the root directory as the default
		$segments[1] ??= basename(strtolower(ROOT));

		// If the second segment is not the same as the basename of the root directory, prepend it to the URL
		$url = ($segments[1] !== basename(strtolower(ROOT))) ? DS . basename(strtolower(ROOT)) . $url : $url;
		

        return $url;
    }

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
}