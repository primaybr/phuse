<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Define a class for HTML tables
class Table implements ComponentInterface
{
    // Use the HTMLAttributes trait
    use ComponentTrait;

    // A property to store the header of the table
    protected array $header = [];

    // A property to store the rows of the table
    protected array $rows = [];

    // A method to set the header of the table
    public function setHeader(array $cells): self
    {
        $this->header = $cells;
        return $this;
    }

    // A method to add a row to the table
    public function addRow(array $cells): self
    {
        $this->rows[] = $cells;
        return $this;
    }

    // A method to render the table as a string
    public function render(): string
    {
        // Generate the attribute string
        $attributeString = $this->generateAttributeString();

        // Start the table element
        $table = "<table {$attributeString}>";

        // Start the header element
        $table .= "<thead>";

        // Start the header row element
        $table .= "<tr>";

        // Loop through the header cells
        foreach ($this->header as $cell) {
            // Add the header cell element
            $table .= "<th>{$cell}</th>";
        }

        // End the header row element
        $table .= "</tr>";

        // End the header element
        $table .= "</thead>";

        // Start the body element
        $table .= "<tbody>";

        // Loop through the rows
        foreach ($this->rows as $row) {
            // Start the row element
            $table .= "<tr>";

            // Loop through the cells
            foreach ($row as $cell) {
                // Add the cell element
                $table .= "<td>{$cell}</td>";
            }

            // End the row element
            $table .= "</tr>";
        }

        // End the body element
        $table .= "</tbody>";

        // End the table element
        $table .= "</table>";

        // Return the table element
        return $table;
    }
	
}
