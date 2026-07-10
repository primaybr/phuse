<?php

declare(strict_types=1);

namespace Core\Middleware;

use Core\Security\CSRF;

/**
 * CSRF Middleware
 *
 * Validates the CSRF token on every state-changing, session-cookie-based
 * request (POST, PUT, PATCH, DELETE) - session auth is forgeable by any
 * page a victim's browser visits, since the browser attaches cookies
 * automatically.
 *
 * Accepts the token either as a `csrf_token` POST field (traditional form
 * submits) or an `X-CSRF-Token` header (AJAX), whichever the caller sends.
 *
 * Requests carrying an `Authorization` header (Bearer/API-key auth, e.g. a
 * REST API keyed off `api_keys` rather than the session cookie) are exempt:
 * CSRF is a cookie-auth attack - a cross-site page can make a browser send
 * a request with cookies attached, but it cannot make the browser attach an
 * arbitrary Authorization header, so token/key-based clients were never
 * vulnerable to begin with. Enforcing CSRF on them anyway would just reject
 * every legitimate non-browser API caller with no security benefit.
 *
 * @package Core\Middleware
 * @author  Prima Yoga
 */
class CSRFMiddleware implements MiddlewareInterface
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function process(callable $next): mixed
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $hasAuthHeader = isset($_SERVER['HTTP_AUTHORIZATION']) && $_SERVER['HTTP_AUTHORIZATION'] !== '';

        if (!in_array($method, self::SAFE_METHODS, true) && !$hasAuthHeader) {
            $csrf = new CSRF();
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';

            if ($token === '' || !$csrf->validateToken($token)) {
                http_response_code(419);
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => false,
                    'message' => 'Invalid or missing CSRF token. Please refresh the page and try again.',
                    'data' => null,
                ]);
                exit;
            }
        }

        return $next();
    }
}
