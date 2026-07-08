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
 * script-src also allow-lists the exact SHA-256 hash of an inline FOUC-
 * prevention snippet (App/Views/_shared/theme-init.php in Vertext, the
 * primary consumer of this framework) - a small, static, first-party
 * script with no per-request variation, so its hash never changes. This
 * is the CSP-recommended way to allow one specific known-safe inline
 * script without weakening the policy generally: any actually-injected
 * malicious inline script has different content and therefore a
 * different hash, so it's still blocked. Harmless as an unused allow-list
 * entry in a Phuse app that doesn't ship that exact script. Recompute via:
 *   php -r '$c=file_get_contents("App/Views/_shared/theme-init.php");
 *   preg_match("/<script>(.*)<\/script>/s",$c,$m);
 *   echo base64_encode(hash("sha256",$m[1],true));'
 * if that script's content ever changes.
 *
 * @package Core\Middleware
 * @author  Prima Yoga
 */
class SecurityHeadersMiddleware implements MiddlewareInterface
{
    private const CSP = "default-src 'self'; script-src 'self' 'sha256-oDYWwGoPMMLZnC4nXKXi7EA6Ad5mbokl8Ye1cMFUfJk='; style-src 'self'; img-src 'self' data: blob:; font-src 'self' data:; frame-ancestors 'none'";

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
