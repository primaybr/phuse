<?php

declare(strict_types=1);

namespace Tests\Core\Http;

use PHPUnit\Framework\TestCase;
use Core\Http\URI;
use Core\Exception\ValidationException;

class URITest extends TestCase
{
    private URI $uri;

    protected function setUp(): void
    {
        $this->uri = new URI();
    }

    public function testMakeURLSlugifiesString(): void
    {
        $this->assertSame('hello-world', $this->uri->makeURL('Hello World'));
    }

    public function testMakeURLThrowsOnEmptyInput(): void
    {
        $this->expectException(ValidationException::class);
        $this->uri->makeURL('   ');
    }

    public function testMakeURLStripsHtmlTags(): void
    {
        $this->assertSame('bold-text', $this->uri->makeURL('<b>Bold Text</b>'));
    }

    public function testGetProtocolDefaultsToHttp(): void
    {
        unset($_SERVER['HTTPS'], $_SERVER['HTTP_X_FORWARDED_PROTO']);
        $this->assertSame('http://', $this->uri->getProtocol());
    }

    public function testGetProtocolDetectsHttps(): void
    {
        $_SERVER['HTTPS'] = 'on';
        $this->assertSame('https://', $this->uri->getProtocol());
        unset($_SERVER['HTTPS']);
    }

    public function testGetHostStripsPort(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.com:8080';
        $this->assertSame('example.com', $this->uri->getHost());
        unset($_SERVER['HTTP_HOST']);
    }

    public function testGetCurrentURLReturnsRequestUri(): void
    {
        $_SERVER['REQUEST_URI'] = '/foo/bar?x=1';
        $this->assertSame('/foo/bar?x=1', $this->uri->getCurrentURL());
        unset($_SERVER['REQUEST_URI']);
    }

    public function testGetHttpRefererReturnsEmptyWhenMissing(): void
    {
        unset($_SERVER['HTTP_REFERER']);
        $this->assertSame('', $this->uri->getHttpReferer());
    }

    public function testGetYoutubeCodeFromStandardUrl(): void
    {
        $code = $this->uri->getYoutubeCode('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
        $this->assertSame('dQw4w9WgXcQ', $code);
    }

    public function testGetYoutubeCodeFromShortUrl(): void
    {
        $code = $this->uri->getYoutubeCode('https://youtu.be/dQw4w9WgXcQ');
        $this->assertSame('dQw4w9WgXcQ', $code);
    }

    public function testMakeImageYoutubeBuildsThumbnailUrl(): void
    {
        $url = $this->uri->makeImageYoutube('https://youtu.be/dQw4w9WgXcQ', 0);
        $this->assertSame('https://img.youtube.com/vi/dQw4w9WgXcQ/0.jpg', $url);
    }

    public function testNormalizeURLCollapsesDoubleSlashes(): void
    {
        $this->assertSame('https://example.com/a/b', $this->uri->normalizeURL('https://example.com//a//b'));
    }
}
