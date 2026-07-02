<?php

declare(strict_types=1);

namespace Core\Middleware;

use Core\Log;

/**
 * Log Request Middleware
 *
 * Logs each request's method/URI on entry and exit (with duration) to a dedicated
 * "requests" log via Core\Log.
 *
 * @package Core\Middleware
 * @author  Prima Yoga
 */
class LogRequest implements MiddlewareInterface
{
    private Log $log;

    /**
     * @param Log|null $log Logger instance (a fresh one is created if omitted).
     */
    public function __construct(?Log $log = null)
    {
        $this->log = $log ?? new Log();
    }

    /**
     * @param callable $next The next middleware or the final application handler.
     * @return mixed The response from $next().
     */
    public function process(callable $next): mixed
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $start = microtime(true);

        $this->log->setLogName('requests')->write("-> {$method} {$uri}");

        $response = $next();

        $durationMs = round((microtime(true) - $start) * 1000, 2);
        $this->log->setLogName('requests')->write("<- {$method} {$uri} ({$durationMs}ms)");

        return $response;
    }
}
