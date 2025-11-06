<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Define a class for HTML section
class Section implements ComponentInterface
{
    // Use the ComponentTrait
    use ComponentTrait;

    // A property to store the content of the section
    protected string $content;

    // A constructor to initialize the content of the section
    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    // A method to render the section as a string
    public function render(): string
    {
        // Generate the attribute string
        $attributeString = $this->generateAttributeString();
		
		$section = "<section {$attributeString}>";
		// Loop through the components in the body
		if(!empty($this->components))
		{
			foreach ($this->components as $component) {
				// Add the component element
				$section .= $component->render();
			}
		}
		
		$section .= "{$this->escape($this->content)}</section>";


        // Return the section element
        return $section;
    }
}
