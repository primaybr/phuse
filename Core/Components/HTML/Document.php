<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Define a class for HTML document component
class Document implements ComponentInterface
{
    // Use the ComponentTrait
    use ComponentTrait;

    // A property to store the head component of the document
    protected Head $head;

    // A property to store the components of the document
    protected array $components = [];

    // A constructor to initialize the head component of the document
    public function __construct(Head $head)
    {
        $this->head = $head;
    }

    // A method to render the document as a string
    public function render(): string
    {
        // Generate the attribute string
        $attributeString = $this->generateAttributeString();

        // Start the document element
        $document = "<!DOCTYPE html><html {$attributeString}>";

        // Add the head element
        $document .= $this->head->render();

        // Start the body element
        $document .= "<body>";

        // Loop through the components in the body
        foreach ($this->components as $component) {
            // Add the component element
            $document .= $component->render();
        }

        // End the body element
        $document .= "</body>";

        // End the document element
        $document .= "</html>";

        // Return the document element
        return $document;
    }
}

