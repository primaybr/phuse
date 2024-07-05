<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Define a class for HTML select component
class Select implements ComponentInterface
{
    // Use the ComponentTrait
    use ComponentTrait;

    // A property to store the options of the select
    protected array $options = [];
	
	// A property to store the selected options
	protected array $selected = [];

    // A method to add an option to the select
    public function addOption(string $value, string $text): self
    {
        $this->options[$value] = $text;
        return $this;
    }
	
	// A method to set selected options
	public function setSelected(string $value): self
	{
		$this->selected[] = $value;
        return $this;
	}

    // A method to render the select as a string
    public function render(): string
    {
        // Generate the attribute string
        $attributeString = $this->generateAttributeString();

        // Start the select element
        $select = "<select {$attributeString}>";

        // Loop through the options
        foreach ($this->options as $value => $text) {
			
			$selected = isset($this->selected[$value]) ? 'selected' : '';
			
            // Add the option element
            $select .= "<option value=\"{$value}\" $selected>{$text}</option>";
        }

        // End the select element
        $select .= "</select>";

        // Return the select element
        return $select;
    }
}
