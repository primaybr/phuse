<?php

declare(strict_types=1);

namespace Core\Http;

use Core\Log;
use Core\Exception\RuntimeException;
use Core\Exception\ValidationException;

/**
 * HTTP Client Class
 *
 * Provides utility methods for handling HTTP client-related operations,
 * particularly for retrieving client IP addresses from various proxy headers
 * with enhanced security and performance features.
 *
 * Security Features:
 * - Comprehensive IP validation with private range filtering
 * - IPv6 support with proper validation
 * - Performance optimizations for header checking
 * - Protection against IP spoofing through multiple validation layers
 *
 * @package Core\Http
 * @author  Prima Yoga
 */
class Client
{
    /**
     * Priority-ordered list of headers to check for client IP.
     * Higher priority headers are checked first.
     */
    private const IP_HEADERS = [
        'HTTP_CF_CONNECTING_IP',     // Cloudflare
        'HTTP_TRUE_CLIENT_IP',       // Akamai/Cloudflare
        'HTTP_X_CLIENT_IP',          // Custom header
        'HTTP_X_FORWARDED_FOR',      // Standard proxy header
        'HTTP_X_FORWARDED',          // Alternative proxy header
        'HTTP_X_CLUSTER_CLIENT_IP',  // Load balancer header
        'HTTP_FORWARDED_FOR',        // Legacy proxy header
        'HTTP_FORWARDED',           // Legacy proxy header
        'REMOTE_ADDR'               // Direct connection
    ];

    /**
     * IP validation flags for security filtering.
     */
    private const IP_VALIDATION_FLAGS = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;

    /**
     * Cache for validated IP addresses to improve performance.
     */
    private static ?string $cachedIp = null;

    /**
     * Explicit list of trusted proxy IPs. Only when REMOTE_ADDR matches one of
     * these will forwarding headers (X-Forwarded-For, CF-Connecting-IP, etc.)
     * be trusted. Empty by default - meaning REMOTE_ADDR is always the source.
     */
    private static array $trustedProxies = [];

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
     * Configure which upstream proxy IPs are trusted.
     * Call this once during bootstrap if your app sits behind a known proxy.
     *
     * @param array $proxies List of trusted proxy IP addresses.
     */
    public static function setTrustedProxies(array $proxies): void
    {
        self::$trustedProxies = $proxies;
        self::$cachedIp = null; // invalidate cache on config change
    }

    /**
     * Determines whether an IP address matches the trusted proxies list,
     * supporting both exact IP strings and CIDR subnet notation (e.g. 10.0.0.0/8).
     *
     * @param string|null $ip The IP to check against trusted proxies.
     * @return bool True if trusted, false otherwise.
     */
    public static function isTrustedProxy(?string $ip): bool
    {
        if ($ip === null || $ip === '' || empty(self::$trustedProxies)) {
            return false;
        }
        foreach (self::$trustedProxies as $proxy) {
            $proxy = trim((string) $proxy);
            if ($proxy === '') continue;
            if ($proxy === $ip) return true;
            if (str_contains($proxy, '/') && self::ipMatchesCidr($ip, $proxy)) return true;
        }
        return false;
    }

    /**
     * Checks if an IP address belongs to a given CIDR subnet.
     * Supports both IPv4 and IPv6.
     *
     * @param string $ip The IP address (e.g. 10.244.1.5).
     * @param string $cidr The CIDR subnet (e.g. 10.0.0.0/8 or fd00::/8).
     * @return bool True if IP is in subnet, false otherwise.
     */
    public static function ipMatchesCidr(string $ip, string $cidr): bool
    {
        $parts = explode('/', $cidr, 2);
        if (count($parts) !== 2) return false;
        $subnet = trim($parts[0]);
        $prefixStr = trim($parts[1]);
        if (!is_numeric($prefixStr)) return false;
        $prefix = (int) $prefixStr;
        $ipBin = @inet_pton($ip);
        $subnetBin = @inet_pton($subnet);
        if ($ipBin === false || $subnetBin === false) return false;
        $len = strlen($ipBin);
        if ($len !== strlen($subnetBin)) return false;
        $maxPrefix = $len * 8;
        if ($prefix < 0 || $prefix > $maxPrefix) return false;
        if ($prefix === 0) return true;
        $fullBytes = intdiv($prefix, 8);
        if ($fullBytes > 0 && substr($ipBin, 0, $fullBytes) !== substr($subnetBin, 0, $fullBytes)) return false;
        $remBits = $prefix % 8;
        if ($remBits > 0) {
            $mask = ~((1 << (8 - $remBits)) - 1) & 0xFF;
            $ipByte = ord($ipBin[$fullBytes]);
            $subnetByte = ord($subnetBin[$fullBytes]);
            if (($ipByte & $mask) !== ($subnetByte & $mask)) return false;
        }
        return true;
    }

    /**
     * Retrieves the real IP address of the client.
     *
     * By default always uses REMOTE_ADDR (the direct TCP peer) to prevent
     * IP spoofing via forged forwarding headers. Forwarding headers are only
     * trusted when REMOTE_ADDR is explicitly listed in setTrustedProxies().
     *
     * @return string The client's real IP address or 'UNKNOWN' if not determinable.
     */
    public function getIpAddress(): string
    {
        if (self::$cachedIp !== null) {
            return self::$cachedIp;
        }

        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? null;

        // Only honour forwarding headers when the direct connection arrives
        // from a known, configured trusted proxy.
        if ($remoteAddr !== null && self::isTrustedProxy($remoteAddr)) {
            $proxyHeaders = array_slice(self::IP_HEADERS, 0, -1); // exclude REMOTE_ADDR
            foreach ($proxyHeaders as $header) {
                $ip = $this->extractIpFromHeader($header);
                if ($ip && $this->isValidPublicIp($ip)) {
                    self::$cachedIp = $ip;
                    return $ip;
                }
            }
        }

        // Fall back to the direct connection address.
        if ($remoteAddr !== null && filter_var($remoteAddr, FILTER_VALIDATE_IP) !== false) {
            self::$cachedIp = $remoteAddr;
            return $remoteAddr;
        }

        self::$cachedIp = 'UNKNOWN';
        return 'UNKNOWN';
    }

    /**
     * Extracts IP address from a specific header.
     *
     * @param string $header The header name to check.
     * @return string|null The extracted IP address or null if not found.
     */
    private function extractIpFromHeader(string $header): ?string
    {
        $headerValue = $_SERVER[$header] ?? null;

        if (empty($headerValue)) {
            return null;
        }

        // Handle comma-separated IP lists (like X-Forwarded-For)
        $ipList = explode(',', $headerValue);
        foreach ($ipList as $ip) {
            $trimmedIp = trim($ip);

            if (filter_var($trimmedIp, FILTER_VALIDATE_IP) !== false) {
                return $trimmedIp;
            }
        }

        return null;
    }

    /**
     * Validates if an IP address is a valid public IP.
     *
     * @param string $ip The IP address to validate.
     * @return bool True if the IP is valid and public, false otherwise.
     */
    private function isValidPublicIp(string $ip): bool
    {
        // Validate IP format and ensure it's not private/reserved
        if (filter_var($ip, FILTER_VALIDATE_IP, self::IP_VALIDATION_FLAGS) === false) {
            return false;
        }

        // Additional validation for IPv6 if needed
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
            return $this->isValidPublicIpv6($ip);
        }

        // For IPv4, additional checks could be added here
        return true;
    }

    /**
     * Validates IPv6 addresses for public accessibility.
     *
     * @param string $ip The IPv6 address to validate.
     * @return bool True if the IPv6 address is valid and public.
     */
    private function isValidPublicIpv6(string $ip): bool
    {
        // Check for local/link-local addresses
        if (str_starts_with($ip, 'fe80:') || str_starts_with($ip, 'fc00:') || str_starts_with($ip, '::1')) {
            return false;
        }

        // Additional IPv6 validation could be added here
        return true;
    }

    /**
     * Clears the IP address cache (useful for testing or when IP might change).
     *
     * @return void
     */
    public static function clearIpCache(): void
    {
        self::$cachedIp = null;
    }

    /**
     * Gets client information including IP and user agent.
     *
     * @return array Array containing client IP and user agent information.
     */
    public function getClientInfo(): array
    {
        return [
            'ip_address' => $this->getIpAddress(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'accept_language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
            'accept_encoding' => $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '',
        ];
    }

    /**
     * Checks if the client is accessing from a mobile device.
     *
     * @return bool True if the client appears to be mobile, false otherwise.
     */
    public function isMobile(): bool
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $mobileKeywords = [
            'mobile', 'android', 'iphone', 'ipad', 'ipod', 'blackberry',
            'windows phone', 'opera mini', 'opera mobi', 'webos', 'iemobile'
        ];

        foreach ($mobileKeywords as $keyword) {
            if (stripos($userAgent, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the client is a bot or crawler.
     *
     * @return bool True if the client appears to be a bot, false otherwise.
     */
    public function isBot(): bool
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $botKeywords = [
            'bot', 'crawler', 'spider', 'scraper', 'googlebot', 'bingbot',
            'yahoo', 'duckduckbot', 'baiduspider', 'yandex', 'sogou',
            'exabot', 'facebookexternalhit', 'twitterbot', 'linkedinbot'
        ];

        foreach ($botKeywords as $keyword) {
            if (stripos($userAgent, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }
}
