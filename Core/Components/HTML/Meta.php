<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Define a class for HTML meta
class Meta implements ComponentInterface
{
    // Use the ComponentTrait
    use ComponentTrait;

    // A property to store the content of the meta
    protected string $content;

    // A constructor to initialize the content of the meta
    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    // A method to render the meta as a string
    public function render(): string
    {
        // Generate the attribute string
        $attributeString = $this->generateAttributeString();

        // Return the meta element
        return "<meta {$attributeString}='{$this->escape($this->content)}'>";
    }
}
