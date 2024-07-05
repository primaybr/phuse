<?php

declare(strict_types=1);

namespace Core\Http;

class Client
{
    // Use string type for the return value
    public function getIpAddress(): string
    {
        // Use short array syntax and foreach with value by reference
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'] as &$key) {
            // Use null coalescing operator to simplify the condition
            if ($_SERVER[$key] ?? false) {
                // Use array destructuring to assign the values to variables
                foreach (explode(',', $_SERVER[$key]) as [$ip]) {
                    $ip = trim($ip); // just to be safe

                    // Use named arguments to improve readability
                    if (filter_var($ip, filter: FILTER_VALIDATE_IP, options: FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            } else {
                return 'UNKNOWN';
            }
        }
    }
}
