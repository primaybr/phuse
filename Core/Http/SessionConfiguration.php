<?php

declare(strict_types=1);

namespace Core\Http;

/**
 * Session configuration policy shared by the HTTP session bootstrap and tests.
 */
final class SessionConfiguration
{
    public const DEFAULT_LIFETIME_SECONDS = 2592000;
    public const DEFAULT_DRIVER = 'files';

    private const MINIMUM_LIFETIME_SECONDS = 300;
    private const MAXIMUM_LIFETIME_SECONDS = 31536000;

    public static function lifetimeSeconds(string|false|null $configured): int
    {
        if (!is_string($configured) || !ctype_digit($configured)) {
            return self::DEFAULT_LIFETIME_SECONDS;
        }

        $seconds = (int) $configured;
        if ($seconds < self::MINIMUM_LIFETIME_SECONDS || $seconds > self::MAXIMUM_LIFETIME_SECONDS) {
            return self::DEFAULT_LIFETIME_SECONDS;
        }

        return $seconds;
    }

    public static function usesFilesystemHandler(string|false|null $handler): bool
    {
        return is_string($handler) && strtolower(trim($handler)) === 'files';
    }

    public static function driver(string|false|null $configured): string
    {
        if (!is_string($configured)) {
            return self::DEFAULT_DRIVER;
        }

        $driver = strtolower(trim($configured));

        return $driver !== '' ? $driver : self::DEFAULT_DRIVER;
    }

    public static function isDurableDriver(string $driver): bool
    {
        return in_array(self::driver($driver), ['database', 'redis'], true);
    }

    public static function requiresDurableStore(string|false|null $environment): bool
    {
        return is_string($environment) && strtolower(trim($environment)) === 'production';
    }
}
