<?php

declare(strict_types=1);

namespace Core\Exception;

/**
 * Database exceptions for database-related errors
 */
class DatabaseException extends BaseException
{
    public function __construct(
        string $message,
        string $type = self::TYPE_DATABASE_ERROR,
        string $severity = self::SEVERITY_HIGH,
        array $context = [],
        string $userMessage = '',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            $type,
            $severity,
            $context,
            $userMessage ?: 'A database error occurred. Please try again later.',
            $code,
            $previous
        );
    }

    /**
     * Create connection error exception
     */
    public static function connectionError(string $message = 'Database connection failed', array $context = []): self
    {
        return new self($message, self::TYPE_DATABASE_ERROR, self::SEVERITY_HIGH, $context, 'A database error occurred. Please try again later.', 1001);
    }

    /**
     * Create query error exception
     */
    public static function queryError(string $query, string $message = 'Database query failed', array $context = [], ?\Throwable $previous = null): self
    {
        $context['query'] = $query;
        return new self($message, self::TYPE_DATABASE_ERROR, self::SEVERITY_HIGH, $context, 'A database error occurred. Please try again later.', 1002, $previous);
    }
}
