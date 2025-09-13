<?php

declare(strict_types=1);

namespace Core\Components\Pagination;

class Pager implements PagerInterface
{
    use PagerTrait;

    // Use constructor property promotion to simplify the constructor
    public function __construct( int|string $totalItems, int|string $currentPage = 1) 
	{
        $this->setTotalItems($totalItems);
        $this->setCurrentPage($currentPage);
    }

    public function render(): string
    {
        $output = "<ul class='pagination'>";
        $output .= $this->generatePaginationLinks();
        $output .= "</ul>";

        return $output;
    }

    // Generates pagination links based on the current state
    protected function generatePaginationLinks(): string
    {
        $links = [];
        
        // If the current Page isn't first
        if ($this->currentPage != 1 && $this->currentPage != 0) {
            // First link
            $links[] = $this->getPageLink(1, $this->firstText);

            // Previous link
            if ($this->currentPage > 1) {
                $links[] = $this->getPageLink($this->currentPage - 1, $this->previousText);
            }
        }

        // Current page and its neighbors
        $start = max(1, $this->currentPage - floor($this->numLinks / 2));
        $end = min($start + $this->numLinks - 1, $this->getTotalPages());

        for ($i = $start; $i <= $end; $i++) {
            $links[] = $this->getPageLink($i, $i, $i == $this->currentPage || ($this->currentPage == 0 && $i == 1));
        }

        $this->currentPage = $this->currentPage < 1 ? 1 : $this->currentPage;

        // If the current page isn't last page
        if ($this->currentPage != $this->getTotalPages()) {
            // Next link
            if ($this->currentPage < $this->getTotalPages()) {
                $links[] = $this->getPageLink($this->currentPage + 1, $this->nextText);
            }

            // Last link
            $links[] = $this->getPageLink($this->getTotalPages(), $this->lastText);
        }

        return implode(' ', $links);
    }

    // Generates a single page link
	protected function getPageLink(mixed $pageNumber, mixed $text, bool $isActive = false): string
    {
        //check for query string
        $urlParts = parse_url($this->url);
        
        if (isset($urlParts['query'])) {
            parse_str($urlParts['query'], $queryParams);
        }

        $queryParams['page'] = $pageNumber;
        $query = '?'.http_build_query($queryParams);

        $url = $urlParts['path'] . $query;
        $active = $isActive ? $this->activeClass : '';

        // Use a ternary operator to simplify the conditional logic
        $html = $isActive
            ? '<li class="page-item ' . $active . '"><span class="page-link">' . $text . '</span></li>'
            : '<li class="page-item" ><a href="' . $url . '" class="page-link">' . $text . '</a></li>';

        return $html;
    }

    // Calculates the total number of pages
    protected function getTotalPages(): int|float
    {
        return ceil($this->totalItems / $this->itemsPerPage);
    }
}
