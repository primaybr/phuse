<?php

declare(strict_types=1);

namespace Core\Security;

class Encryption
{
    // Declare the ciphering method as a constant
    public const CIPHERING = 'aes-256-cbc-hmac-sha256';

    // Use constructor property promotion to initialize the ciphering property
    public function __construct( private string $ciphering = self::CIPHERING ) {}

    // Use the nullsafe operator to avoid null check conditions
    public function generateKey(string $string): string
    {
        // Use OpenSSl digest method
        $key = openssl_digest($string, 'sha512', true);
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
	
    // Use named arguments to specify the options and iv parameters
    public function decrypt(string $encryption, string $decryptionKey, string $encryptionSalt): string
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
