<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Define a class for HTML canvas component
class Canvas implements ComponentInterface
{
    // Use the ComponentTrait
    use ComponentTrait;

	// A property to store the content script of the canvas
    protected string $script;

    // A constructor to initialize the content of the canvas
    public function __construct(string $script)
    {
        $this->script = $script;
    }

    // A method to render the canvas as a string
    public function render(): string
    {
        // Generate the attribute string
        $attributeString = $this->generateAttributeString();

        // Return the canvas element
        return "<canvas {$attributeString}></canvas><script>{$this->script}</script>";
    }
}
