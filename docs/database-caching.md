# Database Query Caching in Phuse

Phuse includes a powerful query caching system to improve database performance by storing the results of database queries and reusing them for subsequent identical queries.

## Features

- **Automatic Caching**: Query results are automatically cached based on configuration
- **Intelligent Cache Invalidation**: Cache is automatically invalidated when data changes
- **Selective Caching**: Control which queries and tables are cached
- **Flexible Configuration**: Customize cache lifetime and behavior
- **Development Mode**: Automatic cache clearing in development environment

## Configuration

Database query caching can be configured in `Config/Database.php`:

```php
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
```

## Usage

### Basic Usage

No code changes are needed to enable basic query caching. It works automatically for all supported query types.

```php
// These results will be cached automatically
$users = $db->query("SELECT * FROM users WHERE active = 1")->result();
```

### Manual Cache Control

#### Clearing the Cache

```php
// Clear all query cache
$db->clearQueryCache();

// Clear cache for a specific table
$db->clearQueryCache('users');
```

#### Disabling Cache for Specific Queries

```php
// Disable cache for this query only
$db->query("SELECT * FROM users WHERE active = 1")->result();
$db->clearQueryCache('users');

// Or use SQL hints
$db->query("SELECT SQL_NO_CACHE * FROM users")->result();
```

### Cache Tags (Advanced)

For more granular cache control, you can implement cache tags:

```php
// When saving data that affects multiple queries
$db->clearQueryCache('users');
$db->clearQueryCache('user_profiles');
```

## Best Practices

1. **Cache Lifetime**:
   - Set an appropriate cache lifetime based on how often your data changes
   - Use `0` for data that rarely changes (e.g., configuration, lookup tables)
   - Use shorter lifetimes for frequently changing data

2. **Exclude Volatile Tables**:
   - Add frequently updated tables to the `exclude_tables` array
   - Consider excluding tables with real-time data requirements

3. **Development vs Production**:
   - Keep `auto_clear_in_development` enabled during development
   - Disable in production for better performance

4. **Monitor Cache Hit/Miss**:
   - Monitor your cache hit ratio
   - Adjust cache lifetime and exclusions based on usage patterns

## Performance Considerations

- **Memory Usage**: Large result sets will consume more memory
- **File System**: Using a fast storage system (SSD) improves cache performance
- **Cache Invalidation**: Be mindful of how often data changes and adjust cache lifetime accordingly

## Advanced: Custom Cache Backend

To use a different caching mechanism (e.g., Redis, Memcached), extend the `QueryCache` class and update the `Connection` class to use your implementation.
