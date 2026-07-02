<?php

declare(strict_types=1);

namespace Core\Security;

class Encryption
{
    // aes-256-cbc-hmac-sha256 is listed by openssl_get_cipher_methods() but fails with
    // "cipher operation failed" under openssl_encrypt()/openssl_decrypt() on OpenSSL 3.x's
    // default providers - it was never actually functional. aes-256-cbc is the standard,
    // fully-supported equivalent (same 32-byte key / 16-byte IV requirements).
    public const CIPHERING = 'aes-256-cbc';

    // Use constructor property promotion to initialize the ciphering property
    public function __construct( private string $ciphering = self::CIPHERING ) {}

    // Use the nullsafe operator to avoid null check conditions
    public function generateKey(string $string): string
    {
        // sha256 produces a 32-byte digest - the exact key length aes-256-cbc requires.
        // sha512 (64 bytes) was used previously, which openssl_encrypt() silently rejects
        // with "invalid key length", making encrypt()/decrypt() non-functional.
        $key = openssl_digest($string, 'sha256', true);
        $salt = $this->generateSalt();

        // Use the null coalescing operator to return an empty string if the result is null
        $return = bin2hex($key ?? '') . '/' . bin2hex($salt ?? '');

        return $return;
    }

    // Use the null coalescing operator to return a default value if the result is null
    public function generateSalt(): string
    {
        // Use OpenSSl ciphering method to get length
        $length = openssl_cipher_iv_length($this->ciphering);

        // Use random_bytes() function which gives random values based on the ciphering length
        $salt = random_bytes($length) ?? '';

        return $salt;
    }
	
	// Use named arguments to specify the options and iv parameters
    public function encrypt(string $string, string $encryptionKey, string $encryptionSalt): string
    {
        $encryptionKey = hex2bin($encryptionKey);
        $encryptionSalt = hex2bin($encryptionSalt);
        // Encryption of string process starts
        $encryption = openssl_encrypt(
            $string,
            $this->ciphering,
            $encryptionKey,
            options: 0,
            iv: $encryptionSalt
        );

        return $encryption;
    }
	
    // Decryption legitimately fails (returns false) when given the wrong key/IV or corrupted
    // ciphertext - CBC's padding validation rejects it. That's expected behavior, not an error
    // state, so the return type reflects it rather than crashing with a TypeError.
    public function decrypt(string $encryption, string $decryptionKey, string $encryptionSalt): string|false
    {
        $decryptionKey = hex2bin($decryptionKey);
        $encryptionSalt = hex2bin($encryptionSalt);
        // Description of string process starts
        $decryption = openssl_decrypt(
            $encryption,
            $this->ciphering,
            $decryptionKey,
            options: 0,
            iv: $encryptionSalt
        );

        return $decryption;
    }
}
