<?php

declare(strict_types=1);

namespace Core\Http;

use Core\Config as Config;
use Core\Http\Session;
use Core\Log;
use Core\Exception\RuntimeException;
use Core\Exception\ValidationException;
use Core\Exception\ConfigurationException;

/**
 * HTTP Request Class
 *
 * A comprehensive HTTP client for making various types of HTTP requests including GET, POST, PUT, and DELETE.
 * This class handles authentication, token refresh, SSL configuration, and provides a fluent interface
 * for building and executing HTTP requests with proper error handling and response management.
 *
 * Features:
 * - Fluent interface for method chaining
 * - Automatic token refresh for authenticated requests
 * - SSL/TLS configuration options
 * - Environment-based URL handling
 * - Comprehensive header and content type management
 * - Stream-based response handling
 *
 * @package Core\Http
 * @author  Prima Yoga
 */
class Request
{
    /**
     * The HTTP method for the request (GET, POST, PUT, DELETE, etc.).
     */
    private string $method;

    /**
     * The request data/payload.
     */
    private mixed $data;

    /**
     * The protocol wrapper (http, https, etc.).
     */
    private string $wrapper;

    /**
     * HTTP request options array.
     */
    private array $options = [];

    /**
     * SSL configuration options.
     */
    private array $ssl = [];

    /**
     * Final compiled options for stream context.
     */
    private array $finalOptions = [];

    /**
     * The stream resource for the HTTP request.
     */
    private mixed $stream;

    /**
     * Framework configuration instance.
     */
    private $config;

    /**
     * Base URL for the application.
     */
    private string $baseUrl;

    /**
     * External API base URL.
     */
    private string $apiExternalUrl;

    /**
     * Current environment (local, development, production, etc.).
     */
    private string $env;

    /**
     * Whether to use token refresh functionality.
     */
    private bool $useRefresh;

    /**
     * HTTP response status code.
     */
    public int|string $httpResponseCode = 200;

    /**
     * HTTP response status text.
     */
    public string $httpResponseStatus;

    /**
     * Session management instance.
     */
    public Session $session;

    /**
     * Authentication token object.
     */
    private object $token;

    /**
     * Admin URL for the application.
     */
    private string $adminUrl;

    /**
     * Logger instance for framework logging.
     */
    private Log $logger;

    /**
     * Initializes the HTTP Request client with configuration and session setup.
     *
     * @param string $wrapper The protocol wrapper to use (default: 'http').
     * @param Log|null $logger Logger instance for framework logging.
     */
    public function __construct(string $wrapper = 'http', ?Log $logger = null)
    {
        $this->logger = $logger ?? new Log();
        $this->setWrapper($wrapper);
        $this->config = (new Config())->get();
        $this->baseUrl = $this->config->site->baseUrl;
        $this->apiExternalUrl = isset($this->config->apiExternal->baseUrl) ? $this->config->apiExternal->baseUrl : '';
        $this->useRefresh = isset($this->config->apiExternal->useRefresh) ? (bool)($this->config->apiExternal->useRefresh === 'true') : false;
        $this->env = $this->config->env;
        $this->session = new Session($this->logger);
        $this->adminUrl = $this->config->site->adminUrl;
    }

    /**
     * Executes an HTTP request with the specified method, URL, and optional data.
     *
     * This method handles the complete request lifecycle including authentication,
     * token refresh, and response processing. It supports method chaining for
     * fluent interface usage.
     *
     * @param string $method The HTTP method (GET, POST, PUT, DELETE, etc.).
     * @param string $url The target URL for the request.
     * @param array|string $data Optional request data/payload.
     * @param bool $refresh Whether to attempt token refresh on 401 responses (default: true).
     * @return self Returns the current instance for method chaining.
     * @throws ValidationException If method or URL is invalid.
     * @throws RuntimeException If request fails or response cannot be processed.
     */
    public function request(string $method, string $url, array|string $data = [], bool $refresh = true): self
    {
        // Validate input parameters
        $this->validateRequestParameters($method, $url);

        if ($data) {
            $this->data = $this->setContent($data);
        }

        $this->method = strtoupper($method);

        $this->getContextOptions();

        $context = stream_context_create($this->finalOptions);

        // Handle environment-specific URL transformation for local development
        $requestUrl = $this->transformUrlForEnvironment($url);

        try {
            $this->stream = fopen($requestUrl, 'r', false, $context);

            if ($this->stream === false) {
                throw new RuntimeException("Failed to open stream for URL: {$requestUrl}");
            }

            $this->httpResponseCode = $this->extractResponseCode();

        } catch (\Exception $e) {
            $this->logger->write("HTTP request failed for URL: {$requestUrl}", 'error', [
                'method' => $method,
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            throw new RuntimeException("HTTP request failed: " . $e->getMessage());
        }

        // Attempt token refresh if enabled and response is 401 Unauthorized
        if ($refresh && $this->useRefresh && $this->httpResponseCode == 401) {
            return $this->refreshRequest($method, $url, $data);
        }

        return $this;
    }

    /**
     * Validates request parameters before execution.
     *
     * @param string $method The HTTP method.
     * @param string $url The request URL.
     * @throws ValidationException If parameters are invalid.
     */
    private function validateRequestParameters(string $method, string $url): void
    {
        $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS'];
        $method = strtoupper($method);

        if (!in_array($method, $allowedMethods, true)) {
            throw new ValidationException("Invalid HTTP method: {$method}. Allowed methods: " . implode(', ', $allowedMethods));
        }

        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new ValidationException("Invalid URL provided: {$url}");
        }
    }

    /**
     * Transforms URL based on environment settings.
     *
     * @param string $url The original URL.
     * @return string The transformed URL.
     */
    private function transformUrlForEnvironment(string $url): string
    {
        if ($this->env !== 'local') {
            $url = str_replace($this->baseUrl, "http://localhost/", $url);
        }

        return $url;
    }

    /**
     * Extracts HTTP response code from response headers.
     *
     * @return int|string The HTTP response code.
     */
    private function extractResponseCode(): int|string
    {
        if (isset($http_response_header[0])) {
            $responseLine = $http_response_header[0];
            if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $responseLine, $matches)) {
                return (int)$matches[1];
            }
        }

        return 200; // Default fallback
    }

    /**
     * Retrieves the content from the HTTP response stream.
     *
     * @return string|false The response content as a string, or false if no valid stream.
     */
    public function getContent(): string|false
    {
        if (is_resource($this->stream)) {
            $content = stream_get_contents($this->stream);
            fclose($this->stream);
            return $content;
        }

        return false;
    }

    /**
     * Handles token refresh for authenticated requests that return 401 Unauthorized.
     *
     * This method attempts to refresh the access token using the refresh token
     * and then re-executes the original request with the new token.
     *
     * @param string $method The original HTTP method.
     * @param string $url The original request URL.
     * @param array|string $data The original request data.
     * @return self Returns the current instance for method chaining.
     * @throws \RuntimeException If token refresh fails or JSON decoding fails.
     */
    private function refreshRequest(string $method, string $url, array|string $data = []): self
    {
        $token = $this->session->get('sesstoken');

        if (!$token || !isset($token->access_token, $token->refresh_token)) {
            throw new \RuntimeException('No valid token available for refresh');
        }

        try {
            // Attempt to refresh the token
            $newTokenResponse = $this->setContentType('application/json')
                ->setHeader("Authorization: Bearer " . $token->access_token)
                ->request('POST', $this->apiExternalUrl . '/auth/refresh', [
                    'json' => json_encode(['refresh_token' => $token->refresh_token])
                ], false)
                ->getContent();

            if ($this->httpResponseCode !== 200) {
                throw new \RuntimeException('Token refresh failed with status: ' . $this->httpResponseCode);
            }

            $newToken = json_decode($newTokenResponse);
            if ($newToken === null) {
                throw new \RuntimeException('Invalid JSON response from token refresh endpoint');
            }

            if (!isset($newToken->access_token, $newToken->id_token)) {
                throw new \RuntimeException('Invalid token response structure');
            }

            // Update session with new token
            $this->updateSessionWithNewToken($newToken);

            // Re-execute the original request with new token
            return $this->executeRequestWithNewToken($method, $url, $data);

        } catch (\Exception $e) {
            throw new \RuntimeException('Token refresh process failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Updates session with the new token data.
     *
     * @param object $newToken The new token object.
     * @throws \RuntimeException If token structure is invalid.
     */
    private function updateSessionWithNewToken(object $newToken): void
    {
        $this->setHeader("Authorization: Bearer " . $newToken->access_token);

        // Validate JWT structure
        $jwtParts = explode('.', $newToken->id_token);
        if (count($jwtParts) !== 3) {
            throw new \RuntimeException('Invalid JWT token structure');
        }

        [$header, $payload, $signature] = $jwtParts;

        $decodedPayload = json_decode(base64_decode($payload), true);
        if ($decodedPayload === null) {
            throw new \RuntimeException('Invalid JWT payload');
        }

        $this->session->set('sessdata', $decodedPayload['dat'] ?? []);
        $this->session->set('sesstoken', $newToken);
    }

    /**
     * Re-executes the original request with the new token.
     *
     * @param string $method The HTTP method.
     * @param string $url The request URL.
     * @param array|string $data The request data.
     * @return self Returns the current instance for method chaining.
     */
    private function executeRequestWithNewToken(string $method, string $url, array|string $data = []): self
    {
        if ($data) {
            $this->data = $this->setContent($data);
        }

        $this->method = strtoupper($method);
        $this->getContextOptions();

        $context = stream_context_create($this->finalOptions);
        $requestUrl = $this->transformUrlForEnvironment($url);

        try {
            $this->stream = fopen($requestUrl, 'r', false, $context);

            if ($this->stream === false) {
                throw new \RuntimeException("Failed to open stream for URL after token refresh: {$requestUrl}");
            }

            $this->httpResponseCode = $this->extractResponseCode();

        } catch (\Exception $e) {
            throw new \RuntimeException("HTTP request with refreshed token failed: " . $e->getMessage(), 0, $e);
        }

        return $this;
    }

    /**
     * Sets a custom header for the HTTP request.
     *
     * @param string $header The header string to set.
     * @return self Returns the current instance for method chaining.
     */
    public function setHeader(string $header): self
    {
        $this->options['header'] = $header;
        return $this;
    }

    /**
     * Sets the content for the HTTP request with automatic format detection.
     *
     * @param array|string $content The content to set (array for form data, string for raw content).
     * @return self Returns the current instance for method chaining.
     * @throws \InvalidArgumentException If the content type is not supported.
     */
    public function setContent(array|string $content): self
    {
        $this->options['content'] = match (true) {
            array_key_exists('json', $content) => $content['json'],
            is_array($content) => http_build_query($content),
            is_string($content) => $content,
            default => throw new \InvalidArgumentException('Invalid content type'),
        };

        return $this;
    }

    /**
     * Sets the content type for the HTTP request.
     *
     * @param string $type The MIME type for the content (default: 'application/x-www-form-urlencoded').
     * @return self Returns the current instance for method chaining.
     */
    public function setContentType(string $type = 'application/x-www-form-urlencoded'): self
    {
        $this->options['content_type'] ??= $type;
        return $this;
    }

    /**
     * Sets the protocol wrapper for the HTTP request.
     *
     * @param string $wrapper The protocol wrapper (default: 'http').
     * @return self Returns the current instance for method chaining.
     */
    public function setWrapper(string $wrapper = 'http'): self
    {
        $this->wrapper = $wrapper;
        return $this;
    }

    /**
     * Configures SSL settings for the HTTP request.
     *
     * @param bool $on Whether to enable SSL verification (default: true).
     * @return self Returns the current instance for method chaining.
     */
    public function setSSL(bool $on = true): self
    {
        if (!$on) {
            $this->ssl = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]];
        }

        return $this;
    }

    /**
     * Sets the HTTP referer header for the request.
     *
     * @param string $referer The referer URL to set.
     * @return self Returns the current instance for method chaining.
     */
    public function setReferer(string $referer): self
    {
        $this->options['referer'] = $referer;
        return $this;
    }

    /**
     * Sets a custom option for the HTTP request context.
     *
     * @param string $option The option name.
     * @param mixed $value The option value.
     * @return self Returns the current instance for method chaining.
     */
    public function setOptions(string $option, mixed $value): self
    {
        $this->options[$option] = $value;
        return $this;
    }

    /**
     * Compiles and configures the HTTP context options for the stream request.
     *
     * This method prepares all necessary options including headers, content type,
     * and method-specific configurations before creating the stream context.
     *
     * @return self Returns the current instance for method chaining.
     */
    private function getContextOptions(): self
    {
        $this->options['max_redirects'] = 0;
        $this->options['ignore_errors'] = 1;
        $this->options['method'] = $this->method;

        if ($this->method === 'POST' || $this->method === 'PUT') {
            if (empty($this->data)) {
                throw new \RuntimeException('You need to specify content to use POST or PUT request.');
            }

            $this->setContentType();

            $this->setHeader(
                "Content-type: " . $this->options['content_type'] . " \r\n" .
                "Content-Length: " . strlen($this->options['content']) . "\r\n" .
                (isset($this->options['header']) ? $this->options['header'] . "\r\n" : "") .
                (isset($this->options['referer']) ? "Referer: " . $this->options['referer'] . "\r\n" : "") .
                (isset($this->options['uid']) ? "uid: " . $this->options['uid'] . "\r\n" : "") .
                (isset($this->options['token']) ? "token: " . $this->options['token'] . "\r\n" : "") .
                (isset($this->options['Auth']) ? "Auth: " . $this->options['Auth'] . "\r\n" : "")
            );
        } else {
            $this->setHeader(
                (isset($this->options['content_type']) ? "Content-type: " . $this->options['content_type'] . " \r\n" : "") .
                (isset($this->options['header']) ? $this->options['header'] . "\r\n" : "")
            );
        }

        foreach ($this->options as $key => $val) {
            $this->finalOptions[$this->wrapper][$key] = $val;
        }

        $this->finalOptions = array_merge($this->finalOptions, $this->ssl);

        return $this;
    }

    // ========== GETTERS AND SETTERS ==========

    /**
     * Gets the HTTP method for the current request.
     *
     * @return string The HTTP method.
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Sets the HTTP method for the request.
     *
     * @param string $method The HTTP method to set.
     * @return self Returns the current instance for method chaining.
     */
    public function setMethod(string $method): self
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Gets the request data/payload.
     *
     * @return mixed The request data.
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * Sets the request data/payload.
     *
     * @param mixed $data The request data to set.
     * @return self Returns the current instance for method chaining.
     */
    public function setData(mixed $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Gets the protocol wrapper.
     *
     * @return string The protocol wrapper.
     */
    public function getWrapper(): string
    {
        return $this->wrapper;
    }

    /**
     * Gets the HTTP request options array.
     *
     * @return array The options array.
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Gets the SSL configuration options.
     *
     * @return array The SSL options array.
     */
    public function getSsl(): array
    {
        return $this->ssl;
    }

    /**
     * Gets the final compiled options for the stream context.
     *
     * @return array The final options array.
     */
    public function getFinalOptions(): array
    {
        return $this->finalOptions;
    }

    /**
     * Gets the stream resource.
     *
     * @return mixed The stream resource.
     */
    public function getStream(): mixed
    {
        return $this->stream;
    }

    /**
     * Sets the stream resource.
     *
     * @param mixed $stream The stream resource to set.
     * @return self Returns the current instance for method chaining.
     */
    public function setStream(mixed $stream): self
    {
        $this->stream = $stream;
        return $this;
    }

    /**
     * Gets the framework configuration instance.
     *
     * @return Config The configuration instance.
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Sets the framework configuration instance.
     *
     * @param Config $config The configuration instance to set.
     * @return self Returns the current instance for method chaining.
     */
    public function setConfig(Config $config): self
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Gets the base URL for the application.
     *
     * @return string The base URL.
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Sets the base URL for the application.
     *
     * @param string $baseUrl The base URL to set.
     * @return self Returns the current instance for method chaining.
     */
    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * Gets the current environment.
     *
     * @return string The environment name.
     */
    public function getEnv(): string
    {
        return $this->env;
    }

    /**
     * Sets the current environment.
     *
     * @param string $env The environment name to set.
     * @return self Returns the current instance for method chaining.
     */
    public function setEnv(string $env): self
    {
        $this->env = $env;
        return $this;
    }

    /**
     * Gets the HTTP response code.
     *
     * @return int|string The HTTP response code.
     */
    public function getHttpResponseCode(): int|string
    {
        return $this->httpResponseCode;
    }

    /**
     * Sets the HTTP response code and updates the response status text.
     *
     * @param int $httpResponseCode The HTTP response code to set.
     * @return self Returns the current instance for method chaining.
     */
    public function setHttpResponseCode(int $httpResponseCode): self
    {
        $this->httpResponseCode = $httpResponseCode;
        $this->httpResponseStatus = (new Response($httpResponseCode))->statusName;
        return $this;
    }
}
