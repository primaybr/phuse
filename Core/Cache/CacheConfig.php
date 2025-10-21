<?php

declare(strict_types=1);

namespace Core\Cache;

/**
 * Unified configuration class for all cache types
 */
class CacheConfig
{
    /**
     * Default time-to-live in seconds
     */
    public int $defaultTtl = 3600;

    /**
     * Cache directory name
     */
    public string $directory = 'cache';

    /**
     * Whether caching is enabled
     */
    public bool $enabled = true;

    /**
     * Available cache drivers
     */
    public array $drivers = ['file', 'memory', 'redis'];

    /**
     * Default cache driver
     */
    public string $defaultDriver = 'file';

    /**
     * File permissions for cache files
     */
    public int $filePermission = 0755;

    /**
     * Whether to use file locking
     */
    public bool $useFileLocking = true;

    /**
     * Cache key prefix
     */
    public string $keyPrefix = '';

    /**
     * Subdirectories for different cache types
     */
    public array $subdirectories = [
        'default' => 'default',
        'query' => 'database',
        'template' => 'templates'
    ];

    /**
     * Query-specific configuration
     */
    public array $queryConfig = [
        'enabled' => true,
        'lifetime' => 3600,
        'cacheable_queries' => ['SELECT', 'SHOW', 'DESCRIBE', 'EXPLAIN'],
        'exclude_tables' => [],
        'ignore_on_calc_found_rows' => true
    ];

    /**
     * Template-specific configuration
     */
    public array $templateConfig = [
        'enabled' => true,
        'lifetime' => 3600,
        'auto_clear_development' => true,
        'cache_dir' => 'templates'
    ];

    /**
     * Memory cache configuration
     */
    public array $memoryConfig = [
        'enabled' => true,
        'max_size' => 1000, // Maximum number of items
        'max_memory' => '64M' // Maximum memory usage
    ];

    /**
     * Create configuration from array
     */
    public static function fromArray(array $config): self
    {
        $instance = new self();

        foreach ($config as $key => $value) {
            if (property_exists($instance, $key)) {
                $instance->$key = $value;
            }
        }

        return $instance;
    }

    /**
     * Get cache directory path for a specific type
     */
    public function getCacheDirectory(string $type = 'default'): string
    {
        $baseDir = rtrim(\Core\Folder\Path::CACHE, DIRECTORY_SEPARATOR);
        $subDir = $this->subdirectories[$type] ?? $this->subdirectories['default'];

        return $baseDir . DIRECTORY_SEPARATOR . $subDir;
    }

    /**
     * Validate configuration
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->defaultTtl < 0) {
            $errors[] = 'Default TTL must be non-negative';
        }

        if (!in_array($this->defaultDriver, $this->drivers)) {
            $errors[] = 'Invalid default cache driver';
        }

        if (empty($this->directory)) {
            $errors[] = 'Cache directory cannot be empty';
        }

        return $errors;
    }
}
