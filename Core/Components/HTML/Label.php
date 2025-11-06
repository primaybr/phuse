<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Define a class for HTML label component
class Label implements ComponentInterface
{
    // Use the ComponentTrait
    use ComponentTrait;

    // A property to store the content of the span
    protected string $content;

    // A constructor to initialize the content of the span
    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    // A method to render the span as a string
    public function render(): string
    {
        // Generate the attribute string
        $attributeString = $this->generateAttributeString();

        // Return the span element
        return "<label {$attributeString}>{$this->escape($this->content)}</label>";
    }
}
