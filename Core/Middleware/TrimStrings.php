<?php

declare(strict_types=1);

namespace Core\Middleware;

/**
 * Trim Strings Middleware
 *
 * Trims leading/trailing whitespace from every string value in $_GET and $_POST
 * before the request reaches the application.
 *
 * @package Core\Middleware
 * @author  Prima Yoga
 */
class TrimStrings implements MiddlewareInterface
{
    /**
     * @param callable $next The next middleware or the final application handler.
     * @return mixed The response from $next().
     */
    public function process(callable $next): mixed
    {
        $_GET = $this->trim($_GET);
        $_POST = $this->trim($_POST);

        return $next();
    }

    /**
     * Recursively trims string values in an array.
     *
     * @param array $data The data to trim.
     * @return array The trimmed data.
     */
    private function trim(array $data): array
    {
        return array_map(
            fn ($value) => is_array($value) ? $this->trim($value) : (is_string($value) ? trim($value) : $value),
            $data
        );
    }
}
