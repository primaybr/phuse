<?php

declare(strict_types=1);

namespace Core\Cache;

use Core\Exception\Error;
use Core\Folder\Path;
use Core\Log;

/**
 * QueryCache handles the caching of database query results
 */
class QueryCache
{
    /**
     * @var string Cache directory path
     */
    private string $cacheDir;

    /**
     * @var int Cache lifetime in seconds (0 = forever)
     */
    private int $lifetime;

    /**
     * @var Log Log instance
     */
    private Log $log;

    /**
     * @var array Configuration
     */
    private array $config;

    /**
     * Constructor
     * 
     * @param array $config Cache configuration
     * @throws Error If cache directory is not writable
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->lifetime = $config['lifetime'] ?? 3600;
        $this->cacheDir = rtrim(Path::CACHE, DS) . DS . trim($config['directory'] ?? 'database', DS) . DS;
        $this->log = new Log();
        
        if (!is_dir($this->cacheDir) && !@mkdir($this->cacheDir, 0755, true)) {
            throw new Error("Unable to create query cache directory: {$this->cacheDir}");
        }
        
        if (!is_writable($this->cacheDir)) {
            throw new Error("Query cache directory is not writable: {$this->cacheDir}");
        }
    }

    /**
     * Generate a cache key for the query
     * 
     * @param string $query The SQL query
     * @param array $params Query parameters
     * @return string Cache key
     */
    public function generateKey(string $query, array $params = []): string
    {
        $query = trim(preg_replace('/\s+/', ' ', $query));
        $key = md5($query . serialize($params));
        return 'query_' . $key . '.cache';
    }

    /**
     * Get the full path to the cached query
     * 
     * @param string $key Cache key
     * @return string Full path to cached file
     */
    public function getCachePath(string $key): string
    {
        return $this->cacheDir . $key;
    }

    /**
     * Check if a valid cached result exists
     * 
     * @param string $key Cache key
     * @return bool True if valid cache exists, false otherwise
     */
    public function hasValidCache(string $key): bool
    {
        if (!$this->isCacheEnabled()) {
            return false;
        }

        $cacheFile = $this->getCachePath($key);
        
        if (!file_exists($cacheFile)) {
            return false;
        }
        
        // If lifetime is 0, cache never expires
        if ($this->lifetime === 0) {
            return true;
        }
        
        // Check if cache is still valid
        return (time() - filemtime($cacheFile)) < $this->lifetime;
    }

    /**
     * Get the cached query result
     * 
     * @param string $key Cache key
     * @return mixed Cached result or false on failure
     */
    public function getCachedResult(string $key)
    {
        $cacheFile = $this->getCachePath($key);
        
        if (!file_exists($cacheFile)) {
            return false;
        }
        
        $content = @file_get_contents($cacheFile);
        
        if ($content === false) {
            return false;
        }
        
        return unserialize($content);
    }

    /**
     * Store the query result in cache
     * 
     * @param string $key Cache key
     * @param mixed $data Data to cache
     * @return bool True on success, false on failure
     */
    public function storeResult(string $key, $data): bool
    {
        if (!$this->isCacheEnabled()) {
            return false;
        }

        $cacheFile = $this->getCachePath($key);
        $result = @file_put_contents($cacheFile, serialize($data), LOCK_EX);
        
        if ($result === false) {
            $this->log->write("Failed to write query cache: {$cacheFile}", 'error');
            return false;
        }
        
        return true;
    }

    /**
     * Clear all cached queries
     * 
     * @param string $pattern Optional pattern to match specific cache files
     * @return bool True on success, false on failure
     */
    public function clear(string $pattern = 'query_*'): bool
    {
        $files = glob($this->cacheDir . $pattern);
        $success = true;
        
        foreach ($files as $file) {
            if (is_file($file) && !unlink($file)) {
                $this->log->write("Failed to delete cache file: {$file}", 'error');
                $success = false;
            }
        }
        
        return $success;
    }

    /**
     * Clear cached queries for a specific table
     * 
     * @param string $table Table name
     * @return bool True on success, false on failure
     */
    public function clearTableCache(string $table): bool
    {
        $pattern = "*{$table}*";
        return $this->clear($pattern);
    }

    /**
     * Check if caching is enabled
     * 
     * @return bool
     */
    public function isCacheEnabled(): bool
    {
        return $this->config['enabled'] ?? true;
    }

    /**
     * Check if a query should be cached
     * 
     * @param string $query The SQL query
     * @return bool
     */
    public function shouldCacheQuery(string $query): bool
    {
        if (!$this->isCacheEnabled()) {
            return false;
        }

        $query = trim($query);
        
        // Check if this is a cacheable query type
        $queryType = strtoupper(strtok($query, ' '));
        $cacheableQueries = $this->config['cacheable_queries'] ?? ['SELECT', 'SHOW', 'DESCRIBE', 'EXPLAIN'];
        
        if (!in_array($queryType, $cacheableQueries, true)) {
            return false;
        }
        
        // Check for excluded tables
        $excludedTables = $this->config['exclude_tables'] ?? [];
        foreach ($excludedTables as $table) {
            if (stripos($query, $table) !== false) {
                return false;
            }
        }
        
        // Don't cache queries with SQL_CALC_FOUND_ROWS if configured
        if (($this->config['ignore_on_calc_found_rows'] ?? true) && 
            stripos($query, 'SQL_CALC_FOUND_ROWS') !== false) {
            return false;
        }
        
        return true;
    }
}
