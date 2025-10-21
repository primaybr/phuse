<?php

declare(strict_types=1);

namespace Tests\Core\Security;

use PHPUnit\Framework\TestCase;
use Core\Security\CSRF;
use Core\Http\Session;

class CSRFTest extends TestCase
{
    private CSRF $csrf;

    protected function setUp(): void
    {
        // Mock session for testing
        $_SESSION = [];
        $this->csrf = new CSRF();
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testGenerateToken(): void
    {
        $token = $this->csrf->generateToken();

        $this->assertIsString($token);
        $this->assertEquals(64, strlen($token)); // 32 bytes * 2 for hex

        // Check that token is stored in session
        $stored = $_SESSION['csrf_token'];
        $this->assertIsArray($stored);
        $this->assertEquals($token, $stored['token']);
        $this->assertIsInt($stored['expires']);
        $this->assertGreaterThan(time(), $stored['expires']);
    }

    public function testGetToken(): void
    {
        $token1 = $this->csrf->getToken();
        $token2 = $this->csrf->getToken();

        $this->assertEquals($token1, $token2);
    }

    public function testValidateToken(): void
    {
        $token = $this->csrf->generateToken();

        $this->assertTrue($this->csrf->validateToken($token));
        $this->assertFalse($this->csrf->validateToken('invalid_token'));
    }

    public function testTokenExpiration(): void
    {
        $token = $this->csrf->generateToken();

        // Simulate token expiration by directly modifying session
        $_SESSION['csrf_token']['expires'] = time() - 1;

        $this->assertFalse($this->csrf->validateToken($token));
    }

    public function testRemoveToken(): void
    {
        $this->csrf->generateToken();
        $this->assertArrayHasKey('csrf_token', $_SESSION);

        $this->csrf->removeToken();
        $this->assertArrayNotHasKey('csrf_token', $_SESSION);
    }

    public function testGetTokenInput(): void
    {
        $input = $this->csrf->getTokenInput();

        $this->assertIsString($input);
        $this->assertStringContainsString('<input type="hidden"', $input);
        $this->assertStringContainsString('name="csrf_token"', $input);
        $this->assertStringContainsString('value="', $input);
    }
}
