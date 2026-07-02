<?php

declare(strict_types=1);

namespace Core\Middleware;

/**
 * Convert Empty Strings To Null Middleware
 *
 * Converts every empty-string value in $_GET and $_POST to null before the request
 * reaches the application - useful ahead of database inserts where an empty string
 * and "no value" should be treated the same way.
 *
 * @package Core\Middleware
 * @author  Prima Yoga
 */
class ConvertEmptyStringsToNull implements MiddlewareInterface
{
    /**
     * @param callable $next The next middleware or the final application handler.
     * @return mixed The response from $next().
     */
    public function process(callable $next): mixed
    {
        $_GET = $this->convert($_GET);
        $_POST = $this->convert($_POST);

        return $next();
    }

    /**
     * Recursively converts empty-string values in an array to null.
     *
     * @param array $data The data to convert.
     * @return array The converted data.
     */
    private function convert(array $data): array
    {
        return array_map(
            fn ($value) => is_array($value) ? $this->convert($value) : ($value === '' ? null : $value),
            $data
        );
    }
}
