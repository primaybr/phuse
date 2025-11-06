<?php

declare(strict_types=1);

namespace Core\Http;

use Core\Config as Config;
use Core\Log;
use Core\Exception\RuntimeException;
use Core\Exception\ValidationException;
use Core\Exception\ConfigurationException;

/**
 * HTTP URI Management Class
 *
 * Provides comprehensive utilities for handling HTTP URIs, including URL generation,
 * image path creation, YouTube thumbnail generation, and current URL detection.
 * This class handles URL normalization, protocol detection, and various URI-related operations.
 *
 * @package Core\Http
 * @author  Prima Yoga
 */
final class URI
{
    /**
     * Character used for replacing spaces and special characters in URLs.
     */
    public const REPLACE = '-';

    /**
     * Regular expression patterns for URL cleaning and normalization.
     *
     * Contains patterns for removing HTML entities, normalizing whitespace,
     * and cleaning special characters from URLs.
     */
    public const PATTERN = [
        '&#\d+?;' => '',      // Remove HTML entities
        '&\S+?;' => '',       // Remove other HTML entities
        '\s+' => self::REPLACE, // Replace whitespace with dashes
        '[^a-z0-9\-\._]' => '', // Remove non-alphanumeric characters except dashes, dots, underscores
        self::REPLACE.'+' => self::REPLACE, // Replace multiple dashes with single dash
        self::REPLACE.'$' => self::REPLACE, // Remove trailing dashes
        '^'.self::REPLACE => self::REPLACE, // Remove leading dashes
        '\.+$' => '',         // Remove trailing dots
    ];

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
     * Creates a URL-friendly string from the provided text.
     *
     * This method cleans the input string by removing HTML tags, HTML entities,
     * normalizing whitespace, and creating a URL-safe slug with proper validation.
     *
     * @param string $string The input string to convert to a URL-friendly format.
     * @return string The cleaned, URL-friendly string.
     * @throws ValidationException If the input string is empty or invalid.
     */
    public function makeURL(string $string): string
    {
        if (empty(trim($string))) {
            throw new ValidationException('Input string cannot be empty');
        }

        $url = strip_tags($string);

        foreach (self::PATTERN as $pattern => $replacement) {
            $url = preg_replace('#'.$pattern.'#i', $replacement, $url);
        }

        $url = html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $url = trim($url);
        $url = stripslashes($url);
        $url = str_replace([',', '.'], ['', ''], $url);
        $url = strtolower($url);

        if (empty($url)) {
            throw new ValidationException('Generated URL slug is empty');
        }

        return $url;
    }

    /**
     * Creates a full URL by prepending the site base URL to the provided string.
     *
     * @param string $string The URL string to prepend the base URL to.
     * @return string The complete URL with base URL prepended.
     * @throws ConfigurationException If the base URL is not configured or invalid.
     * @throws ValidationException If the input string is invalid.
     */
    public function makeFullURL(string $string): string
    {
        $url = $this->makeURL($string);
        $config = new Config();

        $baseUrl = $config->get()->site->baseUrl ?? '';
        if (empty($baseUrl)) {
            throw new ConfigurationException('Base URL is not configured');
        }

        if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            throw new ConfigurationException('Invalid base URL configured');
        }

        return rtrim($baseUrl, '/') . '/' . ltrim($url, '/');
    }

    /**
     * Creates an image path with size directory for responsive images.
     *
     * This method constructs image paths that include size directories for
     * responsive image handling, excluding placeholder images.
     *
     * @param string $image The original image path.
     * @param string $size The size directory (e.g., 'thumb', 'medium', 'large').
     * @return string The image path with size directory included.
     * @throws ValidationException If image path or size is invalid.
     * @throws ConfigurationException If base URL is not configured.
     */
    public function makeImagePath(string $image, string $size): string
    {
        if (empty($image) || empty($size)) {
            throw new ValidationException('Image path and size cannot be empty');
        }

        $config = new Config();

        // Skip processing for placeholder images
        if (str_contains($image, 'place-hold.it')) {
            return $image;
        }

        $baseUrl = $config->get()->site->baseUrl ?? '';
        if (empty($baseUrl)) {
            throw new ConfigurationException('Base URL is not configured');
        }

        $basename = basename($image);
        if (empty($basename)) {
            throw new ValidationException('Invalid image path');
        }

        return rtrim($baseUrl, '/') . '/' . str_replace($basename, $size . '/' . $basename, $image);
    }

    /**
     * Generates a YouTube thumbnail URL for a given video URL.
     *
     * @param string $url The YouTube video URL.
     * @param int $type The thumbnail type (0-3, default: 0).
     * @return string|false The YouTube thumbnail URL or false if video ID cannot be extracted.
     * @throws ValidationException If URL is invalid or type is out of range.
     */
    public function makeImageYoutube(string $url, int $type = 0): string|false
    {
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new ValidationException('Invalid YouTube URL provided');
        }

        if ($type < 0 || $type > 3) {
            throw new ValidationException('Thumbnail type must be between 0 and 3');
        }

        $code = $this->getYoutubeCode($url);
        if (!empty($code)) {
            return "https://img.youtube.com/vi/{$code}/{$type}.jpg";
        }

        return false;
    }

    /**
     * Extracts the YouTube video ID from various YouTube URL formats.
     *
     * Supports standard YouTube URLs, shortened youtu.be URLs, and URLs with query parameters.
     *
     * @param string $url The YouTube URL to extract the video ID from.
     * @return string|false The YouTube video ID or false if not found.
     * @throws ValidationException If URL is invalid.
     * @throws RuntimeException If URL parsing fails.
     */
    public function getYoutubeCode(string $url): string|false
    {
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new ValidationException('Invalid URL provided');
        }

        $parts = parse_url($url);

        if (!$parts) {
            throw new RuntimeException('Malformed URL');
        }

        // Check for video ID in query parameters
        if (isset($parts['query'])) {
            $queryString = [];
            parse_str($parts['query'], $queryString);

            if (isset($queryString['v']) && !empty($queryString['v'])) {
                return $queryString['v'];
            }
            if (isset($queryString['vi']) && !empty($queryString['vi'])) {
                return $queryString['vi'];
            }
        }

        // Check for video ID in path (youtu.be URLs)
        if (isset($parts['path'])) {
            $pathSegments = explode('/', trim($parts['path'], '/'));
            $lastSegment = end($pathSegments);

            if (!empty($lastSegment)) {
                return $lastSegment;
            }
        }

        return false;
    }

    /**
     * Gets the current request URL.
     *
     * @param bool $full Whether to return the full URL with protocol and host (default: false).
     * @return string The current URL or empty string if not available.
     */
    public function getCurrentURL(bool $full = false): string
    {
        if ($full) {
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
            return $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '');
        }

        return $_SERVER['REQUEST_URI'] ?? '';
    }

    /**
     * Gets the HTTP referer from the request headers.
     *
     * @return string The HTTP referer URL or empty string if not available.
     */
    public function getHttpReferer(): string
    {
        return $_SERVER['HTTP_REFERER'] ?? '';
    }

    /**
     * Gets a specific segment from the current request URI.
     *
     * @param int $segment The segment index to retrieve (0-based).
     * @return string The URI segment or empty string if not found.
     */
    public function getSegment(int $segment): string
    {
        $config = new Config();
        $config = $config->get();

        $serverName = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
        $protocol = $this->getProtocol();
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';

        $fullUrl = $protocol . $serverName . $requestUri;
        $relativeUri = str_replace($config->site->baseUrl, '', $fullUrl);

        $uriSegments = explode('/', parse_url($relativeUri, PHP_URL_PATH) ?? '');

        return $uriSegments[$segment] ?? '';
    }

    /**
     * Detects the current request protocol (HTTP or HTTPS).
     *
     * Checks multiple indicators including HTTPS server variable and
     * forwarded protocol headers for reverse proxy compatibility.
     *
     * @return string The protocol string with trailing colon and slashes.
     */
    public function getProtocol(): string
    {
        if (
            (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === '1')) ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        ) {
            return 'https://';
        }

        return 'http://';
    }

    /**
     * Gets the current hostname with port number removed.
     *
     * This method checks multiple possible host headers and applies transformations
     * for forwarded host scenarios.
     *
     * @return string The hostname without port number.
     */
    public function getHost(): string
    {
        $possibleHostSources = [
            'HTTP_X_FORWARDED_HOST',
            'HTTP_HOST',
            'SERVER_NAME',
            'SERVER_ADDR'
        ];

        $sourceTransformations = [
            'HTTP_X_FORWARDED_HOST' => function ($value) {
                $elements = explode(',', $value);
                return trim(end($elements));
            }
        ];

        $host = '';
        foreach ($possibleHostSources as $source) {
            if (!empty($host)) {
                break;
            }
            if (empty($_SERVER[$source] ?? null)) {
                continue;
            }
            $host = $_SERVER[$source];
            if (isset($sourceTransformations[$source])) {
                $host = $sourceTransformations[$source]($host);
            }
        }

        // Remove port number from host
        $host = preg_replace('/:\d+$/', '', $host);

        return trim($host);
    }

    /**
     * Redirects to a specified URL with a 302 status code.
     *
     * @param string $url The URL to redirect to.
     * @return void This method terminates execution after sending headers.
     * @throws ValidationException If the URL is invalid or potentially malicious.
     * @throws RuntimeException If redirect fails.
     */
    public function redirect(string $url): void
    {
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new ValidationException('Invalid redirect URL provided');
        }

        // Prevent open redirect vulnerabilities - ensure URL uses allowed schemes
        $parsedUrl = parse_url($url);
        if (!$parsedUrl || !isset($parsedUrl['scheme'])) {
            throw new ValidationException('Invalid URL scheme in redirect');
        }

        $allowedSchemes = ['http', 'https'];
        if (!in_array($parsedUrl['scheme'], $allowedSchemes, true)) {
            throw new ValidationException('Redirect URL must use HTTP or HTTPS scheme');
        }

        // Additional security: prevent redirects to private IP ranges
        if (isset($parsedUrl['host'])) {
            $ip = gethostbyname($parsedUrl['host']);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                throw new ValidationException('Redirect URL resolves to private or reserved IP address');
            }
        }

        if (!headers_sent()) {
            header('Location: ' . $url, true, 302);
            exit();
        } else {
            $this->logger->write('Headers already sent, cannot redirect', 'warning', [
                'url' => $url,
                'headers_sent' => true
            ]);
            throw new RuntimeException('Cannot redirect: headers already sent');
        }
    }

    /**
     * Normalizes a URL by removing double slashes.
     *
     * @param string $url The URL to normalize.
     * @return string The normalized URL.
     */
    public function normalizeURL(string $url): string
    {
        return preg_replace('/([^:])(\/{2,})/', '$1/', $url);
    }
}
