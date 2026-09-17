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
    private const SESSION_COOKIE_HTTPONLY = true;
    // 'Lax', not 'Strict': a Strict cookie is withheld by the browser on a
    // cross-site top-level navigation, which is exactly what a third-party
    // OAuth provider's redirect back to a callback route is - the session
    // carrying any pending oauth-style state would silently not arrive.
    // Lax still blocks the cross-site POST/subrequest cases CSRF actually
    // relies on, and state-changing endpoints validate their own CSRF token
    // independently regardless. (Confirmed breaking Carikno's Google
    // Sign-In callback - mirrored here since this class is shared.)
    private const SESSION_COOKIE_SAMESITE = 'Lax';
    private static ?DatabaseSessionHandler $databaseSessionHandler = null;

    /**
     * Determine if secure cookie should be used based on environment.
     */
    private static function isSecureConnection(): bool
    {
        // Check if running via CLI
        if (PHP_SAPI === 'cli') {
            return false;
        }

        // Check various HTTPS indicators
        return (
            (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === '1')) ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
            (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
        );
    }

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
        
        // Only initialize if session is not already active
        if (session_status() === PHP_SESSION_NONE) {
            $this->initializeSession();
        }
        
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

        $lifetime = SessionConfiguration::lifetimeSeconds(getenv('SESSION_LIFETIME_SECONDS'));
        $sessionDriver = SessionConfiguration::driver(getenv('SESSION_DRIVER'));

        $this->configureSessionDriver($sessionDriver, $lifetime);

        // Set secure cookie parameters if session is not already active
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_set_cookie_params([
                'lifetime' => $lifetime,
                'path' => '/',
                'domain' => '', // Use current domain
                'secure' => self::isSecureConnection(),
                'httponly' => self::SESSION_COOKIE_HTTPONLY,
                'samesite' => self::SESSION_COOKIE_SAMESITE
            ]);
        }

        // Set session save path and garbage collection only if session is not active
        if (session_status() === PHP_SESSION_NONE) {
            // A non-file handler (for example Redis) owns its own connection
            // string in session.save_path. Treating that value as a directory
            // would overwrite it with a pod-local temporary path and break
            // shared sessions after an ID rotation or a scale-out.
            if (SessionConfiguration::usesFilesystemHandler(ini_get('session.save_handler'))) {
                $sessionPath = ini_get('session.save_path');
                if (empty($sessionPath) || !is_dir($sessionPath) || !is_writable($sessionPath)) {
                    // Fallback to system temp directory if default path is not writable
                    $sessionPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'php_sessions';
                    if (!is_dir($sessionPath)) {
                        mkdir($sessionPath, 0777, true);
                    }
                    ini_set('session.save_path', $sessionPath);
                }
            }
            ini_set('session.gc_maxlifetime', (string) $lifetime);
            ini_set('session.use_strict_mode', '1'); // reject uninitialized session IDs (session fixation)
            ini_set('session.cookie_lifetime', (string) $lifetime);
        }

        // Apply GC settings even if session was already started (e.g. CSRF
        // instantiated first) - ini_set on a live session only affects the
        // next session_start(), but setting cookie params via the cookie
        // itself ensures the browser persists the cookie for the full TTL.
        if (session_status() === PHP_SESSION_ACTIVE) {
            ini_set('session.gc_maxlifetime', (string) $lifetime);
            ini_set('session.use_strict_mode', '1');
        }

        // Use more secure session hash (if available)
        if (function_exists('session_set_save_handler')) {
            // Could implement custom save handler for additional security
        }
    }

    private function configureSessionDriver(string $driver, int $lifetime): void
    {
        $environment = getenv('APP_ENV');

        if (SessionConfiguration::requiresDurableStore($environment) && !SessionConfiguration::isDurableDriver($driver)) {
            throw new ConfigurationException('Production requires a durable SESSION_DRIVER (database or redis).');
        }

        if ($driver === 'database') {
            $this->configureDatabaseSessionHandler($lifetime);
            return;
        }

        if ($driver === 'redis' && SessionConfiguration::driver(ini_get('session.save_handler')) !== 'redis') {
            throw new ConfigurationException('SESSION_DRIVER=redis requires PHP session.save_handler=redis.');
        }

        if (!in_array($driver, ['files', 'redis'], true)) {
            throw new ConfigurationException(sprintf('Unsupported SESSION_DRIVER "%s".', $driver));
        }
    }

    private function configureDatabaseSessionHandler(int $lifetime): void
    {
        if (self::$databaseSessionHandler !== null) {
            return;
        }

        $database = new \Config\Database();
        $connection = $database->getConnectionConfig();

        if (strtolower((string) ($connection['driver'] ?? '')) !== 'pgsql') {
            throw new ConfigurationException('SESSION_DRIVER=database requires PostgreSQL.');
        }

        try {
            $pdo = new \PDO(
                sprintf(
                    'pgsql:host=%s;port=%s;dbname=%s',
                    (string) ($connection['host'] ?? ''),
                    (string) ($connection['port'] ?? '5432'),
                    (string) ($connection['database'] ?? '')
                ),
                (string) ($connection['username'] ?? ''),
                (string) ($connection['password'] ?? ''),
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
        } catch (\PDOException $exception) {
            throw new ConfigurationException('Unable to connect to the database session store.', previous: $exception);
        }

        $handler = new DatabaseSessionHandler(new PostgresSessionStore($pdo), $lifetime);

        if (!session_set_save_handler($handler, true)) {
            throw new ConfigurationException('Unable to register the database session handler.');
        }

        self::$databaseSessionHandler = $handler;
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
        // HIJACKING FIX: read stored values from $_SESSION instead of instance
        // properties - a fresh Session instance on each request had null
        // originals, so validation was always skipped after the first request.
        $storedUserAgent = $_SESSION['user_agent'] ?? null;
        $storedIpAddress = $_SESSION['ip_address'] ?? null;

        // Skip validation if not initialized
        if (!$storedUserAgent || !$storedIpAddress) {
            return true;
        }

        $currentUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $client = new Client();
        $currentIpAddress = $client->getIpAddress();

        $this->originalUserAgent = $storedUserAgent;
        $this->originalIpAddress = $storedIpAddress;

        // Check for significant changes that might indicate hijacking
        if ($currentUserAgent !== $storedUserAgent) {
            $this->logger->write('Session hijacking detected: User agent changed', 'warning', [
                'original_agent' => $storedUserAgent,
                'current_agent' => $currentUserAgent,
                'session_id' => session_id()
            ]);

            // Invalidate compromised session state and issue clean guest session
            // instead of throwing an unhandled 500 RuntimeException.
            $_SESSION = [];
            $this->session = [];
            if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
                session_regenerate_id(true);
            }
            $this->initializeHijackingProtection();
            return true;
        }

        // IP address change is more lenient (mobile networks, etc.)
        if ($currentIpAddress !== $storedIpAddress && !empty($currentIpAddress)) {
            $this->logger->write('Session IP address changed', 'info', [
                'original_ip' => $this->originalIpAddress,
                'current_ip' => $currentIpAddress,
                'session_id' => session_id()
            ]);
            $_SESSION['ip_address'] = $currentIpAddress;
            $this->originalIpAddress = $currentIpAddress;
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
            // Setting null removes the key - callers use set($key, null) to
            // clear one-time state (pending flags, consumed challenges).
            unset($_SESSION[$key]);
            $this->session = $_SESSION;
            return true;
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
     * Ensures the session is started, but only if not already active.
     */
    private function ensureSessionStarted(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $this->initializeSession();
        }
    }

}