<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Define a class for HTML paragraph component
class P implements ComponentInterface
{
    // Use the ComponentTrait
    use ComponentTrait;

    // A property to store the content of the paragraph
    protected string $content;

    // A constructor to initialize the content of the paragraph
    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    // A method to render the paragraph as a string
    public function render(): string
    {
        // Generate the attribute string
        $attributeString = $this->generateAttributeString();

        // Return the paragraph element
        return "<p {$attributeString}>{$this->content}</p>";
    }
}
