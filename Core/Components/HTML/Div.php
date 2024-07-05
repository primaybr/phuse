<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Define a class for HTML divs
class Div implements ComponentInterface
{
    // Use the ComponentTrait
    use ComponentTrait;

    // A property to store the content of the div
    protected string $content;

    // A constructor to initialize the content of the div
    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    // A method to render the div as a string
    public function render(): string
    {
        // Generate the attribute string
        $attributeString = $this->generateAttributeString();
		
		$div = "<div {$attributeString}>";
		// Loop through the components in the body
		if(!empty($this->components))
		{
			foreach ($this->components as $component) {
				// Add the component element
				$div .= $component->render();
			}
		}
		
		$div .= "{$this->content}</div>";


        // Return the div element
        return $div;
    }
}
