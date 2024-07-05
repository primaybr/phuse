<?php

declare(strict_types=1);

namespace Core\Components\Pagination;
	
interface PagerInterface
{
    // Configurable methods
    public function setTotalItems(int $totalItems): void;
    public function setItemsPerPage(int $itemsPerPage): void;
    public function setCurrentPage(int $currentPage): void;
    public function setUrl(string $url): void;
    public function setNumLinks(int $numLinks): void;
    public function setFirstText(string $firstText): void;
    public function setLastText(string $lastText): void;
    public function setPreviousText(string $previousText): void;
    public function setNextText(string $nextText): void;
    public function setActiveClass(string $activeClass): void;

    // Rendering method
    public function render(): string;
}
