<?php

declare(strict_types=1);

namespace Core\Security;

/**
 * Password Hashing Helper
 *
 * Thin, opinionated wrapper around PHP's native password_hash()/password_verify()
 * so applications have one framework-blessed way to hash and verify passwords
 * instead of reaching for openssl/Encryption or rolling their own hashing.
 *
 * @package Core\Security
 * @author  Prima Yoga
 */
class Password
{
    /**
     * The hashing algorithm used by hash(). Argon2id when the extension is
     * available (PHP built with --with-password-argon2), bcrypt otherwise.
     */
    private const ALGO = PASSWORD_ARGON2ID;

    /**
     * Hashes a plaintext password.
     *
     * @param string $plain The plaintext password.
     * @return string The password hash.
     */
    public static function hash(string $plain): string
    {
        $algo = defined('PASSWORD_ARGON2ID') ? self::ALGO : PASSWORD_BCRYPT;

        return password_hash($plain, $algo);
    }

    /**
     * Verifies a plaintext password against a hash produced by hash().
     *
     * @param string $plain The plaintext password to check.
     * @param string $hash The stored password hash.
     * @return bool True if the password matches the hash.
     */
    public static function verify(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    /**
     * Checks whether a hash was produced with older/weaker options than hash()
     * currently uses, so callers can re-hash and update storage after a
     * successful verify() (e.g. after upgrading PHP or changing ALGO).
     *
     * @param string $hash The stored password hash.
     * @return bool True if the hash should be regenerated.
     */
    public static function needsRehash(string $hash): bool
    {
        $algo = defined('PASSWORD_ARGON2ID') ? self::ALGO : PASSWORD_BCRYPT;

        return password_needs_rehash($hash, $algo);
    }
}
