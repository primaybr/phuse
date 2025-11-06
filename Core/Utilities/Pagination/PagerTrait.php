<?php

declare(strict_types=1);

namespace Core\Utilities\Pagination;

use Core\Log;
use InvalidArgumentException;

/**
 * Pagination Trait
 *
 * Provides core pagination functionality for calculating pages, validating input,
 * and managing pagination state. This trait contains all the essential methods
 * for pagination logic and is designed to be used with classes that implement
 * the PagerInterface.
 *
 * The trait manages:
 * - Pagination configuration and initialization
 * - Page calculations and bounds checking
 * - Input validation and error handling
 * - URL generation for navigation links
 * - Logging integration with Core\Log
 * - State management for pagination parameters
 *
 * This trait uses protected properties that should be declared in the using class:
 * - $config: PagerConfig instance for configuration
 * - $logger: Log instance for logging operations
 * - $totalItems: Total number of items to paginate
 * - $itemsPerPage: Number of items per page
 * - $currentPage: Current active page number
 * - $url: Base URL for pagination links
 * - $numLinks: Number of page links to display
 * - $firstText, $lastText, $previousText, $nextText: Navigation link text
 * - $activeClass: CSS class for active page state
 * - $initialized: Flag to prevent multiple initialization
 *
 * @package Core\Utilities\Pagination
 */
trait PagerTrait
{
    protected PagerConfig $config;
    protected ?Log $logger;
    protected int $totalItems = 0;
    protected int $itemsPerPage = 20;
    protected int $currentPage = 1;
    protected string $url = '';
    protected int $numLinks = 5;
    protected string $firstText = '&laquo;';
    protected string $lastText = '&raquo;';
    protected string $previousText = '&lt;';
    protected string $nextText = '&gt;';
    protected string $activeClass = 'active';
    protected bool $initialized = false;

    /**
     * Initialize pagination with configuration
     */
    protected function initialize(?PagerConfig $config = null): void
    {
        if ($this->initialized) {
            return;
        }

        $this->config = $config ?? new PagerConfig();
        $this->logger = $this->createLogger();
        $this->initialized = true;
    }

    /**
     * Create logger instance using Core\Log
     */
    protected function createLogger(): ?Log
    {
        if ($this->config->enableLogging) {
            $log = new Log();
            $log->setLogName($this->config->logFileName);
            return $log;
        }
        return null;
    }

    /**
     * Set configuration
     */
    public function setConfig(PagerConfig $config): self
    {
        $this->config = $config;
        $this->logger = $this->createLogger();
        return $this;
    }

    /**
     * Set total number of items
     */
    public function setTotalItems(int $totalItems): self
    {
        if ($totalItems < 0) {
            throw new InvalidArgumentException('Total items cannot be negative');
        }

        $this->totalItems = $totalItems;

        if ($this->logger) {
            $this->log('INFO', 'Total items set', ['totalItems' => $totalItems]);
        }

        return $this;
    }

    /**
     * Set items per page
     */
    public function setItemsPerPage(int $itemsPerPage): self
    {
        if ($itemsPerPage < $this->config->minItemsPerPage || $itemsPerPage > $this->config->maxItemsPerPage) {
            throw new InvalidArgumentException(
                "Items per page must be between {$this->config->minItemsPerPage} and {$this->config->maxItemsPerPage}"
            );
        }

        $this->itemsPerPage = $itemsPerPage;

        if ($this->logger) {
            $this->log('INFO', 'Items per page set', ['itemsPerPage' => $itemsPerPage]);
        }

        return $this;
    }

    /**
     * Set current page
     */
    public function setCurrentPage(int $currentPage): self
    {
        if ($currentPage < 1) {
            throw new InvalidArgumentException('Current page must be at least 1');
        }

        $this->currentPage = $currentPage;

        if ($this->logger) {
            $this->log('INFO', 'Current page set', ['currentPage' => $currentPage]);
        }

        return $this;
    }

    /**
     * Set base URL for pagination links
     */
    public function setUrl(string $url): self
    {
        $this->url = rtrim($url, '?&');

        if ($this->logger) {
            $this->log('INFO', 'Base URL set', ['url' => $url]);
        }

        return $this;
    }

    /**
     * Set number of page links to show
     */
    public function setNumLinks(int $numLinks): self
    {
        if ($numLinks < $this->config->minNumLinks || $numLinks > $this->config->maxNumLinks) {
            throw new InvalidArgumentException(
                "Number of links must be between {$this->config->minNumLinks} and {$this->config->maxNumLinks}"
            );
        }

        $this->numLinks = $numLinks;

        if ($this->logger) {
            $this->log('INFO', 'Number of links set', ['numLinks' => $numLinks]);
        }

        return $this;
    }

    /**
     * Set text for navigation links
     */
    public function setFirstText(string $firstText): self
    {
        $this->firstText = $firstText;
        return $this;
    }

    public function setLastText(string $lastText): self
    {
        $this->lastText = $lastText;
        return $this;
    }

    public function setPreviousText(string $previousText): self
    {
        $this->previousText = $previousText;
        return $this;
    }

    public function setNextText(string $nextText): self
    {
        $this->nextText = $nextText;
        return $this;
    }

    /**
     * Set CSS class for active items
     */
    public function setActiveClass(string $activeClass): self
    {
        $this->activeClass = $activeClass;
        return $this;
    }

    /**
     * Get total number of pages
     */
    protected function getTotalPages(): int
    {
        if ($this->itemsPerPage <= 0) {
            return 1;
        }
        return (int)ceil($this->totalItems / $this->itemsPerPage);
    }

    /**
     * Get current page (ensuring it's within bounds)
     */
    protected function getCurrentPage(): int
    {
        $totalPages = $this->getTotalPages();
        return min(max(1, $this->currentPage), $totalPages);
    }

    /**
     * Check if pagination is needed
     */
    protected function needsPagination(): bool
    {
        return $this->totalItems > $this->itemsPerPage;
    }

    /**
     * Validate pagination state
     */
    protected function validate(): array
    {
        $errors = $this->config->validate();

        if ($this->totalItems < 0) {
            $errors[] = 'Total items cannot be negative';
        }

        if ($this->itemsPerPage < 1) {
            $errors[] = 'Items per page must be at least 1';
        }

        if ($this->currentPage < 1) {
            $errors[] = 'Current page must be at least 1';
        }

        $totalPages = $this->getTotalPages();
        if ($this->currentPage > $totalPages && $totalPages > 0) {
            $errors[] = 'Current page cannot be greater than total pages';
        }

        return $errors;
    }

    /**
     * Log a message using Core\Log
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger) {
            $contextString = empty($context) ? '' : ' ' . json_encode($context);
            $this->logger->write("[$level] $message$contextString");
        }
    }
}
