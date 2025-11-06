<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Define a class for HTML head component
class Head implements ComponentInterface
{
    // Use the ComponentTrait
    use ComponentTrait;

    // A property to store the title of the document
    protected string $title;

    // A constructor to initialize the title of the document
    public function __construct(string $title = '')
    {
        $this->title = $title;
    }

    // A method to render the head as a string
    public function render(): string
    {
        // Generate the attribute string
        $attributeString = $this->generateAttributeString();

        // Start the head element
        $head = "<head {$attributeString}>";

        // Add the title element
        $head .= "<title>{$this->escape($this->title)}</title>";

        // Loop through the components in the head
        foreach ($this->components as $component) {
            // Add the component element
            $head .= $component->render();
        }

        // End the head element
        $head .= "</head>";

        // Return the head element
        return $head;
    }
}
