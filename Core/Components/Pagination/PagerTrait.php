<?php

declare(strict_types=1);

namespace Core\Components\Pagination;
	
trait PagerTrait {
	
	// Properties used in pagination
    protected $totalItems;
    protected $itemsPerPage = 20;
    protected $currentPage = 1;
    protected $url = '';
    protected $numLinks = 5;
    protected $firstText = '&laquo;';
    protected $lastText = '&raquo;';
    protected $previousText = '&lt;';
    protected $nextText = '&gt;';
    protected $activeClass = 'active';

    public function setTotalItems(int|string $totalItems): void {
        $this->totalItems = $totalItems;
    }

    public function setItemsPerPage(int|string $itemsPerPage): void {
        $this->itemsPerPage = $itemsPerPage;
    }

    public function setCurrentPage(int|string $currentPage): void {
        $this->currentPage = $currentPage;
    }

    public function setUrl(string $url): void {
        $this->url = $url;
    }

    public function setNumLinks(int|string $numLinks): void {
        $this->numLinks = $numLinks;
    }

    public function setFirstText(string $firstText): void {
        $this->firstText = $firstText;
    }

    public function setLastText(string $lastText): void {
        $this->lastText = $lastText;
    }

    public function setPreviousText(string $previousText): void {
        $this->previousText = $previousText;
    }

    public function setNextText(string $nextText): void {
        $this->nextText = $nextText;
    }

    public function setActiveClass(string $activeClass): void {
        $this->activeClass = $activeClass;
    }
	
}
