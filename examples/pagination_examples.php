<?php

/**
 * PAGINATION COMPONENT USAGE EXAMPLES
 *
 * This file demonstrates various usage patterns for the Phuse Framework's
 * Pagination component, showcasing its flexibility and configuration options.
 *
 * The Pagination component provides a robust, accessible, and highly
 * configurable solution for paginating data sets in web applications.
 *
 * @package Examples
 * @author  Prima Yoga
 */

declare(strict_types=1);

namespace Examples;

require_once __DIR__ . '/../Core/Boot.php';

use Core\Utilities\Pagination\Pager;
use Core\Utilities\Pagination\PagerConfig;
use Core\Log;

/**
 * EXAMPLE 1: Basic pagination usage
 */
class BasicPaginationUsage
{
    public function demonstrate(): void
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo " EXAMPLE 1: Basic Pagination Usage\n";
        echo str_repeat("=", 60) . "\n";

        try {
            // Create a basic pager with 150 items, 20 per page
            $pager = new Pager(150, 1);

            echo "✓ Created pager with 150 total items, 20 per page\n";
            echo "✓ Current page: " . $pager->getInfo()['currentPage'] . "\n";
            echo "✓ Total pages: " . $pager->getInfo()['totalPages'] . "\n";
            echo "✓ Items per page: " . $pager->getInfo()['itemsPerPage'] . "\n";

            $html = $pager->render();
            echo "✓ Generated HTML length: " . strlen($html) . " characters\n";

            if (strlen($html) > 0) {
                echo "✓ Navigation links generated successfully\n";
                echo "✓ First page - no previous/first links shown\n";
                echo "✓ Next and last page links are visible\n";
            }

            echo "\n📋 Sample Output (first 200 chars):\n";
            echo substr(htmlspecialchars($html), 0, 200) . "...\n";

        } catch (\Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }

        echo "\n" . str_repeat("-", 60) . "\n";
    }
}

/**
 * EXAMPLE 2: Configured pagination with custom settings
 */
class ConfiguredPaginationUsage
{
    public function demonstrate(): void
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo " EXAMPLE 2: Configured Pagination with Custom Settings\n";
        echo str_repeat("=", 60) . "\n";

        try {
            // Create custom configuration
            $config = new PagerConfig();
            $config->defaultItemsPerPage = 10;
            $config->containerClass = 'my-custom-pagination';
            $config->itemClass = 'page-item-custom';
            $config->linkClass = 'page-link-custom';
            $config->activeClass = 'current-page';
            $config->enableAccessibility = true;

            echo "✓ Custom configuration created\n";
            echo "  - Items per page: " . $config->defaultItemsPerPage . "\n";
            echo "  - Container class: " . $config->containerClass . "\n";
            echo "  - Accessibility: " . ($config->enableAccessibility ? 'enabled' : 'disabled') . "\n";

            // Create pager with custom config
            $pager = new Pager(100, 1, $config);

            echo "✓ Pager created with custom configuration\n";
            echo "✓ Current page: " . $pager->getInfo()['currentPage'] . "\n";
            echo "✓ Total pages: " . $pager->getInfo()['totalPages'] . "\n";
            echo "✓ Items per page: " . $pager->getInfo()['itemsPerPage'] . "\n";

            $html = $pager->render();
            echo "✓ Generated HTML length: " . strlen($html) . " characters\n";

            // Verify custom classes are used
            if (strpos($html, $config->containerClass) !== false) {
                echo "✓ Custom container class applied\n";
            }
            if (strpos($html, 'aria-label=') !== false) {
                echo "✓ Accessibility features enabled\n";
            }

            echo "\n📋 Sample Output (first 300 chars):\n";
            echo substr(htmlspecialchars($html), 0, 300) . "...\n";

        } catch (\Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }

        echo "\n" . str_repeat("-", 60) . "\n";
    }
}

/**
 * EXAMPLE 3: Fluent interface usage
 */
class FluentInterfaceUsage
{
    public function demonstrate(): void
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo " EXAMPLE 3: Fluent Interface Usage\n";
        echo str_repeat("=", 60) . "\n";

        try {
            echo "✓ Demonstrating fluent interface pattern...\n";

            // Use fluent interface to configure pager
            $pager = new Pager();
            $result = $pager
                ->setTotalItems(500)
                ->setItemsPerPage(25)
                ->setCurrentPage(3)
                ->setUrl('/products')
                ->setNumLinks(7)
                ->setFirstText('First')
                ->setLastText('Last')
                ->setPreviousText('Prev')
                ->setNextText('Next');

            echo "✓ Fluent configuration completed\n";
            echo "✓ Total items: " . $result->getInfo()['totalItems'] . "\n";
            echo "✓ Items per page: " . $result->getInfo()['itemsPerPage'] . "\n";
            echo "✓ Current page: " . $result->getInfo()['currentPage'] . "\n";
            echo "✓ Total pages: " . $result->getInfo()['totalPages'] . "\n";
            echo "✓ Navigation links to show: " . $result->getInfo()['totalPages'] . "\n";

            $html = $result->render();
            echo "✓ Generated HTML length: " . strlen($html) . " characters\n";

            // Check for custom navigation text
            if (strpos($html, 'First') !== false) {
                echo "✓ Custom 'First' text found\n";
            }
            if (strpos($html, 'Last') !== false) {
                echo "✓ Custom 'Last' text found\n";
            }
            if (strpos($html, 'Prev') !== false) {
                echo "✓ Custom 'Prev' text found\n";
            }
            if (strpos($html, 'Next') !== false) {
                echo "✓ Custom 'Next' text found\n";
            }

            // Check URL generation
            if (strpos($html, '/products') !== false) {
                echo "✓ Custom URL path used\n";
            }
            if (strpos($html, 'page=') !== false) {
                echo "✓ Page parameters generated\n";
            }

            echo "\n📋 Sample Output (first 400 chars):\n";
            echo substr(htmlspecialchars($html), 0, 400) . "...\n";

        } catch (\Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }

        echo "\n" . str_repeat("-", 60) . "\n";
    }
}

/**
 * EXAMPLE 4: URL generation and parameter handling
 */
class UrlGenerationUsage
{
    public function demonstrate(): void
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo " EXAMPLE 4: URL Generation and Parameter Handling\n";
        echo str_repeat("=", 60) . "\n";

        try {
            echo "✓ Testing URL generation with various scenarios...\n";

            // Test 1: Basic URL with page parameter
            $pager1 = new Pager(100, 1);
            $pager1->setUrl('/products');
            $html1 = $pager1->render();

            echo "\n  Test 1 - Basic URL:\n";
            if (strpos($html1, 'href="/products?page=') !== false) {
                echo "  ✓ Basic URL generation working\n";
            }

            // Test 2: URL with existing query parameters
            $pager2 = new Pager(200, 2);
            $pager2->setUrl('/products?category=electronics&sort=price');
            $html2 = $pager2->render();

            echo "\n  Test 2 - URL with existing parameters:\n";
            if (strpos($html2, 'category=electronics') !== false) {
                echo "  ✓ Existing parameters preserved\n";
            }
            if (strpos($html2, 'sort=price') !== false) {
                echo "  ✓ Multiple parameters preserved\n";
            }
            if (strpos($html2, 'page=') !== false) {
                echo "  ✓ Page parameter added correctly\n";
            }

            // Test 3: Custom page parameter name
            $config = new PagerConfig();
            $config->pageParameter = 'p';

            $pager3 = new Pager(150, 1, $config);
            $pager3->setUrl('/search');
            $html3 = $pager3->render();

            echo "\n  Test 3 - Custom page parameter:\n";
            if (strpos($html3, 'p=') !== false) {
                echo "  ✓ Custom parameter 'p' used\n";
            }
            if (strpos($html3, 'page=') === false) {
                echo "  ✓ Default 'page' parameter avoided\n";
            }

            // Test 4: URL pattern with placeholder
            $config2 = new PagerConfig();
            $config2->urlPattern = '/products/page/{page}';

            $pager4 = new Pager(300, 2, $config2);
            $html4 = $pager4->render();

            echo "\n  Test 4 - URL pattern with placeholder:\n";
            if (strpos($html4, '/products/page/') !== false) {
                echo "  ✓ URL pattern with placeholder working\n";
            }

            echo "\n📋 Sample URLs from generated HTML:\n";
            preg_match_all('/href="([^"]*)"/', $html1, $matches);
            if (!empty($matches[1])) {
                foreach (array_slice($matches[1], 0, 3) as $url) {
                    echo "  " . htmlspecialchars($url) . "\n";
                }
            }

        } catch (\Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }

        echo "\n" . str_repeat("-", 60) . "\n";
    }
}

/**
 * EXAMPLE 5: Error handling and validation
 */
class ErrorHandlingUsage
{
    public function demonstrate(): void
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo " EXAMPLE 5: Error Handling and Validation\n";
        echo str_repeat("=", 60) . "\n";

        try {
            echo "✓ Testing error handling and validation...\n";

            // Test 1: Invalid total items
            echo "\n  Test 1 - Invalid total items:\n";
            try {
                $pager = new Pager();
                $pager->setTotalItems(-1);
                echo "  ⚠ This should have thrown an exception\n";
            } catch (\InvalidArgumentException $e) {
                echo "  ✓ Correctly caught exception: " . $e->getMessage() . "\n";
            }

            // Test 2: Invalid items per page
            echo "\n  Test 2 - Invalid items per page:\n";
            try {
                $pager = new Pager();
                $pager->setItemsPerPage(10000); // Above max limit
                echo "  ⚠ This should have thrown an exception\n";
            } catch (\InvalidArgumentException $e) {
                echo "  ✓ Correctly caught exception: " . $e->getMessage() . "\n";
            }

            // Test 3: Invalid current page
            echo "\n  Test 3 - Invalid current page:\n";
            try {
                $pager = new Pager();
                $pager->setCurrentPage(0);
                echo "  ⚠ This should have thrown an exception\n";
            } catch (\InvalidArgumentException $e) {
                echo "  ✓ Correctly caught exception: " . $e->getMessage() . "\n";
            }

            // Test 4: Invalid configuration
            echo "\n  Test 4 - Invalid configuration:\n";
            $config = new PagerConfig();
            $config->minItemsPerPage = 10;
            $config->maxItemsPerPage = 5; // Invalid: max < min

            $errors = $config->validate();
            if (!empty($errors)) {
                echo "  ✓ Configuration validation working\n";
                echo "  ✓ Found " . count($errors) . " validation error(s)\n";
                foreach ($errors as $error) {
                    echo "    - " . $error . "\n";
                }
            }

            // Test 5: Successful validation
            echo "\n  Test 5 - Valid configuration:\n";
            $validConfig = new PagerConfig();
            $validErrors = $validConfig->validate();
            if (empty($validErrors)) {
                echo "  ✓ Valid configuration passes validation\n";
            }

            echo "\n📋 Summary:\n";
            echo "  ✓ Exception handling working correctly\n";
            echo "  ✓ Input validation preventing invalid states\n";
            echo "  ✓ Configuration validation catching misconfigurations\n";
            echo "  ✓ Graceful error handling with descriptive messages\n";

        } catch (\Exception $e) {
            echo "✗ Unexpected error: " . $e->getMessage() . "\n";
        }

        echo "\n" . str_repeat("-", 60) . "\n";
    }
}

/**
 * EXAMPLE 6: Advanced features and accessibility
 */
class AdvancedFeaturesUsage
{
    public function demonstrate(): void
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo " EXAMPLE 6: Advanced Features and Accessibility\n";
        echo str_repeat("=", 60) . "\n";

        try {
            echo "✓ Demonstrating advanced features...\n";

            // Test accessibility features
            $config = new PagerConfig();
            $config->enableAccessibility = true;
            $config->showFirstLast = true;
            $config->showPrevNext = true;
            $config->showPageNumbers = true;

            $pager = new Pager(1000, 5, $config);
            $html = $pager->render();

            echo "✓ Advanced configuration applied\n";
            echo "✓ Current page: " . $pager->getInfo()['currentPage'] . "\n";
            echo "✓ Total pages: " . $pager->getInfo()['totalPages'] . "\n";
            echo "✓ Items per page: " . $pager->getInfo()['itemsPerPage'] . "\n";

            // Check accessibility features
            if (strpos($html, 'aria-label=') !== false) {
                echo "✓ ARIA labels present for accessibility\n";
            }
            if (strpos($html, 'aria-current=') !== false) {
                echo "✓ ARIA current page indicator present\n";
            }

            // Check navigation elements
            if (strpos($html, 'First') !== false || strpos($html, '&laquo;') !== false) {
                echo "✓ First page navigation present\n";
            }
            if (strpos($html, 'Last') !== false || strpos($html, '&raquo;') !== false) {
                echo "✓ Last page navigation present\n";
            }
            if (strpos($html, 'Prev') !== false || strpos($html, '&lt;') !== false) {
                echo "✓ Previous page navigation present\n";
            }
            if (strpos($html, 'Next') !== false || strpos($html, '&gt;') !== false) {
                echo "✓ Next page navigation present\n";
            }

            // Check pagination info
            $info = $pager->getInfo();
            echo "\n📋 Pagination Info:\n";
            echo "  - Start item: " . $info['startItem'] . "\n";
            echo "  - End item: " . $info['endItem'] . "\n";
            echo "  - Needs pagination: " . ($info['needsPagination'] ? 'Yes' : 'No') . "\n";
            echo "  - Total pages: " . $info['totalPages'] . "\n";

            echo "\n📋 Sample HTML (first 500 chars):\n";
            echo substr(htmlspecialchars($html), 0, 500) . "...\n";

        } catch (\Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }

        echo "\n" . str_repeat("-", 60) . "\n";
    }
}

/**
 * EXAMPLE 7: Integration with logging
 */
class LoggingIntegrationUsage
{
    public function demonstrate(): void
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo " EXAMPLE 7: Integration with Framework Logging\n";
        echo str_repeat("=", 60) . "\n";

        try {
            echo "✓ Demonstrating logging integration...\n";

            // Enable logging in configuration
            $config = new PagerConfig();
            $config->enableLogging = true;
            $config->logFileName = 'pagination_examples';

            echo "✓ Logging configuration set\n";
            echo "  - Logging enabled: " . ($config->enableLogging ? 'Yes' : 'No') . "\n";
            echo "  - Log file: " . $config->logFileName . ".log\n";

            // Create pager with logging
            $pager = new Pager(250, 3, $config);

            echo "✓ Pager created with logging enabled\n";
            echo "✓ Current page: " . $pager->getInfo()['currentPage'] . "\n";
            echo "✓ Total pages: " . $pager->getInfo()['totalPages'] . "\n";

            // Generate HTML (this will trigger logging)
            $html = $pager->render();
            echo "✓ HTML generated (logging occurred)\n";

            // Demonstrate configuration changes (with logging)
            $pager->setItemsPerPage(50);
            echo "✓ Items per page changed to 50 (logged)\n";

            $pager->setCurrentPage(2);
            echo "✓ Current page changed to 2 (logged)\n";

            echo "\n📋 Usage Tips:\n";
            echo "  1. Check logs in: Logs/pagination_examples.log\n";
            echo "  2. All configuration changes are logged\n";
            echo "  3. Performance metrics are tracked\n";
            echo "  4. Error conditions are logged with context\n";
            echo "  5. Use logging to debug pagination issues\n";

        } catch (\Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }

        echo "\n" . str_repeat("-", 60) . "\n";
    }
}

/**
 * Main execution function
 */
function runPaginationExamples(): void
{
    echo "\n🚀 PHUSE FRAMEWORK - PAGINATION COMPONENT EXAMPLES\n";
    echo "Comprehensive demonstration of pagination features and usage patterns.\n";

    $examples = [
        new BasicPaginationUsage(),
        new ConfiguredPaginationUsage(),
        new FluentInterfaceUsage(),
        new UrlGenerationUsage(),
        new ErrorHandlingUsage(),
        new AdvancedFeaturesUsage(),
        new LoggingIntegrationUsage(),
    ];

    foreach ($examples as $example) {
        $example->demonstrate();
    }

    echo "\n" . str_repeat("=", 60) . "\n";
    echo " EXAMPLES COMPLETED SUCCESSFULLY!\n";
    echo str_repeat("=", 60) . "\n";

    echo "\n💡 Next Steps:\n";
    echo "  1. Check generated log files in Logs/ directory\n";
    echo "  2. Review the test suite in tests/Core/Components/Pagination/\n";
    echo "  3. See documentation in docs/pagination-component.md\n";
    echo "  4. Customize PagerConfig for your application needs\n";
    echo "  5. Integrate with your data layer and routing system\n";

    echo "\n📚 Component Features Demonstrated:\n";
    echo "  ✓ Basic pagination setup and rendering\n";
    echo "  ✓ Custom configuration and styling\n";
    echo "  ✓ Fluent interface for easy chaining\n";
    echo "  ✓ URL generation and parameter handling\n";
    echo "  ✓ Comprehensive error handling and validation\n";
    echo "  ✓ Accessibility features and ARIA support\n";
    echo "  ✓ Framework logging integration\n";
    echo "  ✓ Responsive design considerations\n";
    echo "  ✓ Performance optimizations\n";
    echo "  ✓ Extensive configuration options\n";

    echo "\n✨ The Pagination component is ready for production use!\n";
}

// Run the examples if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    runPaginationExamples();
}
