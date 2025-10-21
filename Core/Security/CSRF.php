<?php

declare(strict_types=1);

namespace Core\Security;

use Core\Http\Session;

/**
 * CSRF Protection Class
 *
 * Provides Cross-Site Request Forgery (CSRF) protection by generating and validating
 * unique tokens for form submissions. Tokens are stored in the user session and
 * validated against submitted data.
 *
 * @package Core\Security
 * @author  Prima Yoga
 */
class CSRF
{
    private const TOKEN_NAME = 'csrf_token';
    private const TOKEN_LENGTH = 32;
    private const TOKEN_EXPIRY = 3600; // 1 hour in seconds

    private Session $session;

    /**
     * Constructor initializes the session dependency.
     */
    public function __construct()
    {
        $this->session = new Session();
    }

    /**
     * Generates a new CSRF token and stores it in the session.
     *
     * @return string The generated CSRF token.
     * @throws \Exception If token generation fails.
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
     * @param string $token The token to validate.
     * @return bool True if the token is valid and not expired, false otherwise.
     */
    public function validateToken(string $token): bool
    {
        $stored = $this->session->get(self::TOKEN_NAME);

        if (!$stored || !isset($stored['token'], $stored['expires'])) {
            return false;
        }

        if (time() > $stored['expires']) {
            unset($_SESSION[self::TOKEN_NAME]);
            return false;
        }

        return hash_equals($stored['token'], $token);
    }

    /**
     * Gets the current CSRF token, generating one if it doesn't exist or has expired.
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
        unset($_SESSION[self::TOKEN_NAME]);
    }

    /**
     * Generates HTML input field for CSRF token to be used in forms.
     *
     * @return string HTML input element with the CSRF token.
     */
    public function getTokenInput(): string
    {
        $token = $this->getToken();
        return '<input type="hidden" name="' . htmlspecialchars(self::TOKEN_NAME) . '" value="' . htmlspecialchars($token) . '">';
    }
}
