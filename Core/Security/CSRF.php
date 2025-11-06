<?php

declare(strict_types=1);

namespace Core\Security;

use Core\Http\Session;
use Core\Log;
use Core\Exception\RuntimeException;
use Core\Exception\ValidationException;

/**
 * CSRF Protection Class
 *
 * Provides Cross-Site Request Forgery (CSRF) protection by generating and validating
 * unique tokens for form submissions. Tokens are stored in the user session and
 * validated against submitted data.
 *
 * This class integrates with the framework's session management system to provide
 * secure token-based CSRF protection with automatic expiration and validation.
 *
 * Features:
 * - Secure token generation using cryptographically strong random bytes
 * - Automatic token expiration after configurable time period
 * - Timing-safe token comparison using hash_equals()
 * - Easy integration with HTML forms
 * - Session-based token storage and retrieval
 *
 * @package Core\Security
 * @author  Prima Yoga
 */
class CSRF
{
    /**
     * The name of the session key used to store CSRF tokens.
     */
    private const TOKEN_NAME = 'csrf_token';

    /**
     * The length of generated CSRF tokens in bytes (32 bytes = 64 hex characters).
     */
    private const TOKEN_LENGTH = 32;

    /**
     * Token expiration time in seconds (default: 1 hour).
     */
    private const TOKEN_EXPIRY = 3600;

    /**
     * The session management instance for token storage.
     */
    private Session $session;

    /**
     * Logger instance for framework logging.
     */
    private Log $logger;

    /**
     * Initializes the CSRF protection system with session dependency.
     *
     * Creates a new Session instance for managing CSRF token storage and retrieval.
     *
     * @param Session|null $session Session instance for token storage.
     * @param Log|null $logger Logger instance for framework logging.
     */
    public function __construct(?Session $session = null, ?Log $logger = null)
    {
        $this->logger = $logger ?? new Log();
        $this->session = $session ?? new Session($this->logger);
    }

    /**
     * Generates a new CSRF token and stores it in the session.
     *
     * Creates a cryptographically secure random token, stores it in the session
     * with an expiration timestamp, and returns the token for use in forms.
     *
     * @return string The generated CSRF token in hexadecimal format.
     * @throws \Exception If random byte generation fails.
     */
    public function generateToken(): string
    {
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $this->session->set(self::TOKEN_NAME, [
            'token' => $token,
            'expires' => time() + self::TOKEN_EXPIRY,
        ]);

        return $token;
    }

    /**
     * Validates the provided CSRF token against the one stored in the session.
     *
     * Performs timing-safe comparison of the provided token with the stored token
     * and checks if the token has expired. Removes expired tokens from the session.
     *
     * @param string $token The token to validate against the stored token.
     * @return bool True if the token is valid and not expired, false otherwise.
     */
    public function validateToken(string $token): bool
    {
        $stored = $this->session->get(self::TOKEN_NAME);

        if (!$stored || !isset($stored['token'], $stored['expires'])) {
            return false;
        }

        if (time() > $stored['expires']) {
            $this->removeToken();
            return false;
        }

        return hash_equals($stored['token'], $token);
    }

    /**
     * Gets the current CSRF token, generating one if it doesn't exist or has expired.
     *
     * This is a convenience method that ensures a valid token is always available.
     * If no token exists or the current token has expired, a new one is generated.
     *
     * @return string The current or newly generated CSRF token.
     */
    public function getToken(): string
    {
        $stored = $this->session->get(self::TOKEN_NAME);

        if (!$stored || !isset($stored['token'], $stored['expires']) || time() > $stored['expires']) {
            return $this->generateToken();
        }

        return $stored['token'];
    }

    /**
     * Removes the current CSRF token from the session.
     *
     * @return void
     */
    public function removeToken(): void
    {
        $this->session->flash(self::TOKEN_NAME);
    }

    /**
     * Generates HTML input field for CSRF token to be used in forms.
     *
     * Creates a properly escaped hidden input field containing the current CSRF token.
     * This method should be called within form tags to include CSRF protection.
     *
     * @return string HTML input element with the CSRF token as a string.
     */
    public function getTokenInput(): string
    {
        $token = $this->getToken();
        return '<input type="hidden" name="' . htmlspecialchars(self::TOKEN_NAME) . '" value="' . htmlspecialchars($token) . '">';
    }
}
