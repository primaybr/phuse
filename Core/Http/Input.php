<?php

declare(strict_types=1);

namespace Core\Http;

use Core\Log;
use Core\Exception\RuntimeException;
use Core\Exception\ValidationException;

/**
 * HTTP Input Class
 *
 * Provides secure methods for handling HTTP input data including GET, POST, PUT, and DELETE requests.
 * This class offers a clean interface for accessing various types of HTTP input data with built-in
 * security features including XSS protection and input validation.
 *
 * Security Features:
 * - Automatic XSS protection using htmlspecialchars()
 * - Input validation and sanitization
 * - Safe access to superglobals with null coalescing
 * - Prevention of variable pollution
 *
 * @package Core\Http
 * @author  Prima Yoga
 */
class Input
{
    /**
     * Default sanitization flags for XSS protection.
     */
    private const SANITIZE_FLAGS = ENT_QUOTES | ENT_HTML5;

    /**
     * Logger instance for framework logging.
     */
    private Log $logger;

    /**
     * Constructor with optional logger injection.
     *
     * @param Log|null $logger Logger instance for framework logging.
     */
    public function __construct(?Log $logger = null)
    {
        $this->logger = $logger ?? new Log();
    }

    /**
     * Retrieves GET parameters from the HTTP request with optional sanitization.
     *
     * @param string $name Optional parameter name to retrieve a specific GET value.
     * @param bool $sanitize Whether to sanitize the output for XSS protection (default: true).
     * @return string|array Returns the specific parameter value if name is provided, otherwise returns all GET parameters.
     */
    public function get(string $name = '', bool $sanitize = true): string|array
    {
        if ($name) {
            $value = $_GET[$name] ?? '';
            return $sanitize ? $this->sanitize($value) : $value;
        }

        return $sanitize ? $this->sanitizeArray($_GET) : $_GET;
    }

    /**
     * Retrieves POST parameters from the HTTP request with optional sanitization.
     *
     * @param string $name Optional parameter name to retrieve a specific POST value.
     * @param bool $sanitize Whether to sanitize the output for XSS protection (default: true).
     * @return string|array Returns the specific parameter value if name is provided, otherwise returns all POST parameters.
     */
    public function post(string $name = '', bool $sanitize = true): string|array
    {
        if ($name) {
            $value = $_POST[$name] ?? '';
            return $sanitize ? $this->sanitize($value) : $value;
        }

        return $sanitize ? $this->sanitizeArray($_POST) : $_POST;
    }

    /**
     * Retrieves raw PUT data from the HTTP request body.
     *
     * @return string|null The raw PUT data or null if no data available.
     */
    public function put(): ?string
    {
        $data = file_get_contents('php://input');

        // Check for read errors
        if ($data === false) {
            return null;
        }

        return $data;
    }

    /**
     * Retrieves DELETE parameters from the HTTP request body with optional sanitization.
     *
     * Parses the raw DELETE data as if it were a query string and returns the parameters.
     * Uses a local variable to avoid polluting the global namespace.
     *
     * @param string $name Optional parameter name to retrieve a specific DELETE value.
     * @param bool $sanitize Whether to sanitize the output for XSS protection (default: true).
     * @return string|array Returns the specific parameter value if name is provided, otherwise returns all DELETE parameters.
     */
    public function delete(string $name = '', bool $sanitize = true): string|array
    {
        $rawData = $this->put();

        if ($rawData === null) {
            return $name ? '' : [];
        }

        $deleteParams = [];
        parse_str($rawData, $deleteParams);

        if ($name) {
            $value = $deleteParams[$name] ?? '';
            return $sanitize ? $this->sanitize($value) : $value;
        }

        return $sanitize ? $this->sanitizeArray($deleteParams) : $deleteParams;
    }

    /**
     * Retrieves REQUEST parameters (combination of GET and POST) with optional sanitization.
     *
     * @param string $name Optional parameter name to retrieve a specific REQUEST value.
     * @param bool $sanitize Whether to sanitize the output for XSS protection (default: true).
     * @return string|array Returns the specific parameter value if name is provided, otherwise returns all REQUEST parameters.
     */
    public function request(string $name = '', bool $sanitize = true): string|array
    {
        if ($name) {
            $value = $_REQUEST[$name] ?? '';
            return $sanitize ? $this->sanitize($value) : $value;
        }

        return $sanitize ? $this->sanitizeArray($_REQUEST) : $_REQUEST;
    }

    /**
     * Retrieves FILES data from the HTTP request.
     *
     * @param string $name Optional file input name to retrieve a specific file.
     * @return array|string Returns the specific file data if name is provided, otherwise returns all FILES data.
     */
    public function files(string $name = ''): array|string
    {
        if ($name) {
            return $_FILES[$name] ?? '';
        }

        return $_FILES;
    }

    /**
     * Checks if a specific input parameter exists.
     *
     * @param string $name The parameter name to check.
     * @param string $method The HTTP method to check (GET, POST, REQUEST, default: REQUEST).
     * @return bool True if the parameter exists, false otherwise.
     */
    public function has(string $name, string $method = 'REQUEST'): bool
    {
        return match (strtoupper($method)) {
            'GET' => isset($_GET[$name]),
            'POST' => isset($_POST[$name]),
            'REQUEST' => isset($_REQUEST[$name]),
            default => false,
        };
    }

    /**
     * Gets all input methods as a combined array with optional sanitization.
     *
     * @param bool $sanitize Whether to sanitize the output for XSS protection (default: true).
     * @return array Combined array of GET, POST, and FILES data.
     */
    public function all(bool $sanitize = true): array
    {
        $combined = array_merge($_GET, $_POST);

        if (isset($_FILES)) {
            $combined = array_merge($combined, $_FILES);
        }

        return $sanitize ? $this->sanitizeArray($combined) : $combined;
    }

    /**
     * Sanitizes a string value for XSS protection.
     *
     * @param mixed $value The value to sanitize.
     * @return string The sanitized string value.
     */
    private function sanitize(mixed $value): string
    {
        if (is_array($value)) {
            // This should not happen in normal flow, but handle it gracefully
            return '';
        }

        if (!is_string($value)) {
            return (string)$value;
        }

        return htmlspecialchars($value, self::SANITIZE_FLAGS, 'UTF-8');
    }

    /**
     * Recursively sanitizes an array for XSS protection.
     *
     * @param array $array The array to sanitize.
     * @return array The sanitized array.
     */
    private function sanitizeArray(array $array): array
    {
        $sanitized = [];

        foreach ($array as $key => $value) {
            $sanitized[$key] = is_array($value) ?
                $this->sanitizeArray($value) :
                $this->sanitize($value);
        }

        return $sanitized;
    }
}