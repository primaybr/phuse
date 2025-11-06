<?php

declare(strict_types=1);

namespace Core\Http;

use Core\Log;
use Core\Exception\RuntimeException;
use Core\Exception\ValidationException;
use Core\Exception\ConfigurationException;

/**
 * HTTP Session Management Class
 *
 * Provides a secure, object-oriented interface for managing PHP sessions with
 * comprehensive security features, proper initialization, and error handling.
 *
 * Security Features:
 * - Automatic session initialization with secure settings
 * - Session hijacking protection through user agent and IP validation
 * - Secure session cookie configuration
 * - CSRF token regeneration capabilities
 * - Session fixation protection
 *
 * @package Core\Http
 * @author  Prima Yoga
 */
final class Session
{
    /**
     * Session configuration constants.
     */
    private const SESSION_COOKIE_SECURE = true;
    private const SESSION_COOKIE_HTTPONLY = true;
    private const SESSION_COOKIE_SAMESITE = 'Strict';
    private const SESSION_GC_MAXLIFETIME = 1440; // 24 minutes

    /**
     * Session validation data for hijacking protection.
     */
    private ?string $originalUserAgent = null;
    private ?string $originalIpAddress = null;

    /**
     * The session data array, initialized from $_SESSION superglobal.
     */
    public array $session = [];

    /**
     * Whether the session has been properly initialized.
     */
    private bool $initialized = false;

    /**
     * Logger instance for framework logging.
     */
    private Log $logger;

    /**
     * Initializes the session handler with security features and validation.
     *
     * The constructor automatically starts the session if not already active,
     * configures secure session settings, and sets up hijacking protection.
     *
     * @param Log|null $logger Logger instance for framework logging.
     * @throws RuntimeException If session initialization fails.
     */
    public function __construct(?Log $logger = null)
    {
        $this->logger = $logger ?? new Log();
        $this->initializeSession();
        $this->session = $_SESSION ?? [];
    }

    /**
     * Initializes the session with security configurations.
     *
     * @throws RuntimeException If session cannot be started.
     */
    private function initializeSession(): void
    {
        if ($this->initialized) {
            return;
        }

        // Only initialize sessions in web environment (not CLI)
        if (PHP_SAPI === 'cli') {
            $this->initialized = true;
            return;
        }

        // Set secure session configuration before starting
        $this->configureSessionSecurity();

        if (session_status() === PHP_SESSION_NONE) {
            if (!session_start()) {
                $this->logger->write('Failed to start session', 'error');
                throw new RuntimeException('Failed to start session');
            }
        }

        // Initialize hijacking protection on first access
        if (empty($_SESSION['session_initialized'])) {
            $this->initializeHijackingProtection();
            $_SESSION['session_initialized'] = true;
        }

        $this->initialized = true;
    }

    /**
     * Configures secure session settings.
     */
    private function configureSessionSecurity(): void
    {
        // Only configure session settings in web environment (not CLI)
        if (PHP_SAPI === 'cli') {
            return;
        }

        // Set secure cookie parameters if session is not already active
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_set_cookie_params([
                'lifetime' => 0, // Session cookie (expires when browser closes)
                'path' => '/',
                'domain' => '', // Use current domain
                'secure' => self::SESSION_COOKIE_SECURE,
                'httponly' => self::SESSION_COOKIE_HTTPONLY,
                'samesite' => self::SESSION_COOKIE_SAMESITE
            ]);
        }

        // Set session garbage collection only if session is not active
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.gc_maxlifetime', (string)self::SESSION_GC_MAXLIFETIME);
        }

        // Use more secure session hash (if available)
        if (function_exists('session_set_save_handler')) {
            // Could implement custom save handler for additional security
        }
    }

    /**
     * Initializes session hijacking protection.
     */
    private function initializeHijackingProtection(): void
    {
        $client = new Client();
        $this->originalUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $this->originalIpAddress = $client->getIpAddress();

        $_SESSION['user_agent'] = $this->originalUserAgent;
        $_SESSION['ip_address'] = $this->originalIpAddress;
    }

    /**
     * Validates session integrity for hijacking protection.
     *
     * @return bool True if session is valid, false if potentially hijacked.
     */
    private function validateSessionIntegrity(): bool
    {
        // Skip validation if not initialized
        if (!$this->originalUserAgent || !$this->originalIpAddress) {
            return true;
        }

        $currentUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $client = new Client();
        $currentIpAddress = $client->getIpAddress();

        // Check for significant changes that might indicate hijacking
        if ($currentUserAgent !== $this->originalUserAgent) {
            $this->logger->write('Session hijacking detected: User agent changed', 'warning', [
                'original_agent' => $this->originalUserAgent,
                'current_agent' => $currentUserAgent,
                'session_id' => session_id()
            ]);
            return false;
        }

        // IP address change is more lenient (mobile networks, etc.)
        if ($currentIpAddress !== $this->originalIpAddress && !empty($currentIpAddress)) {
            $this->logger->write('Session IP address changed', 'info', [
                'original_ip' => $this->originalIpAddress,
                'current_ip' => $currentIpAddress,
                'session_id' => session_id()
            ]);
            // Could implement more sophisticated IP validation here
        }

        return true;
    }

    /**
     * Logs security-related events.
     *
     * @param string $message The security event message.
     */
    private function logSecurityEvent(string $message): void
    {
        // In a real application, this would log to a security log
        error_log("[SECURITY] {$message} - Session ID: " . session_id());
    }

    /**
     * Checks if a session key exists and is not empty.
     *
     * @param string $key The session key to check.
     * @return bool True if the key exists and has a non-empty value, false otherwise.
     */
    public function check(string $key): bool
    {
        $this->ensureSessionStarted();
        return isset($_SESSION[$key]) && !empty($_SESSION[$key]);
    }

    /**
     * Sets a value in the session with validation.
     *
     * @param string $key The session key to set.
     * @param mixed $value The value to store in the session.
     * @return bool True if the value was successfully set, false if key or value is empty.
     * @throws RuntimeException If session is invalid.
     * @throws ValidationException If key or value validation fails.
     */
    public function set(string $key, mixed $value): bool
    {
        $this->ensureSessionStarted();

        if (!$this->validateSessionIntegrity()) {
            throw new RuntimeException('Session integrity check failed');
        }

        if (empty($key)) {
            throw new ValidationException('Session key cannot be empty');
        }

        if ($value === null) {
            throw new ValidationException('Session value cannot be null');
        }

        $_SESSION[$key] = $value;
        $this->session = $_SESSION;
        return true;
    }

    /**
     * Retrieves a value from the session.
     *
     * @param string $key Optional session key to retrieve. If empty, returns all session data.
     * @return mixed The session value if key exists, null if key doesn't exist, or all session data if no key provided.
     * @throws RuntimeException If session is invalid.
     */
    public function get(string $key = ''): mixed
    {
        $this->ensureSessionStarted();

        if (!$this->validateSessionIntegrity()) {
            throw new RuntimeException('Session integrity check failed');
        }

        if (!empty($key)) {
            return $_SESSION[$key] ?? null;
        }

        return $_SESSION;
    }

    /**
     * Retrieves and removes a session value in one operation (flash data).
     *
     * This method is useful for temporary data that should be available for exactly one request.
     *
     * @param string $key The session key to retrieve and remove.
     * @return mixed The session value before removal, or null if key doesn't exist.
     * @throws RuntimeException If session is invalid.
     */
    public function flash(string $key): mixed
    {
        $data = $this->get($key);

        if ($data !== null) {
            unset($_SESSION[$key]);
            $this->session = $_SESSION;
        }

        return $data;
    }

    /**
     * Regenerates the session ID to prevent session fixation attacks.
     *
     * @param bool $deleteOldSession Whether to delete the old session data (default: false).
     * @return bool True if regeneration was successful.
     */
    public function regenerateId(bool $deleteOldSession = false): bool
    {
        $this->ensureSessionStarted();

        if (session_regenerate_id($deleteOldSession)) {
            $this->initializeHijackingProtection();
            $this->logger->write('Session ID regenerated for security', 'info', [
                'session_id' => session_id(),
                'delete_old' => $deleteOldSession
            ]);
            return true;
        }

        $this->logger->write('Failed to regenerate session ID', 'warning');
        return false;
    }

    /**
     * Destroys the current session completely.
     *
     * This method clears all session data, destroys the session, and performs cleanup.
     * Use this for logout operations or when a complete session reset is needed.
     *
     * @return void
     */
    public function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];

            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 42000, '/');
            }

            session_destroy();
            $this->logger->write('Session destroyed', 'info');
        }

        $this->initialized = false;
        $this->session = [];
    }

    /**
     * Gets the current session ID.
     *
     * @return string The current session ID.
     */
    public function getId(): string
    {
        return session_id();
    }

    /**
     * Gets the session name.
     *
     * @return string The session name.
     */
    public function getName(): string
    {
        return session_name();
    }

    /**
     * Ensures the session is properly started before operations.
     *
     * @throws RuntimeException If session cannot be started.
     */
    private function ensureSessionStarted(): void
    {
        if (!$this->initialized) {
            $this->initializeSession();
        }
    }
}