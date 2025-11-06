<?php

declare(strict_types=1);

/**
 * PHUSE FRAMEWORK - TEMPLATE SYSTEM EXAMPLES
 *
 * This file contains comprehensive examples demonstrating the template system
 * capabilities including variable replacement, conditionals, loops, caching,
 * and advanced features.
 *
 * @package Examples
 * @author  Prima Yoga
 */

namespace Examples;

use Core\Template\Parser;
use Core\Cache\TemplateCache;

/**
 * EXAMPLE 1: Basic Template Usage
 *
 * Demonstrates basic template rendering with variable replacement
 */
class BasicTemplateUsage
{
    public function demonstrate(): void
    {
        $template = new Parser();

        // Set template and data
        $template->setTemplate('welcome');
        $template->setData([
            'name' => 'John Doe',
            'title' => 'Software Developer',
            'company' => 'Tech Corp'
        ]);

        // Render and output
        echo "=== Basic Template Example ===\n";
        $output = $template->render('', [], true);
        echo $output . "\n";
        echo "✓ Basic template rendering completed\n\n";
    }
}

/**
 * EXAMPLE 2: Conditional Statements
 *
 * Shows how to use if/else logic in templates
 */
class ConditionalTemplateUsage
{
    public function demonstrate(): void
    {
        $template = new Parser();

        echo "=== Conditional Template Example ===\n";

        // Test with logged in user
        $template->setTemplate('user_dashboard');
        $template->setData([
            'logged_in' => true,
            'username' => 'johndoe',
            'role' => 'admin',
            'notifications' => 5
        ]);

        $output = $template->render('', [], true);
        echo "Logged in user:\n$output\n";

        // Test with guest user
        $template->setData([
            'logged_in' => false,
            'username' => '',
            'role' => 'guest',
            'notifications' => 0
        ]);

        $output = $template->render('', [], true);
        echo "Guest user:\n$output\n";
        echo "✓ Conditional template logic completed\n\n";
    }
}

/**
 * EXAMPLE 3: Foreach Loops
 *
 * Demonstrates iterating over arrays in templates
 */
class ForeachTemplateUsage
{
    public function demonstrate(): void
    {
        $template = new Parser();

        echo "=== Foreach Loop Example ===\n";

        $template->setTemplate('product_list');
        $template->setData([
            'products' => [
                ['name' => 'Laptop', 'price' => 999.99, 'category' => 'Electronics'],
                ['name' => 'Mouse', 'price' => 25.50, 'category' => 'Electronics'],
                ['name' => 'Keyboard', 'price' => 75.00, 'category' => 'Electronics'],
                ['name' => 'Monitor', 'price' => 299.99, 'category' => 'Electronics']
            ],
            'category_filter' => 'Electronics'
        ]);

        $output = $template->render('', [], true);
        echo $output;
        echo "✓ Foreach loop template completed\n\n";
    }
}

/**
 * EXAMPLE 4: Nested Data Access
 *
 * Shows how to access nested array properties
 */
class NestedDataTemplateUsage
{
    public function demonstrate(): void
    {
        $template = new Parser();

        echo "=== Nested Data Example ===\n";

        $template->setTemplate('user_profile');
        $template->setData([
            'users' => [
                [
                    'name' => 'Alice Johnson',
                    'profile' => [
                        'age' => 28,
                        'city' => 'San Francisco',
                        'occupation' => 'Designer'
                    ],
                    'skills' => ['Photoshop', 'Illustrator', 'Figma']
                ],
                [
                    'name' => 'Bob Smith',
                    'profile' => [
                        'age' => 32,
                        'city' => 'New York',
                        'occupation' => 'Developer'
                    ],
                    'skills' => ['PHP', 'JavaScript', 'React']
                ]
            ]
        ]);

        $output = $template->render('', [], true);
        echo $output;
        echo "✓ Nested data template completed\n\n";
    }
}

/**
 * EXAMPLE 5: Template Caching
 *
 * Demonstrates template caching for performance
 */
class TemplateCachingUsage
{
    public function demonstrate(): void
    {
        $template = new Parser();

        echo "=== Template Caching Example ===\n";

        // Enable caching
        $template->enableCache(true);

        // First render (creates cache)
        echo "First render (creates cache):\n";
        $startTime = microtime(true);
        $template->setTemplate('heavy_template');
        $template->setData([
            'title' => 'Heavy Template Test',
            'content' => 'This template has complex logic and should be cached.'
        ]);

        $output1 = $template->render('', [], true);
        $firstRenderTime = microtime(true) - $startTime;
        echo "Render time: " . round($firstRenderTime * 1000, 2) . "ms\n";

        // Second render (uses cache)
        echo "Second render (uses cache):\n";
        $startTime = microtime(true);
        $output2 = $template->render('', [], true);
        $secondRenderTime = microtime(true) - $startTime;
        echo "Render time: " . round($secondRenderTime * 1000, 2) . "ms\n";

        // Verify cache was used
        if ($output1 === $output2) {
            echo "✓ Cache working - outputs are identical\n";
        }

        if ($secondRenderTime < $firstRenderTime) {
            echo "✓ Performance improved with caching\n";
        }

        // Clear cache
        $template->clearCache();
        echo "✓ Cache cleared\n\n";
    }
}

/**
 * EXAMPLE 6: Error Handling
 *
 * Shows error handling capabilities
 */
class ErrorHandlingUsage
{
    public function demonstrate(): void
    {
        $template = new Parser();

        echo "=== Error Handling Example ===\n";

        try {
            // This should work fine
            $template->setTemplate('valid_template');
            $template->setData(['message' => 'Success!']);
            $output = $template->render('', [], true);
            echo "Valid template rendered successfully:\n$output\n";

        } catch (\Exception $e) {
            echo "Unexpected error: " . $e->getMessage() . "\n";
        }

        try {
            // This should throw an exception
            $template->setTemplate('nonexistent_template');
        } catch (\Core\Exception\Error $e) {
            echo "✓ Caught expected error for missing template: " . $e->getMessage() . "\n";
        }

        try {
            // This should throw an exception for invalid data
            $template->setData('invalid_data');
        } catch (\Core\Exception\Error $e) {
            echo "✓ Caught expected error for invalid data: " . $e->getMessage() . "\n";
        }

        echo "✓ Error handling completed\n\n";
    }
}

/**
 * EXAMPLE 7: Method Chaining and Fluent Interface
 *
 * Demonstrates the fluent interface for cleaner code
 */
class FluentInterfaceUsage
{
    public function demonstrate(): void
    {
        $template = new Parser();

        echo "=== Fluent Interface Example ===\n";

        // Using method chaining for cleaner code
        $output = $template
            ->setTemplate('blog_post')
            ->setData([
                'title' => 'My Blog Post',
                'author' => 'Jane Doe',
                'date' => '2023-12-01',
                'content' => 'This is the main content of the blog post.',
                'tags' => ['php', 'web-development', 'templates'],
                'comments' => [
                    ['author' => 'User1', 'text' => 'Great post!'],
                    ['author' => 'User2', 'text' => 'Very helpful, thanks!']
                ]
            ])
            ->enableCache(false) // Disable for this example
            ->render('', [], true);

        echo $output;
        echo "✓ Fluent interface completed\n\n";
    }
}

/**
 * EXAMPLE 8: Advanced Template Features
 *
 * Shows complex template scenarios
 */
class AdvancedTemplateFeatures
{
    public function demonstrate(): void
    {
        $template = new Parser();

        echo "=== Advanced Features Example ===\n";

        // Complex template with multiple features
        $template->setTemplate('dashboard');
        $template->setData([
            'user' => [
                'name' => 'Admin User',
                'role' => 'administrator',
                'last_login' => '2023-12-01 10:30:00'
            ],
            'stats' => [
                'total_users' => 1250,
                'active_sessions' => 89,
                'pending_orders' => 12
            ],
            'recent_activity' => [
                ['action' => 'User registered', 'time' => '2 minutes ago'],
                ['action' => 'Order placed', 'time' => '5 minutes ago'],
                ['action' => 'Payment processed', 'time' => '8 minutes ago']
            ],
            'notifications' => [
                ['type' => 'warning', 'message' => 'Server load is high'],
                ['type' => 'info', 'message' => 'New version available'],
                ['type' => 'error', 'message' => 'Database connection failed']
            ]
        ]);

        $output = $template->render('', [], true);
        echo $output;
        echo "✓ Advanced template features completed\n\n";
    }
}

/**
 * EXAMPLE 9: Performance Optimization
 *
 * Demonstrates performance best practices
 */
class PerformanceOptimizationUsage
{
    public function demonstrate(): void
    {
        $template = new Parser();

        echo "=== Performance Optimization Example ===\n";

        // Show caching benefits with multiple renders
        $template->enableCache(true);
        $template->setTemplate('performance_test');

        $renderTimes = [];
        for ($i = 0; $i < 5; $i++) {
            $startTime = microtime(true);
            $template->setData([
                'iteration' => $i + 1,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            $template->render('', [], true);
            $renderTimes[] = microtime(true) - $startTime;
        }

        $averageTime = array_sum($renderTimes) / count($renderTimes);
        echo "Average render time: " . round($averageTime * 1000, 2) . "ms\n";
        echo "Individual render times: " . implode(', ', array_map(fn($t) => round($t * 1000, 2) . 'ms', $renderTimes)) . "\n";

        // Show cache statistics
        $cache = new TemplateCache();
        echo "✓ Performance optimization completed\n\n";
    }
}

/**
 * EXAMPLE 10: Real-world Application Scenario
 *
 * Demonstrates a complete real-world usage scenario
 */
class RealWorldApplicationUsage
{
    public function demonstrate(): void
    {
        $template = new Parser();

        echo "=== Real-World Application Example ===\n";

        // Simulate an e-commerce product page
        $template->setTemplate('product_page');
        $template->setData([
            'product' => [
                'id' => 12345,
                'name' => 'Wireless Bluetooth Headphones',
                'price' => 89.99,
                'description' => 'High-quality wireless headphones with noise cancellation and 30-hour battery life.',
                'image' => '/images/products/headphones.jpg',
                'in_stock' => true,
                'stock_quantity' => 25,
                'category' => 'Electronics',
                'brand' => 'AudioTech',
                'rating' => 4.5,
                'reviews' => 128
            ],
            'related_products' => [
                ['name' => 'USB-C Cable', 'price' => 12.99],
                ['name' => 'Phone Case', 'price' => 19.99],
                ['name' => 'Screen Protector', 'price' => 9.99]
            ],
            'user_preferences' => [
                'currency' => 'USD',
                'show_prices' => true,
                'compact_view' => false
            ]
        ]);

        $output = $template->render('', [], true);
        echo $output;
        echo "✓ Real-world application example completed\n\n";
    }
}

// Example usage demonstration
if (php_sapi_name() === 'cli') {
    echo "Phuse Template System Examples\n";
    echo "==============================\n\n";

    echo "🚀 Template Examples Available:\n";
    echo "• Basic Template: Visit /examples/basic\n";
    echo "• Conditional Logic: Visit /examples/conditional\n";
    echo "• Foreach Loops: Visit /examples/foreach\n";
    echo "• Nested Data: Visit /examples/nested\n";
    echo "• Blog Post: Visit /examples/blog\n";
    echo "• Dashboard: Visit /examples/dashboard\n";
    echo "• Product Page: Visit /examples/product\n";
    echo "• All Examples Index: Visit /examples\n\n";

    echo "📖 For comprehensive documentation, see: docs/template-system.md\n";
    echo "🎯 Access examples through the web interface at the URLs above\n";
    echo "🔧 All templates are located in: App/Views/examples/\n";
}
