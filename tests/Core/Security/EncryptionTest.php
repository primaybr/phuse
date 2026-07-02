<?php

declare(strict_types=1);

namespace Tests\Core\Security;

use PHPUnit\Framework\TestCase;
use Core\Security\Encryption;

class EncryptionTest extends TestCase
{
    private Encryption $encryption;

    protected function setUp(): void
    {
        $this->encryption = new Encryption();
    }

    public function testGenerateKeyReturnsKeyAndSalt(): void
    {
        $combined = $this->encryption->generateKey('app-secret');

        $this->assertIsString($combined);
        $this->assertStringContainsString('/', $combined);

        [$key, $salt] = explode('/', $combined);
        $this->assertNotEmpty($key);
        $this->assertNotEmpty($salt);
        $this->assertTrue(ctype_xdigit($key));
        $this->assertTrue(ctype_xdigit($salt));
    }

    public function testGenerateKeyProducesFreshSaltEachCall(): void
    {
        $first = $this->encryption->generateKey('same-secret');
        $second = $this->encryption->generateKey('same-secret');

        [$keyA, $saltA] = explode('/', $first);
        [$keyB, $saltB] = explode('/', $second);

        // Same secret -> same derived key, but a fresh random salt each time
        $this->assertSame($keyA, $keyB);
        $this->assertNotSame($saltA, $saltB);
    }

    public function testEncryptDecryptRoundTrip(): void
    {
        [$key, $salt] = explode('/', $this->encryption->generateKey('app-secret'));

        $plaintext = 'sensitive data';
        $ciphertext = $this->encryption->encrypt($plaintext, $key, $salt);

        $this->assertNotSame($plaintext, $ciphertext);

        $decrypted = $this->encryption->decrypt($ciphertext, $key, $salt);
        $this->assertSame($plaintext, $decrypted);
    }

    public function testDecryptFailsWithWrongSalt(): void
    {
        [$key, $salt] = explode('/', $this->encryption->generateKey('app-secret'));
        [, $wrongSalt] = explode('/', $this->encryption->generateKey('app-secret'));

        $ciphertext = $this->encryption->encrypt('sensitive data', $key, $salt);
        $decrypted = $this->encryption->decrypt($ciphertext, $key, $wrongSalt);

        // CBC's padding check legitimately rejects wrong-IV decryption outright (false),
        // rather than silently returning garbage plaintext.
        $this->assertFalse($decrypted);
    }
}
