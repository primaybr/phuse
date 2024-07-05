<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Define an interface for HTML components
interface ComponentInterface
{
    // Define a method to render the component as a string
    public function render(): string;
}