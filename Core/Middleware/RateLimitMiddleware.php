<?php

declare(strict_types=1);

namespace Core\Middleware;

use Core\Cache\CacheManager;

/**
 * Rate Limit Middleware
 *
 * Limits how many times a keyed action (e.g. a login attempt, an API key) may run within a
 * rolling time window, backed by Core\Cache\CacheManager for counter storage.
 *
 * @package Core\Middleware
 * @author  Prima Yoga
 */
class RateLimitMiddleware implements MiddlewareInterface
{
    /**
     * @param string $key Unique identifier for the thing being limited (e.g. "login:{$ip}").
     * @param int $maxAttempts Maximum attempts allowed within the window.
     * @param int $windowSeconds Length of the rolling window in seconds.
     * @param string $cacheName Named cache instance to store counters in.
     */
    public function __construct(
        private string $key,
        private int $maxAttempts = 60,
        private int $windowSeconds = 60,
        private string $cacheName = 'rate_limit'
    ) {
    }

    /**
     * Process the request, halting with a 429 response if the limit was exceeded.
     *
     * @param callable $next The next middleware or the final application handler.
     * @return mixed The response from $next(), or a 429 message if rate-limited.
     */
    public function process(callable $next): mixed
    {
        $cache = CacheManager::get($this->cacheName);
        $now = time();
        $state = $cache->get($this->key);

        if (!is_array($state) || !isset($state['resetAt']) || $state['resetAt'] <= $now) {
            $state = ['count' => 0, 'resetAt' => $now + $this->windowSeconds];
        }

        $state['count']++;
        $cache->set($this->key, $state, $this->windowSeconds);

        if ($state['count'] > $this->maxAttempts) {
            http_response_code(429);
            header('Retry-After: ' . max(0, $state['resetAt'] - $now));
            return 'Too Many Requests';
        }

        return $next();
    }
}
