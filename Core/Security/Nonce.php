<?php

declare(strict_types=1);

namespace Core\Security;

/**
 * Generates one cryptographically random nonce per request, cached for the
 * lifetime of the request so the same value can be used both in the CSP
 * header (App/helpers.php) and in every inline <script nonce="..."> tag
 * rendered into the page - the browser only allows a script through if its
 * nonce attribute matches the one in the header.
 */
final class Nonce
{
    private static ?string $value = null;

    public static function get(): string
    {
        if (self::$value === null) {
            self::$value = base64_encode(random_bytes(16));
        }

        return self::$value;
    }
}
