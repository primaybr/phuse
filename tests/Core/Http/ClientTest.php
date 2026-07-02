<?php

declare(strict_types=1);

namespace Tests\Core\Http;

use PHPUnit\Framework\TestCase;
use Core\Http\Client;

/**
 * Regression tests for the v1.2.5 IP-spoofing fix: Client::getIpAddress() must only trust
 * forwarding headers (X-Forwarded-For, CF-Connecting-IP, etc.) when REMOTE_ADDR is explicitly
 * registered via setTrustedProxies() - otherwise any client could forge their own IP.
 */
class ClientTest extends TestCase
{
    protected function setUp(): void
    {
        // Resets the internal IP cache as a side effect.
        Client::setTrustedProxies([]);

        unset(
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_X_FORWARDED_FOR'],
            $_SERVER['HTTP_CF_CONNECTING_IP']
        );
    }

    protected function tearDown(): void
    {
        Client::setTrustedProxies([]);
    }

    public function testUsesRemoteAddrByDefault(): void
    {
        $_SERVER['REMOTE_ADDR'] = '203.0.113.10';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.99';

        $client = new Client();

        // No trusted proxies configured - the forwarding header must be ignored entirely.
        $this->assertSame('203.0.113.10', $client->getIpAddress());
    }

    public function testIgnoresForwardingHeaderFromUntrustedRemoteAddr(): void
    {
        Client::setTrustedProxies(['10.0.0.1']); // some other, trusted proxy
        $_SERVER['REMOTE_ADDR'] = '203.0.113.10'; // direct connection is NOT the trusted proxy
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.99';

        $client = new Client();

        $this->assertSame('203.0.113.10', $client->getIpAddress());
    }

    public function testHonorsForwardingHeaderFromTrustedProxy(): void
    {
        Client::setTrustedProxies(['203.0.113.10']);
        $_SERVER['REMOTE_ADDR'] = '203.0.113.10'; // the trusted proxy itself
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.99';

        $client = new Client();

        $this->assertSame('198.51.100.99', $client->getIpAddress());
    }

    public function testReturnsUnknownWhenNoAddressAvailable(): void
    {
        $client = new Client();

        $this->assertSame('UNKNOWN', $client->getIpAddress());
    }

    public function testResultIsCachedUntilTrustedProxiesChange(): void
    {
        $_SERVER['REMOTE_ADDR'] = '203.0.113.10';
        $client = new Client();

        $first = $client->getIpAddress();

        // Changing REMOTE_ADDR after the first call should not affect the cached result...
        $_SERVER['REMOTE_ADDR'] = '203.0.113.99';
        $this->assertSame($first, $client->getIpAddress());

        // ...until setTrustedProxies() explicitly invalidates the cache.
        Client::setTrustedProxies([]);
        $this->assertSame('203.0.113.99', $client->getIpAddress());
    }
}
