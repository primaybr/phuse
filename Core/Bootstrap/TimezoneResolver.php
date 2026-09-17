<?php

declare(strict_types=1);

namespace Core\Bootstrap;

/**
 * Applies the app's runtime default timezone, once per process/request -
 * PHP's default timezone isn't persistent, so this must run on every request
 * (both admin and front-end pass through Core\Base::run()'s single
 * MiddlewareStack, so calling this there covers both surfaces uniformly).
 *
 * Two layers, mirroring Config\Database.php's own env-var-first idiom:
 *  1. Config\Config.php's 'timezone' key (env var or a safe hardcoded
 *     default) - always available, no DB required, covers pre-install/setup
 *     wizard requests where the settings table doesn't exist yet.
 *  2. The live `settings.timezone` admin setting, if present and valid -
 *     read the same defensive try/catch-around-a-Model-read shape already
 *     proven safe by ThemeEngine::activeTheme()/siteSettings().
 */
final class TimezoneResolver
{
    public static function apply(string $configDefault): void
    {
        date_default_timezone_set(self::isValid($configDefault) ? $configDefault : 'UTC');

        try {
            $row = (new \Core\Model('settings'))
                ->select('value')
                ->where('key', 'timezone')
                ->get(1);

            if ($row && !empty($row['value']) && self::isValid($row['value'])) {
                date_default_timezone_set($row['value']);
            }
        } catch (\Throwable) {
            // Keep the Config-layer default - DB not ready yet (pre-install) or unavailable.
        }
    }

    public static function isValid(string $timezone): bool
    {
        return in_array($timezone, \DateTimeZone::listIdentifiers(), true);
    }
}
