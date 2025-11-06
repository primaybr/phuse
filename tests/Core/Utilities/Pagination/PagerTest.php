<?php

declare(strict_types=1);

namespace Tests\Core\Utilities\Pagination;

use Core\Utilities\Pagination\Pager;
use Core\Utilities\Pagination\PagerConfig;
use Core\Utilities\Pagination\PagerInterface;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

/**
 * Test cases for Pagination component
 */
class PagerTest extends TestCase
{
    private PagerConfig $defaultConfig;

    protected function setUp(): void
    {
        $this->defaultConfig = new PagerConfig();
    }

    /**
     * Test basic pagination creation
     */
    public function testBasicPaginationCreation(): void
    {
        $pager = new Pager(100, 1);
        $this->assertInstanceOf(Pager::class, $pager);
        $this->assertInstanceOf(PagerInterface::class, $pager);
    }

    /**
     * Test pagination with custom configuration
     */
    public function testPaginationWithCustomConfig(): void
    {
        $config = new PagerConfig();
        $config->defaultItemsPerPage = 10;
        $config->containerClass = 'custom-pagination';

        $pager = new Pager(100, 1, $config);
        $this->assertEquals(10, $pager->getInfo()['itemsPerPage']);
    }

    /**
     * Test fluent interface
     */
    public function testFluentInterface(): void
    {
        $pager = new Pager();
        $result = $pager
            ->setTotalItems(100)
            ->setItemsPerPage(10)
            ->setCurrentPage(2)
            ->setUrl('/test')
            ->setNumLinks(7);

        $this->assertInstanceOf(Pager::class, $result);
        $this->assertEquals(100, $result->getInfo()['totalItems']);
        $this->assertEquals(10, $result->getInfo()['itemsPerPage']);
        $this->assertEquals(2, $result->getInfo()['currentPage']);
    }

    /**
     * Test total items validation
     */
    public function testTotalItemsValidation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Total items cannot be negative');

        $pager = new Pager();
        $pager->setTotalItems(-1);
    }

    /**
     * Test items per page validation
     */
    public function testItemsPerPageValidation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Items per page must be between');

        $pager = new Pager();
        $pager->setItemsPerPage(10000); // Above max limit
    }

    /**
     * Test current page validation
     */
    public function testCurrentPageValidation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Current page must be at least 1');

        $pager = new Pager();
        $pager->setCurrentPage(0);
    }

    /**
     * Test num links validation
     */
    public function testNumLinksValidation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Number of links must be between');

        $pager = new Pager();
        $pager->setNumLinks(100); // Above max limit
    }

    /**
     * Test basic pagination rendering
     */
    public function testBasicPaginationRendering(): void
    {
        $pager = new Pager(100, 1);
        $html = $pager->render();

        $this->assertStringContainsString('<nav', $html);
        $this->assertStringContainsString('aria-label="Pagination Navigation"', $html);
        $this->assertStringContainsString('<ul', $html);
        $this->assertStringContainsString('</ul>', $html);
        $this->assertStringContainsString('</nav>', $html);
    }

    /**
     * Test pagination with single page (no pagination needed)
     */
    public function testSinglePagePagination(): void
    {
        $pager = new Pager(10, 1); // 10 items, 20 per page = 1 page
        $html = $pager->render();

        $this->assertEmpty($html); // Should return empty string
    }

    /**
     * Test pagination with multiple pages
     */
    public function testMultiplePagePagination(): void
    {
        $pager = new Pager(100, 1); // 100 items, 20 per page = 5 pages
        $html = $pager->render();

        $this->assertNotEmpty($html);
        $this->assertStringContainsString('pagination', $html);
        $this->assertStringContainsString('page-link', $html);
        $this->assertStringContainsString('page-item', $html);
    }

    /**
     * Test first page rendering
     */
    public function testFirstPageRendering(): void
    {
        $pager = new Pager(100, 1);
        $html = $pager->render();

        // Should not show first/previous links on first page
        $this->assertStringNotContainsString('&amp;laquo;', $html);
        $this->assertStringNotContainsString('&amp;lt;', $html);

        // Should show next/last links
        $this->assertStringContainsString('&amp;gt;', $html);
        $this->assertStringContainsString('&amp;raquo;', $html);
    }

    /**
     * Test last page rendering
     */
    public function testLastPageRendering(): void
    {
        $pager = new Pager(100, 5); // 5th page (last page)
        $html = $pager->render();

        // Should show first/previous links
        $this->assertStringContainsString('&amp;laquo;', $html);
        $this->assertStringContainsString('&amp;lt;', $html);

        // Should not show next/last links
        $this->assertStringNotContainsString('&amp;gt;', $html);
        $this->assertStringNotContainsString('&amp;raquo;', $html);
    }

    /**
     * Test middle page rendering
     */
    public function testMiddlePageRendering(): void
    {
        $pager = new Pager(100, 3); // 3rd page
        $html = $pager->render();

        // Should show all navigation links
        $this->assertStringContainsString('&amp;laquo;', $html);
        $this->assertStringContainsString('&amp;lt;', $html);
        $this->assertStringContainsString('&amp;gt;', $html);
        $this->assertStringContainsString('&amp;raquo;', $html);
    }

    /**
     * Test active page styling
     */
    public function testActivePageStyling(): void
    {
        $pager = new Pager(100, 2);
        $html = $pager->render();

        $this->assertStringContainsString('aria-current="page"', $html);
        $this->assertStringContainsString('active', $html);
    }

    /**
     * Test URL generation
     */
    public function testUrlGeneration(): void
    {
        $pager = new Pager(100, 1);
        $pager->setUrl('/products');

        $html = $pager->render();

        // Should contain URLs with page parameter
        $this->assertStringContainsString('href="/products?', $html);
        $this->assertStringContainsString('page=', $html);
    }

    /**
     * Test custom URL parameter
     */
    public function testCustomUrlParameter(): void
    {
        $config = new PagerConfig();
        $config->pageParameter = 'p';

        $pager = new Pager(100, 1, $config);
        $pager->setUrl('/products');

        $html = $pager->render();

        // Should use custom parameter name
        $this->assertStringContainsString('p=', $html);
        $this->assertStringNotContainsString('page=', $html);
    }

    /**
     * Test accessibility features
     */
    public function testAccessibilityFeatures(): void
    {
        $config = new PagerConfig();
        $config->enableAccessibility = true;

        $pager = new Pager(100, 2, $config);
        $html = $pager->render();

        // Should contain ARIA labels
        $this->assertStringContainsString('aria-label=', $html);
        $this->assertStringContainsString('aria-current="page"', $html);
    }

    /**
     * Test pagination info
     */
    public function testPaginationInfo(): void
    {
        $pager = new Pager(95, 3); // 95 items, 20 per page, page 3

        $info = $pager->getInfo();

        $this->assertEquals(95, $info['totalItems']);
        $this->assertEquals(20, $info['itemsPerPage']);
        $this->assertEquals(3, $info['currentPage']);
        $this->assertEquals(5, $info['totalPages']);
        $this->assertTrue($info['needsPagination']);
        $this->assertEquals(41, $info['startItem']); // (3-1)*20+1 = 41
        $this->assertEquals(60, $info['endItem']); // min(95, 3*20) = 60
    }

    /**
     * Test start and end item calculations
     */
    public function testStartEndItems(): void
    {
        $pager = new Pager(100, 1);
        $this->assertEquals(1, $pager->getStartItem());
        $this->assertEquals(20, $pager->getEndItem());

        $pager->setCurrentPage(3);
        $this->assertEquals(41, $pager->getStartItem());
        $this->assertEquals(60, $pager->getEndItem());

        $pager->setCurrentPage(5);
        $this->assertEquals(81, $pager->getStartItem());
        $this->assertEquals(100, $pager->getEndItem()); // Last page
    }

    /**
     * Test ellipsis rendering
     */
    public function testEllipsisRendering(): void
    {
        $pager = new Pager(200, 10); // 200 items, 20 per page = 10 pages, page 10
        $html = $pager->render();

        // Should show ellipsis when there are many pages
        $this->assertStringContainsString('…', $html);
    }

    /**
     * Test custom text for navigation
     */
    public function testCustomNavigationText(): void
    {
        $pager = new Pager(100, 1);
        $pager
            ->setFirstText('First')
            ->setLastText('Last')
            ->setPreviousText('Prev')
            ->setNextText('Next');

        $html = $pager->render();

        $this->assertStringNotContainsString('First', $html); // Not shown on first page
        $this->assertStringNotContainsString('Prev', $html);  // Not shown on first page
        $this->assertStringContainsString('Next', $html);
        $this->assertStringContainsString('Last', $html);
    }

    /**
     * Test configuration validation
     */
    public function testConfigurationValidation(): void
    {
        $config = new PagerConfig();
        $errors = $config->validate();

        $this->assertIsArray($errors);
        $this->assertEmpty($errors); // Default config should be valid
    }

    /**
     * Test invalid configuration
     */
    public function testInvalidConfiguration(): void
    {
        $config = new PagerConfig();
        $config->defaultItemsPerPage = -1; // Invalid

        $errors = $config->validate();

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('defaultItemsPerPage', $errors[0]);
    }

    /**
     * Test URL building with query parameters
     */
    public function testUrlBuildingWithQueryParameters(): void
    {
        $pager = new Pager(100, 1);
        $pager->setUrl('/products?category=electronics&sort=price');

        $html = $pager->render();

        // Should preserve existing query parameters and add page
        $this->assertStringContainsString('category=electronics', $html);
        $this->assertStringContainsString('sort=price', $html);
        $this->assertStringContainsString('page=2', $html);
    }

    /**
     * Test custom CSS classes
     */
    public function testCustomCssClasses(): void
    {
        $config = new PagerConfig();
        $config->containerClass = 'my-pagination';
        $config->itemClass = 'my-page-item';
        $config->linkClass = 'my-page-link';
        $config->activeClass = 'my-active';

        $pager = new Pager(100, 1, $config);
        $html = $pager->render();

        $this->assertStringContainsString('my-pagination', $html);
        $this->assertStringContainsString('my-page-item', $html);
        $this->assertStringContainsString('my-page-link', $html);
        $this->assertStringContainsString('my-active', $html);
    }

    /**
     * Test disabled navigation links
     */
    public function testDisabledNavigationLinks(): void
    {
        $pager = new Pager(100, 1);
        $html = $pager->render();

        // Should have disabled class for non-clickable elements
        $this->assertStringContainsString('disabled', $html);
    }

    /**
     * Test error rendering
     */
    public function testErrorRendering(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Items per page must be between');

        $config = new PagerConfig();
        $config->minItemsPerPage = 10;
        $config->maxItemsPerPage = 5; // Invalid: max < min

        $pager = new Pager(100, 1, $config);
        $pager->render(); // This should throw an exception
    }

    /**
     * Test zero total items
     */
    public function testZeroTotalItems(): void
    {
        $pager = new Pager(0, 1);
        $html = $pager->render();

        $this->assertEmpty($html); // No pagination needed
    }

    /**
     * Test very large number of pages
     */
    public function testLargeNumberOfPages(): void
    {
        $pager = new Pager(10000, 250); // 10000 items, 20 per page = 500 pages
        $html = $pager->render();

        $this->assertNotEmpty($html);
        $this->assertStringContainsString('…', $html); // Should show ellipsis
    }

    /**
     * Test configuration from array
     */
    public function testConfigurationFromArray(): void
    {
        $configArray = [
            'defaultItemsPerPage' => 15,
            'containerClass' => 'custom-nav',
            'enableAccessibility' => false
        ];

        $config = PagerConfig::fromArray($configArray);

        $this->assertEquals(15, $config->defaultItemsPerPage);
        $this->assertEquals('custom-nav', $config->containerClass);
        $this->assertFalse($config->enableAccessibility);
    }

    /**
     * Test configuration to array
     */
    public function testConfigurationToArray(): void
    {
        $config = new PagerConfig();
        $config->defaultItemsPerPage = 25;

        $array = $config->toArray();

        $this->assertIsArray($array);
        $this->assertEquals(25, $array['defaultItemsPerPage']);
        $this->assertArrayHasKey('containerClass', $array);
    }
}
