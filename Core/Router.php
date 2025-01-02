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
		$serverName = $_SERVER['SERVER_NAME'] ?? 'localhost'; // Fallback to 'localhost'
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
        if (is_string($controller) && is_string($this->action[$pattern]) && !empty($this->action[$pattern])) {
            $controller = str_replace('/', '\\', Folder\Path::CONTROLLERS . $controller);
            $controller = new $controller();
            $handler = [$controller, $this->action[$pattern]];

            if (method_exists($controller, $this->action[$pattern]) && is_callable($handler)) {
                $this->logger->write("Calling controller method: " . $this->action[$pattern]);
                $handler(...$matches);
            } else {
                $this->error->show(404);
            }
        } else {
            $this->logger->write("Calling controller function");
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
        return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    }

    private function redirectIfNeeded(): void
    {
        $config = (new Config())->get();
        $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost'; // Fallback to 'localhost'
        $httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost'; // Fallback to 'localhost'
        $redirect = match (true) {
            (bool)ip2long($serverName) != 1 && $serverName != 'localhost' && !empty($serverName) && (isset($config->https) && $config->https === false)
                && !(isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') => 'https://' . $httpHost . $_SERVER['REQUEST_URI'],
            default => null,
        };

        if ($redirect) {
            header('HTTP/1.1 301 Moved Permanently');
            header('location: ' . $redirect);
            exit;
        }
    }
}