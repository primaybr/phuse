<?php

/**
 * Query Caching Example
 *
 * This example demonstrates how to use query caching with the Model class.
 * Query caching can significantly improve performance for frequently executed
 * SELECT queries by storing results in cache files.
 */

require_once __DIR__ . '/../Core/Boot.php';

    // Create a model instance (this will fail if database is not configured)
    $userModel = new Core\Model('users');

try {

    echo "=== Database Query Caching Example ===\n\n";

    // Example 1: Basic caching
    echo "1. Basic Query Caching:\n";

    // First query - will execute and cache the result
    echo "   - Executing first query (will be cached)...\n";
    $startTime = microtime(true);
    $result1 = $userModel->select('id, name')->where('active', 1)->get();
    print_r($result1);
    $firstQueryTime = microtime(true) - $startTime;
    echo "   - First query took: " . number_format($firstQueryTime, 4) . " seconds\n";

    // Check if cache file exists after first query
    $cacheFiles = glob('Cache/database/*.cache');
    echo "   - Cache files after first query: " . count($cacheFiles) . "\n";

    // Second query - should use cached result
    echo "   - Executing same query again (should use cache)...\n";
    $startTime = microtime(true);
    $result2 = $userModel->select('id, name')->where('active', 1)->get();
    print_r($result2);
    $secondQueryTime = microtime(true) - $startTime;
    echo "   - Second query took: " . number_format($secondQueryTime, 4) . " seconds\n";

    // Compare results
    $resultsMatch = $result1 === $result2;
    echo "   - Results match: " . ($resultsMatch ? 'YES' : 'NO') . "\n";
    echo "   - Cache speedup: " . number_format(($firstQueryTime - $secondQueryTime) / $firstQueryTime * 100, 1) . "% faster\n";

    echo "\n";

    // Example 2: Cache management
    echo "2. Cache Management:\n";

    // Clear cache for this table
    $cleared = $userModel->clearTableCache();
    echo "   - Cleared table cache: " . ($cleared ? 'SUCCESS' : 'FAILED') . "\n";

    // Disable caching for this instance
    $userModel->enableCache(false);
    echo "   - Disabled caching for this model instance\n";

    // Re-enable caching
    $userModel->enableCache(true);
    echo "   - Re-enabled caching for this model instance\n";

    echo "\n";

    // Example 3: Cache configuration
    echo "3. Cache Configuration:\n";
    echo "   - Cache lifetime: 1 hour (3600 seconds)\n";
    echo "   - Cache directory: Cache/database/\n";
    echo "   - Cacheable queries: SELECT, SHOW, DESCRIBE, EXPLAIN\n";
    echo "   - Excluded tables: None\n";

    echo "\n";

    // Example 4: Cache invalidation scenarios
    echo "4. Cache Invalidation Scenarios:\n";
    echo "   - Cache is automatically invalidated when:\n";
    echo "     * Cache lifetime expires (1 hour)\n";
    echo "     * Table cache is explicitly cleared\n";
    echo "     * Non-SELECT queries are executed (INSERT, UPDATE, DELETE)\n";
    echo "     * Cache is disabled for the model instance\n";

    echo "\n=== Query Caching Example Complete ===\n";

} catch (Core\Exception\DatabaseException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    echo "This is expected if the database is not properly configured.\n";
    echo "However, the caching system is initialized and ready to use.\n";
} catch (Exception $e) {
    echo "General Error: " . $e->getMessage() . "\n";
}

echo "\nCaching Methods Available:\n";
echo "- enableCache(bool): Enable/disable caching for this model\n";
echo "- clearTableCache(): Clear cache for this table\n";
echo "- clearAllCache(): Clear all cached queries\n";
echo "- Caching is automatic for SELECT queries\n";
