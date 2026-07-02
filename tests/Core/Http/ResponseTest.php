<?php

declare(strict_types=1);

namespace Tests\Core\Http;

use PHPUnit\Framework\TestCase;
use Core\Http\Response;

class ResponseTest extends TestCase
{
    public function testDefaultsToStatus200Ok(): void
    {
        $response = new Response();

        $this->assertSame(200, $response->statusCode);
        $this->assertSame('OK', $response->statusName);
        $this->assertSame('http', $response->wrapper);
    }

    public function testCustomStatusCodeResolvesName(): void
    {
        $response = new Response(404);

        $this->assertSame(404, $response->statusCode);
        $this->assertSame('Not Found', $response->statusName);
    }

    public function testUnknownStatusCodeFallsBack(): void
    {
        $response = new Response(999);

        $this->assertSame('Unknown Status Code', $response->statusName);
    }

    public function testCustomWrapper(): void
    {
        $response = new Response(200, 'https');

        $this->assertSame('https', $response->wrapper);
    }

    public function testStatusCodesConstantContainsCommonCodes(): void
    {
        $this->assertSame('OK', Response::STATUS_CODES[200]);
        $this->assertSame('Not Found', Response::STATUS_CODES[404]);
        $this->assertSame('Too Many Requests', Response::STATUS_CODES[429]);
        $this->assertSame('Internal Server Error', Response::STATUS_CODES[500]);
    }
}
