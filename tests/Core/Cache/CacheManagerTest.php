<?php

declare(strict_types=1);

namespace Tests\Core\Cache;

use PHPUnit\Framework\TestCase;
use Core\Cache\CacheManager;

class CacheManagerTest extends TestCase
{
    protected function tearDown(): void
    {
        CacheManager::clearInstances();
    }

    public function testGetReturnsSameInstanceForSameNameAndDriver(): void
    {
        $a = CacheManager::get('test_cache_manager', 'file');
        $b = CacheManager::get('test_cache_manager', 'file');

        $this->assertSame($a, $b);
    }

    public function testGetReturnsMemoryDriver(): void
    {
        $cache = CacheManager::get('test_memory', 'memory');

        $cache->set('key', 'value');
        $this->assertSame('value', $cache->get('key'));
    }

    public function testFileCacheSetGetRoundTrip(): void
    {
        $cache = CacheManager::get('test_file_roundtrip', 'file');
        $key = 'roundtrip_' . uniqid('', true);

        $this->assertNull($cache->get($key));

        $cache->set($key, ['nested' => ['value' => 42]], 60);

        // A set()/get() done immediately after must return the same value -
        // regression test for a bug where isCacheValid() treated the raw
        // serialized string as an already-unserialized array and always
        // reported the entry as expired.
        $value = $cache->get($key);
        $this->assertIsArray($value);
        $this->assertSame(42, $value['nested']['value']);

        $cache->delete($key);
        $this->assertNull($cache->get($key));
    }

    public function testFileCacheHas(): void
    {
        $cache = CacheManager::get('test_file_has', 'file');
        $key = 'has_' . uniqid('', true);

        $this->assertFalse($cache->has($key));
        $cache->set($key, 'value', 60);
        $this->assertTrue($cache->has($key));
    }

    public function testFileCacheExpiredEntryIsTreatedAsMiss(): void
    {
        $cache = CacheManager::get('test_file_expiry', 'file');
        $key = 'expiry_' . uniqid('', true);

        $cache->set($key, 'value', -1); // already expired

        $this->assertNull($cache->get($key));
    }

    public function testClearInstancesResetsRegistry(): void
    {
        $a = CacheManager::get('test_clear', 'memory');
        CacheManager::clearInstances();
        $b = CacheManager::get('test_clear', 'memory');

        $this->assertNotSame($a, $b);
    }

    public function testCreateMemoryConfigDefaults(): void
    {
        $config = CacheManager::createMemoryConfig();

        $this->assertSame('memory', $config->defaultDriver);
    }

    public function testCreateFileConfigDefaults(): void
    {
        $config = CacheManager::createFileConfig();

        $this->assertSame('file', $config->defaultDriver);
        $this->assertSame(3600, $config->defaultTtl);
    }
}
