<?php

declare(strict_types=1);

namespace Core\Middleware;

use Core\Config;

/**
 * Security Headers Middleware
 *
 * Applies a baseline set of security headers (CSP, X-Frame-Options,
 * X-Content-Type-Options, Referrer-Policy, and conditionally HSTS) to every
 * request - admin, public front-end, and the REST API alike. This is the one
 * call site all three surfaces pass through, so the policy is defined once
 * here instead of being duplicated per controller.
 *
 * The CSP here is intentionally stricter than what the admin panel needs
 * (no 'unsafe-inline'). Admin-specific pages that require it re-emit their
 * own Content-Security-Policy header afterward - PHP's header() replaces a
 * same-named header by default, so the later, more permissive value wins
 * only where it's explicitly set.
 *
 * @package Core\Middleware
 * @author  Prima Yoga
 */
class SecurityHeadersMiddleware implements MiddlewareInterface
{
    private const CSP = "default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data: blob:; font-src 'self' data:; frame-ancestors 'none'";

    /**
     * @param callable $next The next middleware or the final application handler.
     * @return mixed The response from $next().
     */
    public function process(callable $next): mixed
    {
        if (!headers_sent()) {
            header('Content-Security-Policy: ' . self::CSP);
            header('X-Frame-Options: DENY');
            header('X-Content-Type-Options: nosniff');
            header('Referrer-Policy: strict-origin-when-cross-origin');

            if ($this->isHttps()) {
                header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
            }
        }

        return $next();
    }

    /**
     * HSTS is keyed off the app's own 'https' config flag (the same flag that
     * already drives the session cookie's Secure attribute) rather than
     * re-deriving it from request headers - one source of truth, so a
     * reverse-proxy deployment can't have the two disagree.
     */
    private function isHttps(): bool
    {
        $config = (new Config())->get();
        return isset($config->https) && $config->https === true;
    }
}
