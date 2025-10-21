<?php

declare(strict_types=1);

/**
 * PHUSE FRAMEWORK - ENHANCED CACHE SYSTEM EXAMPLES
 *
 * This file contains practical examples demonstrating the improvements
 * made to the cache system and how to use the new features.
 */

namespace Core\Cache\Examples;

use Core\Cache\Cache;
use Core\Cache\FileCache;
use Core\Cache\MemoryCache;
use Core\Cache\CacheManager;
use Core\Cache\CacheConfig;

/**
 * EXAMPLE 1: Using the improved main Cache class (backward compatible)
 */
class BasicCacheUsage
{
    public function demonstrate(): void
    {
        // Old way (still works)
        $cache = new Cache();
        $cache->set('my_key', 'Hello World', 300);
        $value = $cache->get('my_key'); // Returns string or ''

        // New features available
        $stats = $cache->getStats(); // Get cache statistics
        $hasKey = $cache->has('my_key'); // Check if key exists
        $cache->delete('my_key'); // Delete specific key

        // Batch operations
        $cache->setMultiple([
            'key1' => 'value1',
            'key2' => 'value2'
        ], 600);

        $values = $cache->getMultiple(['key1', 'key2']);
    }
}

/**
 * EXAMPLE 2: Using advanced FileCache directly
 */
class AdvancedFileCacheUsage
{
    public function demonstrate(): void
    {
        $fileCache = new FileCache();

        // Set with custom TTL
        $fileCache->set('user_data', ['name' => 'John', 'age' => 30], 1800);

        // Get with type safety
        $userData = $fileCache->get('user_data'); // Returns array or null

        // Better error handling
        if ($fileCache->has('user_data')) {
            $data = $fileCache->get('user_data');
            // Process data safely
        }

        // Monitor performance
        $stats = $fileCache->getStats();
        echo "Cache hit rate: {$stats['hit_rate_percent']}%";
    }
}

/**
 * EXAMPLE 3: Using Memory Cache for fast operations
 */
class MemoryCacheUsage
{
    public function demonstrate(): void
    {
        $memoryCache = new MemoryCache();

        // Ultra-fast in-memory caching
        $memoryCache->set('session_data', ['user_id' => 123], 3600);
        $sessionData = $memoryCache->get('session_data');

        // Memory-specific features
        $memoryUsage = $memoryCache->getMemoryUsage();
        $expiredCount = $memoryCache->cleanup(); // Clean expired entries
    }
}

/**
 * EXAMPLE 4: Using CacheManager for multiple cache types
 */
class CacheManagerUsage
{
    public function demonstrate(): void
    {
        // Get default file cache
        $defaultCache = CacheManager::getDefault();

        // Get specific cache instances
        $fileCache = CacheManager::get('files', 'file');
        $memoryCache = CacheManager::get('session', 'memory');

        // Create with custom configuration
        $config = CacheManager::createFileConfig([
            'default_ttl' => 7200,
            'key_prefix' => 'app_v2'
        ]);

        $customCache = CacheManager::get('custom', 'file', $config);

        // Monitor all caches
        $allStats = CacheManager::getAllStats();
    }
}

/**
 * EXAMPLE 5: Custom configuration for production
 */
class ProductionConfiguration
{
    public function configureProductionCache(): CacheConfig
    {
        return CacheManager::createFileConfig([
            'enabled' => true,
            'default_ttl' => 3600,
            'use_file_locking' => true,
            'file_permission' => 0755,
            'key_prefix' => 'prod_app',
            'subdirectories' => [
                'default' => 'cache',
                'query' => 'database',
                'template' => 'templates'
            ]
        ]);
    }
}

// Example usage demonstration
if (php_sapi_name() === 'cli') {
    echo "Cache System Examples\n";
    echo "===================\n\n";

    $basicExample = new BasicCacheUsage();
    $basicExample->demonstrate();
    echo "✓ Basic cache usage example completed\n";

    $advancedExample = new AdvancedFileCacheUsage();
    $advancedExample->demonstrate();
    echo "✓ Advanced FileCache usage example completed\n";

    $memoryExample = new MemoryCacheUsage();
    $memoryExample->demonstrate();
    echo "✓ Memory cache usage example completed\n";

    echo "\nAll examples completed successfully!\n";
}
