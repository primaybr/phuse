<?php

declare(strict_types=1);

namespace Core\Exception;

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
