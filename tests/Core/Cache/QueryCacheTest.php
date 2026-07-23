<?php

declare(strict_types=1);

namespace Tests\Core\Cache;

use PHPUnit\Framework\TestCase;
use Core\Cache\QueryCache;

final class QueryCacheTest extends TestCase
{
    private QueryCache $cache;
    private string $testDir;

    protected function setUp(): void
    {
        $this->testDir = 'test-query-cache-' . uniqid();
        $this->cache = new QueryCache(['directory' => $this->testDir]);
    }

    protected function tearDown(): void
    {
        $this->cache->clear();
        @rmdir(\Core\Folder\Path::CACHE . $this->testDir);
    }

    public function testGenerateKeyEmbedsEveryTableAJoinTouches(): void
    {
        $key = $this->cache->generateKey(
            'SELECT * FROM canonical_products cp JOIN brands b ON b.id = cp.brand_id WHERE cp.id = :id',
            [':id' => '1']
        );

        $this->assertStringContainsString('brands', $key);
        $this->assertStringContainsString('canonical_products', $key);
    }

    public function testClearTableCacheOnlyRemovesEntriesForThatTable(): void
    {
        $fooKey = $this->cache->generateKey('SELECT * FROM foo', []);
        $barKey = $this->cache->generateKey('SELECT * FROM bar', []);

        $this->cache->storeResult($fooKey, ['from' => 'foo']);
        $this->cache->storeResult($barKey, ['from' => 'bar']);

        $this->assertTrue($this->cache->hasValidCache($fooKey));
        $this->assertTrue($this->cache->hasValidCache($barKey));

        $this->cache->clearTableCache('foo');

        $this->assertFalse($this->cache->hasValidCache($fooKey));
        $this->assertTrue($this->cache->hasValidCache($barKey));
    }
}
