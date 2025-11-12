<?php

declare(strict_types=1);

namespace Core\Exception;

/**
 * Exception class for cache-related errors
 */
class CacheException extends \Exception
{
    /**
     * Exception type constants
     */
    public const TYPE_CONNECTION_ERROR = 'connection_error';
    public const TYPE_INVALID_KEY = 'invalid_key';
    public const TYPE_CACHE_NOT_FOUND = 'cache_not_found';
    public const TYPE_PERMISSION_DENIED = 'permission_denied';
    public const TYPE_SERIALIZATION_ERROR = 'serialization_error';
    public const TYPE_CONFIGURATION_ERROR = 'configuration_error';

    /**
     * Exception type
     */
    private string $type;

    /**
     * Additional context data
     */
    private array $context;

    /**
     * Constructor
     */
    public function __construct(
        string $message,
        string $type = self::TYPE_CONNECTION_ERROR,
        array $context = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->type = $type;
        $this->context = $context;
    }

    /**
     * Get exception type
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get context data
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Create cache not found exception
     */
    public static function cacheNotFound(string $key, array $context = []): self
    {
        return new self(
            "Cache key '{$key}' not found",
            self::TYPE_CACHE_NOT_FOUND,
            $context
        );
    }

    /**
     * Create permission denied exception
     */
    public static function permissionDenied(string $path, array $context = []): self
    {
        return new self(
            "Permission denied for cache path: {$path}",
            self::TYPE_PERMISSION_DENIED,
            $context
        );
    }

    /**
     * Create invalid key exception
     */
    public static function invalidKey(string $key, array $context = []): self
    {
        return new self(
            "Invalid cache key: {$key}",
            self::TYPE_INVALID_KEY,
            $context
        );
    }

    /**
     * Create configuration error exception
     */
    public static function configurationError(string $message, array $context = []): self
    {
        return new self(
            $message,
            self::TYPE_CONFIGURATION_ERROR,
            $context
        );
    }

    /**
     * Create serialization error exception
     */
    public static function serializationError(string $message = 'Data serialization failed', array $context = []): self
    {
        return new self(
            $message,
            self::TYPE_SERIALIZATION_ERROR,
            $context
        );
    }
}
