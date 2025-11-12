<?php

/**
 * Database-Specific Features Example
 *
 * This example demonstrates database-specific query builder features
 * that differ between MySQL and PostgreSQL.
 */

require_once __DIR__ . '/../Core/Boot.php';

try {
    // Create a model instance
    $userModel = new Core\Model('users');

    echo "=== Database-Specific Features Example ===\n\n";

    // Example 1: Random ordering
    echo "1. Random Ordering:\n";
    echo "   MySQL: ORDER BY RAND()\n";
    echo "   PostgreSQL: ORDER BY RANDOM()\n";

    // This will use the appropriate syntax based on the database driver
    $query = $userModel->select('id, name')->orderByRandom()->limit(1);
    echo "   Query: " . $query->builder()->compile() . "\n\n";

    // Example 2: Date formatting
    echo "2. Date Formatting:\n";
    echo "   MySQL: DATE_FORMAT(created_at, '%Y-%m-%d')\n";
    echo "   PostgreSQL: TO_CHAR(created_at, 'YYYY-MM-DD')\n";

    $query = $userModel->select('id, name')->dateFormat('created_at');
    echo "   Query: " . $query->builder()->compile() . "\n\n";

    // Example 3: JSON operations
    echo "3. JSON Operations:\n";
    echo "   MySQL: JSON_EXTRACT(data, '$.settings.theme')\n";
    echo "   PostgreSQL: data -> 'settings' -> 'theme'\n";

    $query = $userModel->select('id, name')->jsonExtract('data', 'settings.theme');
    echo "   Query: " . $query->builder()->compile() . "\n\n";

    // Example 4: Full-text search
    echo "4. Full-text Search:\n";
    echo "   MySQL: MATCH(content) AGAINST('search term')\n";
    echo "   PostgreSQL: content @@ plainto_tsquery('english', 'search term')\n";

    $query = $userModel->select('id, title')->fullTextSearch('content', 'database');
    echo "   Query: " . $query->builder()->compile() . "\n\n";

    // Example 5: String aggregation
    echo "5. String Aggregation:\n";
    echo "   MySQL: GROUP_CONCAT(tags SEPARATOR ',')\n";
    echo "   PostgreSQL: STRING_AGG(tags, ',')\n";

    $query = $userModel->select('category')->stringAgg('tags', ';', 'all_tags')->groupBy('category');
    echo "   Query: " . $query->builder()->compile() . "\n\n";

    // Example 6: Null coalescing
    echo "6. Null Coalescing:\n";
    echo "   MySQL: IFNULL(display_name, 'Anonymous')\n";
    echo "   PostgreSQL: COALESCE(display_name, 'Anonymous')\n";

    $query = $userModel->select('id')->coalesce('display_name', 'Anonymous');
    echo "   Query: " . $query->builder()->compile() . "\n\n";

    // Example 7: PostgreSQL-specific features (if using PostgreSQL)
    echo "7. PostgreSQL-Specific Features:\n";
    echo "   - DISTINCT ON: SELECT DISTINCT ON (category) id, name FROM products\n";
    echo "   - ILIKE: WHERE name ILIKE '%john%'\n";
    echo "   - Arrays: WHERE 'admin' = ANY(roles)\n";
    echo "   - RETURNING: INSERT INTO users (name) VALUES ('John') RETURNING id\n\n";

    // Example 8: MySQL-specific features (if using MySQL)
    echo "8. MySQL-Specific Features:\n";
    echo "   - GROUP_CONCAT: GROUP_CONCAT(tags SEPARATOR ';')\n";
    echo "   - SOUNDEX: SOUNDEX(name) for phonetic matching\n";
    echo "   - REGEXP: name REGEXP '^[A-Z]'\n";
    echo "   - MATCH AGAINST: Full-text search with BOOLEAN MODE\n\n";

    echo "=== Database-Specific Features Summary ===\n";
    echo "✅ Automatic database detection\n";
    echo "✅ Database-specific SQL syntax\n";
    echo "✅ Fallback to MySQL syntax for unsupported databases\n";
    echo "✅ Consistent API across different database systems\n";

} catch (Core\Exception\DatabaseException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    echo "This is expected if the database is not properly configured.\n";
    echo "However, the query building features are available.\n";
} catch (Exception $e) {
    echo "General Error: " . $e->getMessage() . "\n";
}

echo "\nDatabase-Specific Methods Available:\n";
echo "- orderByRandom(): Random ordering (RAND() vs RANDOM())\n";
echo "- dateFormat(field, format): Date formatting\n";
echo "- jsonExtract(field, path): JSON field extraction\n";
echo "- jsonContains(field, value, path): JSON containment check\n";
echo "- fullTextSearch(field, term): Full-text search\n";
echo "- stringAgg(field, separator, alias): String aggregation\n";
echo "- coalesce(field, default): Null coalescing\n";
echo "- caseWhen(field, cases, default): CASE statements\n";
echo "- regexp(field, pattern): Regular expression matching\n";
echo "- limitOffset(limit, offset): Combined LIMIT/OFFSET\n";
