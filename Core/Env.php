<?php

declare(strict_types=1);

namespace Core;

/**
 * Loads KEY=value pairs from a .env file into the process environment, so
 * secrets (DB credentials, API keys) never need to be hardcoded in versioned
 * PHP config files. No Composer dependency exists in this project, so this
 * is a minimal parser rather than vlucas/phpdotenv.
 *
 * @package Core
 * @author  Prima Yoga
 */
class Env
{
    private static bool $loaded = false;

    /**
     * Parses the given .env file (defaults to ROOT.'.env') and populates
     * getenv()/$_ENV/$_SERVER. Missing file is not an error - apps may rely
     * entirely on real environment variables set outside PHP (e.g. by the
     * web server or container). Safe to call more than once; only the first
     * call has any effect.
     */
    public static function load(?string $path = null): void
    {
        if (self::$loaded) {
            return;
        }
        self::$loaded = true;

        $path ??= ROOT . '.env';

        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $eqPos = strpos($line, '=');
            if ($eqPos === false) {
                continue;
            }

            $key   = trim(substr($line, 0, $eqPos));
            $value = trim(substr($line, $eqPos + 1));

            if ($key === '') {
                continue;
            }

            // Strip one layer of matching surrounding quotes, if present.
            if (strlen($value) >= 2) {
                $first = $value[0];
                $last  = $value[strlen($value) - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            if (getenv($key) === false) {
                putenv("{$key}={$value}");
                $_ENV[$key]    = $value;
                $_SERVER[$key] = $value;
            }
        }
    }

    /**
     * Reads a single environment variable, checking $_ENV first (fast path,
     * populated by load() above) and falling back to getenv() for variables
     * set outside this loader (e.g. by the web server).
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        $value = getenv($key);

        return $value === false ? $default : $value;
    }
}
