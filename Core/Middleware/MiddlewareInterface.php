<?php

declare(strict_types=1);

namespace Core\Middleware;

/**
 * Middleware Interface
 *
 * Defines the contract for middleware components that can process HTTP requests
 * and responses in the Phuse framework.
 *
 * @package Core\Middleware
 * @author  Prima Yoga
 */
interface MiddlewareInterface
{
    /**
     * Process the request and return a response.
     *
     * Middleware can modify the request before passing it to the next middleware,
     * or modify the response after the next middleware has processed it.
     *
     * @param callable $next The next middleware or the final application handler.
     * @return mixed The response from the middleware chain.
     */
    public function process(callable $next): mixed;
}
