<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Define a class for HTML button component
class Button implements ComponentInterface
{
    // Use the ComponentTrait
    use ComponentTrait;

    // A property to store the content of the button
    protected string $content;

    // A constructor to initialize the content of the button
    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    // A method to render the button as a string
    public function render(): string
    {
        // Generate the attribute string
        $attributeString = $this->generateAttributeString();

        // Return the button element
        return "<button {$attributeString}>{$this->escape($this->content)}</button>";
    }
}
