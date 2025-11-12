<?php

declare(strict_types=1);

namespace Core\Cache;

use Core\Log;
use Core\Cache\CacheConfig;
use Core\Exception\CacheException;

/**
 * Abstract base class for all cache implementations
 */
abstract class BaseCache
{
    /**
     * Cache configuration
     */
    protected CacheConfig $config;

    /**
     * Logger instance
     */
    protected Log $logger;

    /**
     * Cache statistics
     */
    protected array $stats = [
        'hits' => 0,
        'misses' => 0,
        'sets' => 0,
        'deletes' => 0,
        'errors' => 0
    ];

    /**
     * Constructor
     */
    public function __construct(?CacheConfig $config = null)
    {
        $this->config = $config ?? new CacheConfig();
        $this->logger = new Log();

        // Validate configuration
        $errors = $this->config->validate();
        if (!empty($errors)) {
            throw CacheException::configurationError(
                'Invalid cache configuration: ' . implode(', ', $errors)
            );
        }

        $this->initialize();
    }

    /**
     * Initialize the cache implementation
     */
    abstract protected function initialize(): void;

    /**
     * Get a value from cache
     */
    abstract public function get(string $key): mixed;

    /**
     * Set a value in cache
     */
    abstract public function set(string $key, $value, ?int $ttl = null): bool;

    /**
     * Delete a value from cache
     */
    abstract public function delete(string $key): bool;

    /**
     * Clear all cache
     */
    abstract public function clear(): bool;

    /**
     * Check if cache has a value
     */
    public function has(string $key): bool
    {
        try {
            return $this->get($key) !== null;
        } catch (CacheException $e) {
            return false;
        }
    }

    /**
     * Get multiple values from cache
     */
    public function getMultiple(array $keys): array
    {
        $results = [];
        foreach ($keys as $key) {
            try {
                $results[$key] = $this->get($key);
            } catch (CacheException $e) {
                $results[$key] = null;
                $this->incrementStat('errors');
            }
        }
        return $results;
    }

    /**
     * Set multiple values in cache
     */
    public function setMultiple(array $values, ?int $ttl = null): bool
    {
        $success = true;
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * Delete multiple values from cache
     */
    public function deleteMultiple(array $keys): bool
    {
        $success = true;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * Generate a cache key with prefix
     */
    protected function generateKey(string $key): string
    {
        if (empty($this->config->keyPrefix)) {
            return $key;
        }
        return $this->config->keyPrefix . '_' . $key;
    }

    /**
     * Validate cache key
     */
    protected function validateKey(string $key): void
    {
        if (empty($key)) {
            throw CacheException::invalidKey($key, ['reason' => 'empty_key']);
        }

        if (strlen($key) > 255) {
            throw CacheException::invalidKey($key, ['reason' => 'key_too_long']);
        }
    }

    /**
     * Ensure cache directory exists
     */
    protected function ensureCacheDirectory(string $type = 'default'): string
    {
        $dir = $this->config->getCacheDirectory($type);

        if (!is_dir($dir) && !mkdir($dir, $this->config->filePermission, true)) {
            throw CacheException::permissionDenied($dir, [
                'action' => 'create_directory',
                'type' => $type
            ]);
        }

        if (!is_writable($dir)) {
            throw CacheException::permissionDenied($dir, [
                'action' => 'write_directory',
                'type' => $type
            ]);
        }

        return $dir;
    }

    /**
     * Increment a statistic counter
     */
    protected function incrementStat(string $stat): void
    {
        if (isset($this->stats[$stat])) {
            $this->stats[$stat]++;
        }
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        $total = $this->stats['hits'] + $this->stats['misses'];
        $hitRate = $total > 0 ? ($this->stats['hits'] / $total) * 100 : 0;

        return [
            'operations' => $this->stats,
            'hit_rate_percent' => round($hitRate, 2),
            'total_operations' => $total
        ];
    }

    /**
     * Reset statistics
     */
    public function resetStats(): void
    {
        $this->stats = [
            'hits' => 0,
            'misses' => 0,
            'sets' => 0,
            'deletes' => 0,
            'errors' => 0
        ];
    }

    /**
     * Get configuration
     */
    public function getConfig(): CacheConfig
    {
        return $this->config;
    }

    /**
     * Log cache operation
     */
    protected function log(string $message, string $level = 'info', array $context = []): void
    {
        $context['cache_type'] = static::class;
        $this->logger->write($message, $level, $context);
    }
}
