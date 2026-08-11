<?php

declare(strict_types=1);

namespace Tests\Core;

use Core\Cache\QueryCache;
use Core\Model;
use PHPUnit\Framework\TestCase;

final class InspectableQueryCacheModel extends Model
{
    public function __construct()
    {
    }

    public function __destruct()
    {
    }

    public function queryCache(): QueryCache
    {
        $this->initializeQueryCache();

        return $this->queryCache ?? throw new \RuntimeException('Query cache was not initialized.');
    }
}

final class ModelQueryCacheConfigurationTest extends TestCase
{
    private bool $hadRequestUri;
    private string $requestUri = '';

    protected function setUp(): void
    {
        $this->hadRequestUri = array_key_exists('REQUEST_URI', $_SERVER);
        $this->requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '');
    }

    protected function tearDown(): void
    {
        if ($this->hadRequestUri) {
            $_SERVER['REQUEST_URI'] = $this->requestUri;
        } else {
            unset($_SERVER['REQUEST_URI']);
        }
    }

    public function testAdminRequestDisablesEveryQuery(): void
    {
        $_SERVER['REQUEST_URI'] = '/admin/blog/ai-articles';

        self::assertFalse(
            (new InspectableQueryCacheModel())->queryCache()->shouldCacheQuery('SELECT * FROM settings')
        );
    }

    public function testAdminRequestBelowBasePathDisablesEveryQuery(): void
    {
        $_SERVER['REQUEST_URI'] = '/app/admin/blog/ai-articles?refresh=1';

        self::assertFalse(
            (new InspectableQueryCacheModel())->queryCache()->shouldCacheQuery('SELECT * FROM settings')
        );
    }

    public function testPublicRequestKeepsConfiguredCachingAndExclusions(): void
    {
        $_SERVER['REQUEST_URI'] = '/search?q=phone';
        $cache = (new InspectableQueryCacheModel())->queryCache();

        self::assertTrue($cache->shouldCacheQuery('SELECT * FROM settings'));
        self::assertFalse($cache->shouldCacheQuery('SELECT * FROM sessions'));
        self::assertFalse($cache->shouldCacheQuery('SELECT * FROM migrations'));
    }
}
