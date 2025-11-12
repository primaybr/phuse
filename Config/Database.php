<?php

declare(strict_types=1);

namespace Config;

/**
 * Database Configuration
 * 
 * This file contains configuration options for the database connection
 * and query caching.
 */
class Database
{
    /**
     * Database connection settings
     */
    public array $connections = [
        'default' => [
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'port'      => '3306',
            'database'  => 'testdb',
            'username'  => 'root',
            'password'  => '',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
        ]
    ];

    /**
     * Query Caching Configuration
     */
    public array $cache = [
        // Enable query caching
        'enabled' => true,
        
        // Default cache lifetime in seconds (0 = forever)
        'lifetime' => 3600, // 1 hour
        
        // Cache directory (relative to the main cache directory)
        'directory' => 'database',
        
        // Whether to ignore cached results when SELECT SQL_CALC_FOUND_ROWS is used
        'ignore_on_calc_found_rows' => true,
        
        // List of tables to exclude from caching
        'exclude_tables' => [
            'sessions',
            'cache',
            'migrations'
        ],
        
        // List of query types to cache (SELECT, SHOW, etc.)
        'cacheable_queries' => ['SELECT', 'SHOW', 'DESCRIBE', 'EXPLAIN'],
    ];

    /**
     * Get the database configuration
     * 
     * @param string $connection The connection name
     * @return array
     */
    public function getConnectionConfig(string $connection = 'default'): array
    {
        return $this->connections[$connection] ?? $this->connections['default'];
    }

    /**
     * Get the cache configuration
     * 
     * @return array
     */
    public function getCacheConfig(): array
    {
        return $this->cache;
    }
}
