<?php

declare(strict_types=1);

namespace Core\Utilities\Pagination;

use InvalidArgumentException;

/**
 * Pagination Class
 *
 * A comprehensive pagination component that generates HTML navigation for paginated content.
 * This class implements the PagerInterface and uses PagerTrait to provide a fluent API
 * for configuring and rendering pagination controls.
 *
 * The pagination system provides:
 * - Bootstrap-compatible HTML output
 * - Configurable navigation elements (first, previous, next, last)
 * - Intelligent page number grouping with ellipsis
 * - URL generation with query parameters or custom patterns
 * - Comprehensive validation and error handling
 * - Accessibility features with ARIA labels
 * - Logging integration with Core\Log
 * - Memory efficient rendering
 *
 * Example usage:
 * ```php
 * $pager = new Pager(150, 1); // 150 items, page 1
 * $pager->setItemsPerPage(20)
 *        ->setUrl('/products')
 *        ->setNumLinks(7);
 * echo $pager->render();
 * ```
 *
 * @package Core\Utilities\Pagination
 */
class Pager implements PagerInterface
{
    use PagerTrait;

    /**
     * Create a new pager instance
     *
     * Initializes a new pagination instance with the specified parameters.
     * This constructor provides a quick way to create a functional pager
     * with minimal configuration. Additional settings can be configured
     * using the fluent interface methods.
     *
     * @param int $totalItems Total number of items to paginate (default: 0)
     * @param int $currentPage Current active page number (default: 1)
     * @param PagerConfig|null $config Custom configuration object (optional)
     *
     * @example
     * // Simple pager with defaults
     * $pager = new Pager(150, 1);
     *
     * // Pager with custom configuration
     * $config = new PagerConfig();
     * $config->defaultItemsPerPage = 25;
     * $pager = new Pager(150, 1, $config);
     */
    public function __construct(int $totalItems = 0, int $currentPage = 1, ?PagerConfig $config = null)
    {
        $this->initialize($config);
        $this->setTotalItems($totalItems);
        $this->setCurrentPage($currentPage);
        // Set default items per page from config if not explicitly set
        if ($config && $totalItems > 0) {
            $this->setItemsPerPage($config->defaultItemsPerPage);
        }
    }

    /**
     * Render the pagination HTML
     *
     * Generates the complete HTML navigation for the current pagination state.
     * This method performs validation, checks if pagination is needed, and
     * renders the appropriate navigation elements based on configuration.
     *
     * The output includes:
     * - Bootstrap-compatible navigation structure
     * - First/Previous/Next/Last navigation links (if enabled)
     * - Numbered page links with intelligent ellipsis handling
     * - Accessibility features (ARIA labels) if enabled
     * - Proper URL generation for all links
     *
     * Returns an empty string if pagination is not needed or if there are validation errors.
     *
     * @return string HTML string for pagination navigation or empty string
     *
     * @example
     * $html = $pager->render();
     * if (!empty($html)) {
     *     echo '<nav aria-label="Pagination">' . $html . '</nav>';
     * }
     */
    public function render(): string
    {
        $errors = $this->validate();
        if (!empty($errors)) {
            $this->log('ERROR', 'Pagination validation failed', ['errors' => $errors]);
            return $this->renderError($errors);
        }

        if (!$this->needsPagination()) {
            $this->log('INFO', 'No pagination needed', [
                'totalItems' => $this->totalItems,
                'itemsPerPage' => $this->itemsPerPage
            ]);
            return '';
        }

        $totalPages = $this->getTotalPages();
        $currentPage = $this->getCurrentPage();

        $this->log('INFO', 'Rendering pagination', [
            'totalItems' => $this->totalItems,
            'itemsPerPage' => $this->itemsPerPage,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages
        ]);

        return $this->renderPagination($currentPage, $totalPages);
    }

    /**
     * Render the pagination navigation
     */
    private function renderPagination(int $currentPage, int $totalPages): string
    {
        $html = '<nav aria-label="Pagination Navigation">';
        $html .= '<ul class="' . htmlspecialchars($this->config->containerClass) . '">';

        // First page link
        if ($this->config->showFirstLast && $currentPage > 1) {
            $html .= $this->renderPageLink(1, $this->firstText, 'first', $this->config->srFirstText);
        }

        // Previous page link
        if ($this->config->showPrevNext && $currentPage > 1) {
            $html .= $this->renderPageLink(
                (int)($currentPage - 1),
                $this->previousText,
                'previous',
                $this->config->srPreviousText
            );
        }

        // Page number links
        if ($this->config->showPageNumbers) {
            $html .= $this->renderPageNumbers($currentPage, $totalPages);
        }

        // Next page link
        if ($this->config->showPrevNext && $currentPage < $totalPages) {
            $html .= $this->renderPageLink(
                (int)($currentPage + 1),
                $this->nextText,
                'next',
                $this->config->srNextText
            );
        }

        // Last page link
        if ($this->config->showFirstLast && $currentPage < $totalPages) {
            $html .= $this->renderPageLink((int)$totalPages, $this->lastText, 'last', $this->config->srLastText);
        }

        $html .= '</ul>';
        $html .= '</nav>';

        return $html;
    }

    /**
     * Render page number links with ellipsis logic
     */
    private function renderPageNumbers(int $currentPage, int $totalPages): string
    {
        $html = '';
        $start = max(1, $currentPage - floor($this->numLinks / 2));
        $end = min($totalPages, $start + $this->numLinks - 1);

        // Adjust start if we're near the end
        if ($end - $start + 1 < $this->numLinks) {
            $start = max(1, $end - $this->numLinks + 1);
        }

        // First page if not in range
        if ($start > 1) {
            $html .= $this->renderPageLink(1, '1', 'page', sprintf($this->config->srPageText, 1));
            if ($start > 2) {
                $html .= $this->renderEllipsis();
            }
        }

        // Page number links
        for ($i = (int)$start; $i <= (int)$end; $i++) {
            $isActive = ($i === $currentPage);
            $html .= $this->renderPageLink(
                $i,
                (string)$i,
                'page',
                sprintf($this->config->srPageText, $i),
                $isActive
            );
        }

        // Last page if not in range
        if ((int)$end < (int)$totalPages) {
            if ((int)$end < (int)$totalPages - 1) {
                $html .= $this->renderEllipsis();
            }
            $html .= $this->renderPageLink(
                (int)$totalPages,
                (string)$totalPages,
                'page',
                sprintf($this->config->srPageText, $totalPages)
            );
        }

        return $html;
    }

    /**
     * Render a single page link
     */
    private function renderPageLink(int $page, string $text, string $type, string $srText, bool $isActive = false): string
    {
        $url = $this->buildUrl($page);
        $itemClass = $this->config->itemClass;
        $linkClass = $this->config->linkClass;
        $activeClass = $isActive ? ' ' . $this->config->activeClass : '';
        $disabledClass = ($isActive || $page < 1) ? ' ' . $this->config->disabledClass : '';

        $attributes = [
            'class' => $itemClass . $activeClass . $disabledClass,
            'aria-current' => $isActive ? 'page' : null,
        ];

        if ($this->config->enableAccessibility && !empty($srText)) {
            $attributes['aria-label'] = $srText;
        }

        $attrString = $this->buildAttributes($attributes);

        if ($isActive) {
            return '<li ' . $attrString . '><span class="' . $linkClass . '">' . htmlspecialchars($text) . "</span></li>";
        }

        return '<li ' . $attrString . '><a href="' . htmlspecialchars($url) . '" class="' . $linkClass . '">' . htmlspecialchars($text) . "</a></li>";
    }

    /**
     * Render ellipsis for pagination
     */
    private function renderEllipsis(): string
    {
        return '<li class="' . htmlspecialchars($this->config->itemClass) . ' ' . htmlspecialchars($this->config->disabledClass) . '">' .
               '<span class="' . htmlspecialchars($this->config->linkClass) . '">…</span></li>';
    }

    /**
     * Build URL for a specific page
     */
    private function buildUrl(int $page): string
    {
        if (empty($this->url)) {
            return $this->config->buildPageUrl('', $page);
        }
        return $this->config->buildPageUrl($this->url, $page);
    }

    /**
     * Build HTML attributes string
     */
    private function buildAttributes(array $attributes): string
    {
        $attrStrings = [];
        foreach ($attributes as $name => $value) {
            if ($value === null) {
                continue;
            }
            $attrStrings[] = htmlspecialchars($name) . '="' . htmlspecialchars($value) . '"';
        }
        return implode(' ', $attrStrings);
    }

    /**
     * Render error message
     */
    private function renderError(array $errors): string
    {
        $errorText = 'Pagination Error: ' . implode(', ', $errors);
        return "<!-- {$errorText} -->";
    }

    /**
     * Get pagination info for debugging
     *
     * Returns comprehensive information about the current pagination state,
     * including calculated values and boundaries. This is useful for debugging
     * pagination issues or displaying pagination information to users.
     *
     * @return array Array containing pagination information
     *
     * @example
     * $info = $pager->getInfo();
     * // Returns: ['totalItems' => 150, 'itemsPerPage' => 20, 'currentPage' => 1, ...]
     */
    public function getInfo(): array
    {
        return [
            'totalItems' => $this->totalItems,
            'itemsPerPage' => $this->itemsPerPage,
            'currentPage' => $this->getCurrentPage(),
            'totalPages' => $this->getTotalPages(),
            'needsPagination' => $this->needsPagination(),
            'startItem' => $this->getStartItem(),
            'endItem' => $this->getEndItem(),
        ];
    }

    /**
     * Get the starting item number for current page
     *
     * Calculates the absolute position of the first item on the current page.
     * This is useful for displaying "Showing items X-Y of Z" messages
     * or for database queries with LIMIT and OFFSET.
     *
     * @return int The 1-based index of the first item on current page
     *
     * @example
     * // Page 3 with 20 items per page
     * $startItem = $pager->getStartItem(); // Returns 41
     */
    public function getStartItem(): int
    {
        return ($this->getCurrentPage() - 1) * $this->itemsPerPage + 1;
    }

    /**
     * Get the ending item number for current page
     *
     * Calculates the absolute position of the last item on the current page.
     * This is useful for displaying "Showing items X-Y of Z" messages
     * or for database queries with LIMIT and OFFSET. The end item will
     * never exceed the total number of items.
     *
     * @return int The 1-based index of the last item on current page
     *
     * @example
     * // Page 3 with 20 items per page, total 50 items
     * $endItem = $pager->getEndItem(); // Returns 50 (not 60)
     */
    public function getEndItem(): int
    {
        return min($this->totalItems, $this->getCurrentPage() * $this->itemsPerPage);
    }
}
