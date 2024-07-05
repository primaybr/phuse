<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Define a class for HTML iframe component
class Iframe implements ComponentInterface
{
    // Use the ComponentTrait
    use ComponentTrait;

    // A property to store the source of the iframe
    protected string $src;

    // A property to store the title of the iframe
    protected string $title;

    // A constructor to initialize the source and title of the iframe
    public function __construct(string $src, string $title)
    {
        $this->src = $src;
        $this->title = $title;
    }

    // A method to render the iframe as a string
    public function render(): string
    {
        // Generate the attribute string
        $attributeString = $this->generateAttributeString();

        // Return the iframe element
        return "<iframe src=\"{$this->src}\" title=\"{$this->title}\" {$attributeString}></iframe>";
    }
}
