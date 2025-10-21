<?php

declare(strict_types=1);

namespace Core\Cache;

use Core\Folder\Folder as Folder;

/**
 * Main Cache class - provides backward compatibility and unified interface
 */
class Cache implements CacheInterface
{
    /**
     * File cache instance
     */
    private FileCache $fileCache;

    /**
     * Folder utility for directory creation
     */
    private Folder $folder;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->folder = new Folder();
        $this->fileCache = new FileCache();
    }

    /**
     * Set the cache file with the given name, content and expiration time
     * @deprecated Use FileCache::set() for better performance and features
     */
    public function set(string $cacheName, string $cache, int $time = 600): void
    {
        $this->fileCache->set($cacheName, $cache, $time);
    }

    /**
     * Get the cache file content with the given name and expiration time
     * @deprecated Use FileCache::get() for better performance and features
     */
    public function get(string $cacheName, int $time = 600): string
    {
        $result = $this->fileCache->get($cacheName);

        // Maintain backward compatibility - return empty string for null results
        return $result !== null ? (string) $result : '';
    }

    /**
     * Clear all the cache files in the cache directory
     * @deprecated Use FileCache::clear() for better error handling
     */
    public function clear(): void
    {
        $this->fileCache->clear();
    }

    /**
     * Check if cache has a value
     */
    public function has(string $key): bool
    {
        return $this->fileCache->has($key);
    }

    /**
     * Get multiple values from cache
     */
    public function getMultiple(array $keys): array
    {
        return $this->fileCache->getMultiple($keys);
    }

    /**
     * Set multiple values in cache
     */
    public function setMultiple(array $values, ?int $ttl = null): bool
    {
        return $this->fileCache->setMultiple($values, $ttl);
    }

    /**
     * Delete multiple values from cache
     */
    public function deleteMultiple(array $keys): bool
    {
        return $this->fileCache->deleteMultiple($keys);
    }

    /**
     * Delete a specific cache entry
     */
    public function delete(string $key): bool
    {
        return $this->fileCache->delete($key);
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        return $this->fileCache->getStats();
    }

    /**
     * Reset cache statistics
     */
    public function resetStats(): void
    {
        $this->fileCache->resetStats();
    }

    /**
     * Get cache configuration
     */
    public function getConfig(): CacheConfig
    {
        return $this->fileCache->getConfig();
    }

    /**
     * Get the underlying FileCache instance for advanced features
     */
    public function getFileCache(): FileCache
    {
        return $this->fileCache;
    }

    /**
     * Create cache instance with custom configuration
     */
    public static function withConfig(CacheConfig $config): self
    {
        $instance = new self();
        $instance->fileCache = new FileCache($config);
        return $instance;
    }
}