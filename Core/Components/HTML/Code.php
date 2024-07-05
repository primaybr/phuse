<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Define a class for HTML code component
class Code implements ComponentInterface
{
    // Use the ComponentTrait
    use ComponentTrait;

    // A property to store the content of the code
    protected string $content;

    // A constructor to initialize the content of the code
    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    // A method to render the code as a string
    public function render(): string
    {
        // Generate the attribute string
        $attributeString = $this->generateAttributeString();

        // Return the code element
        return "<code {$attributeString}>{$this->content}</code>";
    }
}
