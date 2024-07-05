<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Define a class for HTML embed component
class Embed implements ComponentInterface
{
    // Use the ComponentTrait
    use ComponentTrait;

    // A property to store the source of the embed
    protected string $src;

    // A property to store the type of the embed
    protected string $type;

    // A constructor to initialize the source and title of the embed
    public function __construct(string $src, string $type)
    {
        $this->src = $src;
        $this->type = $type;
    }

    // A method to render the embed as a string
    public function render(): string
    {
        // Generate the attribute string
        $attributeString = $this->generateAttributeString();

        // Return the embed element
        return "<embed src=\"{$this->src}\" type=\"{$this->type}\" {$attributeString} />";
    }
}
