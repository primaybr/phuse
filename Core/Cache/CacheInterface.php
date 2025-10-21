<?php

declare(strict_types=1);

namespace Core\Cache;

interface CacheInterface
{
    // Set the cache file with the given name, content and expiration time
    public function set(string $cacheName, string $cache, int $time = 600): void;

    // Get the cache file content with the given name and expiration time
    public function get(string $cacheName, int $time = 600): string;

    // Clear all the cache files in the cache directory
    public function clear(): void;

    // Check if cache has a value
    public function has(string $key): bool;

    // Get multiple values from cache
    public function getMultiple(array $keys): array;

    // Set multiple values in cache
    public function setMultiple(array $values, ?int $ttl = null): bool;

    // Delete multiple values from cache
    public function deleteMultiple(array $keys): bool;

    // Delete a specific cache entry
    public function delete(string $key): bool;

    // Get cache statistics
    public function getStats(): array;

    // Reset cache statistics
    public function resetStats(): void;

    // Get cache configuration
    public function getConfig(): CacheConfig;
}