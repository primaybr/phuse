<?php

declare(strict_types=1);

namespace Core\Exception;

/**
 * System-level exceptions for core framework errors
 */
class SystemException extends BaseException
{
    public function __construct(
        string $message,
        array $context = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            self::TYPE_SYSTEM_ERROR,
            self::SEVERITY_HIGH,
            $context,
            'A system error occurred. Please contact support.',
            $code,
            $previous
        );
    }
}

/**
 * Validation exceptions for input validation errors
 */
class ValidationException extends BaseException
{
    /**
     * Validation errors
     */
    private array $validationErrors;

    public function __construct(
        string $message = 'Validation failed',
        array $validationErrors = [],
        array $context = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $this->validationErrors = $validationErrors;

        parent::__construct(
            $message,
            self::TYPE_VALIDATION_ERROR,
            self::SEVERITY_MEDIUM,
            $context,
            'Please check your input and try again.',
            $code,
            $previous
        );
    }

    /**
     * Get validation errors
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    /**
     * Add validation error
     */
    public function addValidationError(string $field, string $error): self
    {
        $this->validationErrors[$field] = $error;
        return $this;
    }
}

/**
 * Database exceptions for database-related errors
 */
class DatabaseException extends BaseException
{
    public function __construct(
        string $message,
        array $context = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            self::TYPE_DATABASE_ERROR,
            self::SEVERITY_HIGH,
            $context,
            'A database error occurred. Please try again later.',
            $code,
            $previous
        );
    }

    /**
     * Create connection error exception
     */
    public static function connectionError(string $message = 'Database connection failed', array $context = []): self
    {
        return new self($message, $context, 1001);
    }

    /**
     * Create query error exception
     */
    public static function queryError(string $query, string $message = 'Database query failed', array $context = []): self
    {
        $context['query'] = $query;
        return new self($message, $context, 1002);
    }
}

/**
 * Filesystem exceptions for file operation errors
 */
class FilesystemException extends BaseException
{
    public function __construct(
        string $message,
        array $context = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            self::TYPE_FILESYSTEM_ERROR,
            self::SEVERITY_MEDIUM,
            $context,
            'A file system error occurred.',
            $code,
            $previous
        );
    }

    /**
     * Create file not found exception
     */
    public static function fileNotFound(string $path, array $context = []): self
    {
        $context['path'] = $path;
        return new self("File not found: {$path}", $context, 2001);
    }

    /**
     * Create permission denied exception
     */
    public static function permissionDenied(string $path, array $context = []): self
    {
        $context['path'] = $path;
        return new self("Permission denied: {$path}", $context, 2002);
    }
}

/**
 * Configuration exceptions for configuration-related errors
 */
class ConfigurationException extends BaseException
{
    public function __construct(
        string $message,
        array $context = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            self::TYPE_CONFIGURATION_ERROR,
            self::SEVERITY_HIGH,
            $context,
            'A configuration error occurred.',
            $code,
            $previous
        );
    }

    /**
     * Create missing configuration exception
     */
    public static function missingConfig(string $key, array $context = []): self
    {
        $context['config_key'] = $key;
        return new self("Missing configuration: {$key}", $context, 3001);
    }

    /**
     * Create invalid configuration exception
     */
    public static function invalidConfig(string $key, string $value, array $context = []): self
    {
        $context['config_key'] = $key;
        $context['config_value'] = $value;
        return new self("Invalid configuration value for {$key}: {$value}", $context, 3002);
    }
}

/**
 * Runtime exceptions for application runtime errors
 */
class RuntimeException extends BaseException
{
    public function __construct(
        string $message,
        array $context = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            self::TYPE_RUNTIME_ERROR,
            self::SEVERITY_MEDIUM,
            $context,
            'An application error occurred. Please try again.',
            $code,
            $previous
        );
    }
}
