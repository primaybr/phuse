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
}