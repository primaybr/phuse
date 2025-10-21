<?php

declare(strict_types=1);

namespace Core\Middleware;

use Core\Middleware\MiddlewareInterface;

/**
 * Middleware Stack
 *
 * Manages a stack of middleware components that process HTTP requests
 * in a Last-In-First-Out (LIFO) order.
 *
 * @package Core\Middleware
 * @author  Prima Yoga
 */
class MiddlewareStack
{
    /**
     * @var array<int, MiddlewareInterface> The stack of middleware components.
     */
    private array $middlewares = [];

    /**
     * @var callable The final handler to call when the middleware stack is exhausted.
     */
    private $handler;

    /**
     * Constructor.
     *
     * @param callable $handler The final handler to call after all middleware.
     */
    public function __construct(callable $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Add middleware to the stack.
     *
     * Middleware is added to the beginning of the stack, so it will be executed last.
     *
     * @param MiddlewareInterface $middleware The middleware to add.
     * @return self Returns the middleware stack for method chaining.
     */
    public function add(MiddlewareInterface $middleware): self
    {
        array_unshift($this->middlewares, $middleware);
        return $this;
    }

    /**
     * Process the request through the middleware stack.
     *
     * @return mixed The response from the middleware chain.
     */
    public function process(): mixed
    {
        $handler = $this->handler;

        foreach (array_reverse($this->middlewares) as $middleware) {
            $nextHandler = $handler;
            $handler = function () use ($middleware, $nextHandler) {
                return $middleware->process($nextHandler);
            };
        }

        return $handler();
    }
}
