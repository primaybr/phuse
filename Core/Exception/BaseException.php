<?php

declare(strict_types=1);

namespace Core\Exception;

/**
 * Base exception class for all Phuse framework exceptions
 *
 * Provides enhanced error handling with context information,
 * proper logging integration, and consistent error formatting.
 */
abstract class BaseException extends \Exception
{
    /**
     * Exception type constants for categorization
     */
    public const TYPE_SYSTEM_ERROR = 'system_error';
    public const TYPE_VALIDATION_ERROR = 'validation_error';
    public const TYPE_AUTHENTICATION_ERROR = 'authentication_error';
    public const TYPE_AUTHORIZATION_ERROR = 'authorization_error';
    public const TYPE_DATABASE_ERROR = 'database_error';
    public const TYPE_CACHE_ERROR = 'cache_error';
    public const TYPE_FILESYSTEM_ERROR = 'filesystem_error';
    public const TYPE_NETWORK_ERROR = 'network_error';
    public const TYPE_CONFIGURATION_ERROR = 'configuration_error';
    public const TYPE_RUNTIME_ERROR = 'runtime_error';

    /**
     * Exception severity levels
     */
    public const SEVERITY_LOW = 'low';
    public const SEVERITY_MEDIUM = 'medium';
    public const SEVERITY_HIGH = 'high';
    public const SEVERITY_CRITICAL = 'critical';

    /**
     * Exception type
     */
    private string $type;

    /**
     * Exception severity
     */
    private string $severity;

    /**
     * Additional context data
     */
    private array $context;

    /**
     * User-friendly message for display
     */
    private string $userMessage;

    /**
     * Constructor
     */
    public function __construct(
        string $message,
        string $type = self::TYPE_SYSTEM_ERROR,
        string $severity = self::SEVERITY_MEDIUM,
        array $context = [],
        string $userMessage = '',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->type = $type;
        $this->severity = $severity;
        $this->context = $context;
        $this->userMessage = $userMessage ?: $message;
    }

    /**
     * Get exception type
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get exception severity
     */
    public function getSeverity(): string
    {
        return $this->severity;
    }

    /**
     * Get context data
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Get user-friendly message
     */
    public function getUserMessage(): string
    {
        return $this->userMessage;
    }

    /**
     * Add context data
     */
    public function addContext(string $key, mixed $value): self
    {
        $this->context[$key] = $value;
        return $this;
    }

    /**
     * Set user-friendly message
     */
    public function setUserMessage(string $message): self
    {
        $this->userMessage = $message;
        return $this;
    }

    /**
     * Convert exception to array for logging
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'severity' => $this->severity,
            'message' => $this->message,
            'user_message' => $this->userMessage,
            'code' => $this->code,
            'file' => $this->file,
            'line' => $this->line,
            'context' => $this->context,
            'trace' => $this->getTraceAsString(),
        ];
    }

    /**
     * Get log level based on severity
     */
    public function getLogLevel(): string
    {
        return match ($this->severity) {
            self::SEVERITY_CRITICAL => 'error',
            self::SEVERITY_HIGH => 'error',
            self::SEVERITY_MEDIUM => 'warning',
            self::SEVERITY_LOW => 'info',
            default => 'error',
        };
    }
}
