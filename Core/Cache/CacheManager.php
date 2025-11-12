<?php

declare(strict_types=1);

namespace Core\Cache;

use Core\Cache\CacheConfig;
use Core\Cache\FileCache;
use Core\Cache\MemoryCache;
use Core\Exception\CacheException;

/**
 * Cache Manager - Factory and manager for different cache implementations
 */
class CacheManager
{
    /**
     * Cache instances
     */
    private static array $instances = [];

    /**
     * Default cache instance
     */
    private static ?BaseCache $default = null;

    /**
     * Create or get a cache instance
     */
    public static function get(string $name = 'default', string $driver = 'file', ?CacheConfig $config = null): BaseCache
    {
        $key = $name . '_' . $driver;

        if (isset(self::$instances[$key])) {
            return self::$instances[$key];
        }

        $config = $config ?? new CacheConfig();
        $config->defaultDriver = $driver;

        $instance = match ($driver) {
            'file' => new FileCache($config),
            'memory' => new MemoryCache($config),
            default => throw CacheException::configurationError("Unsupported cache driver: {$driver}")
        };

        self::$instances[$key] = $instance;

        if ($name === 'default' || self::$default === null) {
            self::$default = $instance;
        }

        return $instance;
    }

    /**
     * Get the default cache instance
     */
    public static function getDefault(): BaseCache
    {
        if (self::$default === null) {
            self::$default = self::get('default', 'file');
        }

        return self::$default;
    }

    /**
     * Set the default cache instance
     */
    public static function setDefault(BaseCache $cache): void
    {
        self::$default = $cache;
    }

    /**
     * Clear all cache instances
     */
    public static function clearInstances(): void
    {
        self::$instances = [];
        self::$default = null;
    }

    /**
     * Get all cache statistics
     */
    public static function getAllStats(): array
    {
        $stats = [];

        foreach (self::$instances as $key => $instance) {
            $stats[$key] = $instance->getStats();
        }

        return $stats;
    }

    /**
     * Create cache configuration with common presets
     */
    public static function createConfig(array $options = []): CacheConfig
    {
        $config = new CacheConfig();

        foreach ($options as $key => $value) {
            if (property_exists($config, $key)) {
                $config->$key = $value;
            }
        }

        return $config;
    }

    /**
     * Create a fast in-memory cache configuration
     */
    public static function createMemoryConfig(array $options = []): CacheConfig
    {
        $defaults = [
            'default_driver' => 'memory',
            'memory_config' => [
                'enabled' => true,
                'max_size' => 1000,
                'max_memory' => '64M'
            ]
        ];

        return self::createConfig(array_merge($defaults, $options));
    }

    /**
     * Create a production-ready file cache configuration
     */
    public static function createFileConfig(array $options = []): CacheConfig
    {
        $defaults = [
            'default_driver' => 'file',
            'use_file_locking' => true,
            'file_permission' => 0755,
            'default_ttl' => 3600
        ];

        return self::createConfig(array_merge($defaults, $options));
    }
}
