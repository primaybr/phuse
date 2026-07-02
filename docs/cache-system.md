# Enhanced Cache System Documentation

## Overview

The Phuse framework's cache system has been significantly enhanced with a unified architecture, advanced features, and production-ready capabilities while maintaining full backward compatibility.

## Key Improvements

### 1. Unified Architecture

- **BaseCache Abstract Class**: Provides consistent interface for all cache implementations
- **CacheManager**: Factory pattern for easy switching between cache types
- **CacheConfig**: Unified configuration system with validation
- **CacheException**: Proper error handling with specific exception types

### 2. Enhanced File Caching

- **FileCache Class**: Production-ready file cache with:
  - File locking for concurrent access protection
  - Comprehensive error handling and logging
  - Cache statistics and performance monitoring
  - Batch operations support
  - Automatic cleanup of expired entries

### 3. Memory Caching

- **MemoryCache Class**: Ultra-fast in-memory cache option
- TTL support with automatic expiration
- Memory usage monitoring and cleanup

### 4. Advanced Features

- **Cache Statistics**: Hit rates, operation counts, memory usage tracking
- **Batch Operations**: Efficient multiple key operations (getMultiple, setMultiple, deleteMultiple)
- **Flexible Configuration**: Environment-specific cache settings
- **Comprehensive Logging**: Detailed operation tracking

## Usage Examples

### Basic Usage (Backward Compatible)

```php
<?php
$cache = new Core\Cache\Cache();
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
?>
```

### Advanced FileCache Usage

```php
<?php
$fileCache = new Core\Cache\FileCache();

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
?>
```

### Memory Cache for Fast Operations

```php
<?php
$memoryCache = new Core\Cache\MemoryCache();

// Ultra-fast in-memory caching
$memoryCache->set('session_data', ['user_id' => 123], 3600);
$sessionData = $memoryCache->get('session_data');

// Memory-specific features
$memoryUsage = $memoryCache->getMemoryUsage();
$expiredCount = $memoryCache->cleanup(); // Clean expired entries
?>
```

### Cache Manager for Multiple Cache Types

```php
<?php
// Get default file cache
$defaultCache = Core\Cache\CacheManager::getDefault();

// Get specific cache instances
$fileCache = Core\Cache\CacheManager::get('files', 'file');
$memoryCache = Core\Cache\CacheManager::get('session', 'memory');

// Create with custom configuration
// Note: option keys must match CacheConfig's camelCase property names -
// snake_case keys are silently ignored (fixed in v1.2.8; this example
// previously showed the broken snake_case form).
$config = Core\Cache\CacheManager::createFileConfig([
    'defaultTtl' => 7200,
    'keyPrefix' => 'app_v2'
]);

$customCache = Core\Cache\CacheManager::get('custom', 'file', $config);

// Monitor all caches
$allStats = Core\Cache\CacheManager::getAllStats();
?>
```

### Production Configuration

```php
<?php
$config = Core\Cache\CacheManager::createFileConfig([
    'enabled' => true,
    'defaultTtl' => 3600,
    'useFileLocking' => true,
    'filePermission' => 0755,
    'keyPrefix' => 'prod_app',
    'subdirectories' => [
        'default'   => 'default',
        'query'     => 'database',
        'templates' => 'templates'  // Note: key is 'templates', not 'template'
    ]
]);
?>
```

> **Note (v1.2.0):** The subdirectory config key for template caching was corrected from `'template'` to `'templates'`. Each named cache instance now receives its own subdirectory automatically via `CacheManager`, so manual subdirectory wiring is rarely needed.

## Technical Improvements

### Thread Safety
- File locking prevents race conditions in concurrent environments
- Proper synchronization for cache file operations

### Performance Enhancements
- Efficient cache key generation and validation
- Automatic cleanup of expired entries
- Memory usage optimization and monitoring

### Error Handling
- Graceful handling of corrupt cache files
- Comprehensive exception hierarchy
- Detailed logging for debugging

### Configuration Flexibility
- Environment-specific cache settings
- Runtime configuration validation
- Support for multiple cache drivers

## Migration Guide

### Existing Code Compatibility
All existing cache usage continues to work without changes:

```php
<?php
// This still works exactly as before
$cache = new Core\Cache\Cache();
$cache->set('key', 'value', 300);
$value = $cache->get('key');
$cache->clear();
?>
```

### Gradual Migration Path
To take advantage of new features:

```php
<?php
// Step 1: Use new methods for better functionality
$cache = new Core\Cache\Cache();
$cache->setMultiple(['key1' => 'value1', 'key2' => 'value2']);
$values = $cache->getMultiple(['key1', 'key2']);

// Step 2: Access advanced features via getFileCache()
$fileCache = $cache->getFileCache();
$stats = $fileCache->getStats();

// Step 3: Use CacheManager for advanced scenarios
$memoryCache = Core\Cache\CacheManager::get('session', 'memory');
?>
```

## Benefits Achieved

- **🔒 Thread Safety**: File locking prevents data corruption
- **📈 Performance**: Better hit rates and monitoring capabilities
- **🛠️ Maintainability**: Unified architecture and consistent interfaces
- **🔄 Flexibility**: Easy switching between cache implementations
- **📝 Observability**: Comprehensive logging and statistics
- **🔒 Production Ready**: Enterprise-level features and error handling

## File Structure

```
Core/Cache/
├── Cache.php (Enhanced - backward compatible)
├── CacheInterface.php (Extended with new methods)
├── CacheConfig.php (Unified configuration)
├── BaseCache.php (Abstract base class)
├── FileCache.php (Advanced file caching)
├── MemoryCache.php (In-memory caching)
├── CacheManager.php (Cache factory/manager)
├── QueryCache.php (Database query caching)
├── TemplateCache.php (Template compilation caching)
└── Examples.php (PHP usage examples)

Core/Exception/
├── CacheException.php (Proper error handling)
```