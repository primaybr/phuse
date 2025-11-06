<?php

declare(strict_types=1);

namespace Core\Utilities\Pagination;

/**
 * Pagination Interface
 *
 * Defines the contract for pagination classes that can generate navigation
 * links for paginated content. This interface provides a fluent API for
 * configuring pagination settings and rendering HTML navigation elements.
 *
 * The pagination system supports:
 * - Configurable items per page and total items
 * - Customizable navigation text and CSS classes
 * - First/Last and Previous/Next navigation links
 * - Page number links with intelligent grouping
 * - URL generation with query parameters
 * - Accessibility features (ARIA labels)
 * - Bootstrap-compatible CSS classes
 *
 * @package Core\Utilities\Pagination
 */
interface PagerInterface
{
    // Configurable methods

    /**
     * Set the total number of items to paginate
     *
     * @param int $totalItems Total number of items across all pages
     * @return self Returns self for method chaining
     */
    public function setTotalItems(int $totalItems): self;

    /**
     * Set the number of items to display per page
     *
     * @param int $itemsPerPage Number of items per page (must be positive)
     * @return self Returns self for method chaining
     */
    public function setItemsPerPage(int $itemsPerPage): self;

    /**
     * Set the current active page number
     *
     * @param int $currentPage Current page number (1-based indexing)
     * @return self Returns self for method chaining
     */
    public function setCurrentPage(int $currentPage): self;

    /**
     * Set the base URL for pagination links
     *
     * @param string $url Base URL pattern for generating page links
     * @return self Returns self for method chaining
     */
    public function setUrl(string $url): self;

    /**
     * Set the number of page number links to display
     *
     * @param int $numLinks Number of page links to show in navigation
     * @return self Returns self for method chaining
     */
    public function setNumLinks(int $numLinks): self;

    /**
     * Set the text for the "First" page link
     *
     * @param string $firstText Text or HTML for the first page link
     * @return self Returns self for method chaining
     */
    public function setFirstText(string $firstText): self;

    /**
     * Set the text for the "Last" page link
     *
     * @param string $lastText Text or HTML for the last page link
     * @return self Returns self for method chaining
     */
    public function setLastText(string $lastText): self;

    /**
     * Set the text for the "Previous" page link
     *
     * @param string $previousText Text or HTML for the previous page link
     * @return self Returns self for method chaining
     */
    public function setPreviousText(string $previousText): self;

    /**
     * Set the text for the "Next" page link
     *
     * @param string $nextText Text or HTML for the next page link
     * @return self Returns self for method chaining
     */
    public function setNextText(string $nextText): self;

    /**
     * Set the CSS class for active page items
     *
     * @param string $activeClass CSS class name for active page state
     * @return self Returns self for method chaining
     */
    public function setActiveClass(string $activeClass): self;

    /**
     * Set the complete configuration object
     *
     * @param PagerConfig $config Configuration object with all settings
     * @return self Returns self for method chaining
     */
    public function setConfig(PagerConfig $config): self;

    /**
     * Render the pagination navigation as HTML
     *
     * Generates the complete HTML for the pagination navigation based on
     * current settings. Returns an empty string if there are no items to paginate.
     *
     * @return string HTML string for pagination navigation
     */
    public function render(): string;
}
