<?php

declare(strict_types=1);

namespace Core\Template;

// Define an interface for the template class
interface ParserInterface
{
    public function setTemplate(string $template): self;
    public function setData(array $data): self;
    public function render(string $template = "", array $data = [], bool $return = false): ?string;
    public function exception(string $message, string $template = "error/default"): void;
	public function parseData(string $template, array $data): string;
}