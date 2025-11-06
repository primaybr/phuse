<?php

declare(strict_types=1);

namespace Core\Utilities\Pagination;

/**
 * Pagination Configuration Class
 *
 * Provides comprehensive configuration options for the Pagination component,
 * including display settings, CSS classes, navigation text, accessibility features,
 * URL patterns, and validation rules. This class centralizes all pagination
 * customization options and provides validation to ensure settings are correct.
 *
 * Configuration areas include:
 * - Items per page limits and defaults
 * - CSS classes for styling (Bootstrap compatible)
 * - Navigation link text and symbols
 * - Accessibility features and ARIA labels
 * - URL generation patterns and parameters
 * - Logging and debugging options
 *
 * @package Core\Utilities\Pagination
 */
class PagerConfig
{
    /**
     * Default items per page
     *
     * The default number of items to display per page when not explicitly set.
     * This value must be between minItemsPerPage and maxItemsPerPage.
     *
     * @var int Default items per page (1-1000)
     */
    public int $defaultItemsPerPage = 20;

    /**
     * Maximum items per page allowed
     *
     * The maximum number of items that can be displayed per page.
     * This prevents users from requesting too many items at once.
     *
     * @var int Maximum items per page
     */
    public int $maxItemsPerPage = 1000;

    /**
     * Minimum items per page allowed
     *
     * The minimum number of items that can be displayed per page.
     * This ensures a reasonable minimum for pagination to be useful.
     *
     * @var int Minimum items per page
     */
    public int $minItemsPerPage = 1;

    /**
     * Default number of page links to show
     *
     * The default number of page number links to display in the navigation.
     * This value must be between minNumLinks and maxNumLinks.
     *
     * @var int Default number of page links (3-20)
     */
    public int $defaultNumLinks = 5;

    /**
     * Maximum number of page links to show
     *
     * The maximum number of page number links that can be displayed.
     * This prevents the navigation from becoming too wide.
     *
     * @var int Maximum number of page links
     */
    public int $maxNumLinks = 20;

    /**
     * Minimum number of page links to show
     *
     * The minimum number of page number links that must be displayed.
     * This ensures the navigation always shows a reasonable number of links.
     *
     * @var int Minimum number of page links
     */
    public int $minNumLinks = 3;

    /**
     * CSS class for the pagination container
     *
     * The CSS class applied to the main pagination container element.
     * This is typically used with CSS frameworks like Bootstrap.
     *
     * @var string CSS class for pagination container
     */
    public string $containerClass = 'pagination';

    /**
     * CSS class for page items
     *
     * The CSS class applied to individual page items within the pagination.
     * Each page number, first, previous, next, and last link is wrapped in this class.
     *
     * @var string CSS class for page items
     */
    public string $itemClass = 'page-item';

    /**
     * CSS class for page links
     *
     * The CSS class applied to the actual anchor tags within page items.
     * This controls the styling of clickable page navigation elements.
     *
     * @var string CSS class for page links
     */
    public string $linkClass = 'page-link';

    /**
     * CSS class for active page item
     *
     * The CSS class applied to the currently active page item.
     * This visually indicates which page the user is currently viewing.
     *
     * @var string CSS class for active page state
     */
    public string $activeClass = 'active';

    /**
     * CSS class for disabled page item
     *
     * The CSS class applied to disabled navigation items (like "Previous" on first page).
     * This provides visual feedback for non-clickable navigation elements.
     *
     * @var string CSS class for disabled page state
     */
    public string $disabledClass = 'disabled';

    /**
     * Text for the first page link
     *
     * The text or HTML displayed for the "First" page navigation link.
     * Common symbols include «, ≪, or "First" text.
     *
     * @var string Text for first page link
     */
    public string $firstText = '&laquo;';

    /**
     * Text for the last page link
     *
     * The text or HTML displayed for the "Last" page navigation link.
     * Common symbols include », ≫, or "Last" text.
     *
     * @var string Text for last page link
     */
    public string $lastText = '&raquo;';

    /**
     * Text for the previous page link
     *
     * The text or HTML displayed for the "Previous" page navigation link.
     * Common symbols include <, ←, or "Previous" text.
     *
     * @var string Text for previous page link
     */
    public string $previousText = '&lt;';

    /**
     * Text for the next page link
     *
     * The text or HTML displayed for the "Next" page navigation link.
     * Common symbols include >, →, or "Next" text.
     *
     * @var string Text for next page link
     */
    public string $nextText = '&gt;';

    /**
     * Show first/last links
     *
     * Whether to display "First" and "Last" page navigation links.
     * When true, adds jump-to-beginning and jump-to-end functionality.
     *
     * @var bool Whether to show first/last navigation links
     */
    public bool $showFirstLast = true;

    /**
     * Show previous/next links
     *
     * Whether to display "Previous" and "Next" page navigation links.
     * When true, adds sequential navigation between adjacent pages.
     *
     * @var bool Whether to show previous/next navigation links
     */
    public bool $showPrevNext = true;

    /**
     * Show page numbers
     *
     * Whether to display numbered page links in the navigation.
     * When false, only shows first/previous/next/last navigation.
     *
     * @var bool Whether to show numbered page links
     */
    public bool $showPageNumbers = true;

    /**
     * Base URL pattern for pagination links
     *
     * The base URL used for generating pagination links. This can include
     * a placeholder pattern like '/products/page/{page}' or be a simple base URL.
     * If empty, query parameters will be used instead.
     *
     * @var string URL pattern for pagination links
     */
    public string $urlPattern = '';

    /**
     * Query parameter name for page number
     *
     * The name of the query parameter used to specify the page number.
     * For example, 'page', 'p', or 'offset' are common parameter names.
     *
     * @var string Query parameter name for pagination
     */
    public string $pageParameter = 'page';

    /**
     * Enable logging using Core\Log
     *
     * Whether to enable logging of pagination operations and events.
     * When enabled, uses the framework's Core\Log system for consistent logging.
     *
     * @var bool Whether to enable pagination logging
     */
    public bool $enableLogging = true;

    /**
     * Log file name (without extension)
     *
     * The base name for log files created by the pagination component.
     * The framework's logging system will handle the full path and extension.
     *
     * @var string Base name for pagination log files
     */
    public string $logFileName = 'pagination_component';

    /**
     * Enable accessibility features (ARIA labels)
     *
     * Whether to include accessibility features like ARIA labels and screen reader support.
     * When enabled, adds proper semantic markup for assistive technologies.
     *
     * @var bool Whether to enable accessibility features
     */
    public bool $enableAccessibility = true;

    /**
     * Screen reader text for first page
     *
     * Accessible text for screen readers describing the first page link.
     * This provides context for users of assistive technologies.
     *
     * @var string Screen reader text for first page
     */
    public string $srFirstText = 'First page';

    /**
     * Screen reader text for last page
     *
     * Accessible text for screen readers describing the last page link.
     * This provides context for users of assistive technologies.
     *
     * @var string Screen reader text for last page
     */
    public string $srLastText = 'Last page';

    /**
     * Screen reader text for previous page
     *
     * Accessible text for screen readers describing the previous page link.
     * This provides context for users of assistive technologies.
     *
     * @var string Screen reader text for previous page
     */
    public string $srPreviousText = 'Previous page';

    /**
     * Screen reader text for next page
     *
     * Accessible text for screen readers describing the next page link.
     * This provides context for users of assistive technologies.
     *
     * @var string Screen reader text for next page
     */
    public string $srNextText = 'Next page';

    /**
     * Screen reader text pattern for page numbers
     *
     * Template for screen reader text describing numbered page links.
     * The %d placeholder will be replaced with the actual page number.
     *
     * @var string Screen reader text template for page numbers
     */
    public string $srPageText = 'Page %d';

    /**
     * Create configuration from array
     *
     * Creates a new PagerConfig instance and populates it with values from
     * the provided array. Only properties that exist in the class will be set.
     * This provides an easy way to configure pagination from application settings.
     *
     * @param array $config Associative array of configuration options
     * @return self New PagerConfig instance with the provided settings
     *
     * @example
     * $config = PagerConfig::fromArray([
     *     'defaultItemsPerPage' => 25,
     *     'containerClass' => 'my-pagination',
     *     'enableAccessibility' => true
     * ]);
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
     * Convert configuration to array
     *
     * Returns all public properties of the configuration object as an array.
     * This is useful for serialization, debugging, or passing config to views.
     * All properties including defaults are included in the output.
     *
     * @return array Associative array of all configuration properties
     *
     * @example
     * $configArray = $pagerConfig->toArray();
     * // Returns all current configuration as key-value pairs
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * Validate configuration
     *
     * Validates all configuration values to ensure they are within acceptable ranges
     * and logically consistent. Returns an array of error messages for any invalid settings.
     * This helps catch configuration errors before they cause issues in pagination rendering.
     *
     * @return array Array of validation error messages. Empty array if all settings are valid.
     *
     * @example
     * $errors = $pagerConfig->validate();
     * if (!empty($errors)) {
     *     echo "Configuration errors: " . implode(', ', $errors);
     * }
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->defaultItemsPerPage < $this->minItemsPerPage || $this->defaultItemsPerPage > $this->maxItemsPerPage) {
            $errors[] = 'defaultItemsPerPage must be between minItemsPerPage and maxItemsPerPage';
        }

        if ($this->minItemsPerPage < 1) {
            $errors[] = 'minItemsPerPage must be at least 1';
        }

        if ($this->maxItemsPerPage < $this->minItemsPerPage) {
            $errors[] = 'maxItemsPerPage must be greater than or equal to minItemsPerPage';
        }

        if ($this->defaultNumLinks < $this->minNumLinks || $this->defaultNumLinks > $this->maxNumLinks) {
            $errors[] = 'defaultNumLinks must be between minNumLinks and maxNumLinks';
        }

        if ($this->minNumLinks < 1) {
            $errors[] = 'minNumLinks must be at least 1';
        }

        if ($this->maxNumLinks < $this->minNumLinks) {
            $errors[] = 'maxNumLinks must be greater than or equal to minNumLinks';
        }

        if (empty($this->containerClass)) {
            $errors[] = 'containerClass cannot be empty';
        }

        if (empty($this->itemClass)) {
            $errors[] = 'itemClass cannot be empty';
        }

        if (empty($this->linkClass)) {
            $errors[] = 'linkClass cannot be empty';
        }

        if (empty($this->activeClass)) {
            $errors[] = 'activeClass cannot be empty';
        }

        if (empty($this->disabledClass)) {
            $errors[] = 'disabledClass cannot be empty';
        }

        if (empty($this->pageParameter)) {
            $errors[] = 'pageParameter cannot be empty';
        }

        return $errors;
    }

    /**
     * Get full URL with page parameter
     *
     * Generates a complete URL for a specific page number, either using URL patterns
     * or query parameters. This method handles both pretty URLs and query string pagination.
     * If a URL pattern is defined, it will use pattern replacement; otherwise, it adds
     * the page parameter as a query string.
     *
     * @param string $baseUrl The base URL to modify
     * @param int $page The page number to include in the URL
     * @return string Complete URL with page parameter
     *
     * @example
     * // With URL pattern: /products/page/{page}
     * $url = $config->buildPageUrl('/products/page/', 3); // Returns: /products/page/3
     *
     * // With query parameter: /products?page=3
     * $url = $config->buildPageUrl('/products', 3); // Returns: /products?page=3
     */
    public function buildPageUrl(string $baseUrl, int $page): string
    {
        if (empty($this->urlPattern)) {
            return $this->addPageParameter($baseUrl, $page);
        }

        return str_replace('{page}', (string)$page, $this->urlPattern);
    }

    /**
     * Add page parameter to URL
     *
     * Private helper method that adds the page parameter as a query string
     * to the given URL. This method handles URL parsing and query parameter merging.
     * It preserves existing query parameters while adding or updating the page parameter.
     *
     * @param string $url The base URL to modify
     * @param int $page The page number to add as a query parameter
     * @return string URL with page parameter added as query string
     *
     * @example
     * // URL without existing query: /products -> /products?page=3
     * // URL with existing query: /products?category=1 -> /products?category=1&page=3
     */
    private function addPageParameter(string $url, int $page): string
    {
        $urlParts = parse_url($url);
        $query = '';

        if (isset($urlParts['query'])) {
            parse_str($urlParts['query'], $queryParams);
        } else {
            $queryParams = [];
        }

        $queryParams[$this->pageParameter] = $page;
        $query = '?' . http_build_query($queryParams);

        return ($urlParts['path'] ?? '') . $query;
    }
}
