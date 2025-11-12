<?php

declare(strict_types=1);

namespace Core\Cache;

use Core\Cache\BaseCache;
use Core\Cache\CacheConfig;
use Core\Exception\CacheException;

/**
 * In-memory cache implementation
 */
class MemoryCache extends BaseCache
{
    /**
     * Cache storage
     */
    private array $storage = [];

    /**
     * Cache TTL tracking
     */
    private array $ttl = [];

    /**
     * Initialize the memory cache
     */
    protected function initialize(): void
    {
        // Memory cache doesn't need directory setup
    }

    /**
     * Get a value from cache
     */
    public function get(string $key): mixed
    {
        $this->validateKey($key);
        $cacheKey = $this->generateKey($key);

        // Check if key exists and hasn't expired
        if (isset($this->storage[$cacheKey]) &&
            (isset($this->ttl[$cacheKey]) && $this->ttl[$cacheKey] > time())) {

            $this->incrementStat('hits');
            $this->log('Memory cache hit', 'debug', ['key' => $key]);
            return $this->storage[$cacheKey]['value'];
        }

        // Remove expired entry if exists
        if (isset($this->storage[$cacheKey])) {
            unset($this->storage[$cacheKey], $this->ttl[$cacheKey]);
        }

        $this->incrementStat('misses');
        return null;
    }

    /**
     * Set a value in cache
     */
    public function set(string $key, $value, ?int $ttl = null): bool
    {
        $this->validateKey($key);
        $cacheKey = $this->generateKey($key);
        $ttl = $ttl ?? $this->config->defaultTtl;

        try {
            $this->storage[$cacheKey] = [
                'value' => $value,
                'created_at' => time()
            ];

            $this->ttl[$cacheKey] = time() + $ttl;

            $this->incrementStat('sets');
            $this->log('Memory cache set', 'debug', [
                'key' => $key,
                'ttl' => $ttl
            ]);

            return true;
        } catch (\Throwable $e) {
            $this->incrementStat('errors');
            $this->log('Memory cache set error', 'error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Delete a value from cache
     */
    public function delete(string $key): bool
    {
        $this->validateKey($key);
        $cacheKey = $this->generateKey($key);

        if (isset($this->storage[$cacheKey])) {
            unset($this->storage[$cacheKey], $this->ttl[$cacheKey]);
            $this->incrementStat('deletes');
            $this->log('Memory cache deleted', 'debug', ['key' => $key]);
            return true;
        }

        return false;
    }

    /**
     * Clear all cache
     */
    public function clear(): bool
    {
        $count = count($this->storage);
        $this->storage = [];
        $this->ttl = [];
        $this->resetStats();

        $this->log('Memory cache cleared', 'info', [
            'entries_cleared' => $count
        ]);

        return true;
    }

    /**
     * Get memory usage statistics
     */
    public function getMemoryUsage(): array
    {
        return [
            'entries' => count($this->storage),
            'memory_usage' => strlen(serialize($this->storage))
        ];
    }

    /**
     * Clean up expired entries
     */
    public function cleanup(): int
    {
        $now = time();
        $expired = 0;

        foreach ($this->ttl as $key => $expiry) {
            if ($expiry <= $now) {
                unset($this->storage[$key], $this->ttl[$key]);
                $expired++;
            }
        }

        if ($expired > 0) {
            $this->log('Memory cache cleanup', 'info', [
                'expired_entries' => $expired
            ]);
        }

        return $expired;
    }
}
