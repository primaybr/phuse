<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Define a class for HTML tables
class Table implements ComponentInterface
{
    // Use the HTMLAttributes trait
    use ComponentTrait;

    // A method to render the table as a string
    public function render(): string
    {
        // Generate the attribute string
        $attributeString = $this->generateAttributeString();

        // Start the table element
        $table = "<table {$attributeString}>";

        // Loop through the components and render them
        foreach ($this->components as $component) {
            $table .= $component->render();
        }

        // End the table element
        $table .= "</table>";

        // Return the table element
        return $table;
    }
	
}
