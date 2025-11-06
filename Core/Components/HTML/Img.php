<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Define a class for HTML img component
class Img implements ComponentInterface
{
    // Use the ComponentTrait
    use ComponentTrait;

    // A property to store the source of the img
    protected string $src;

    // A property to store the alternative text of the img
    protected string $alt;

    // A constructor to initialize the source and the alternative text of the img
    public function __construct(string $src, string $alt)
    {
        $this->src = $src;
        $this->alt = $alt;
    }

    // A method to render the img as a string
    public function render(): string
    {
        // Generate the attribute string
        $attributeString = $this->generateAttributeString();

        // Return the img element
        return "<img src=\"{$this->escape($this->src)}\" alt=\"{$this->escape($this->alt)}\" {$attributeString}>";
    }
}
