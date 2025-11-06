<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Define a class for HTML main
class Main implements ComponentInterface
{
    // Use the ComponentTrait
    use ComponentTrait;

    // A property to store the content of the main
    protected string $content;

    // A constructor to initialize the content of the main
    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    // A method to render the main as a string
    public function render(): string
    {
        // Generate the attribute string
        $attributeString = $this->generateAttributeString();
		
		$main = "<main {$attributeString}>";
		// Loop through the components in the body
		if(!empty($this->components))
		{
			foreach ($this->components as $component) {
				// Add the component element
				$main .= $component->render();
			}
		}
		
		$main .= "{$this->escape($this->content)}</main>";


        // Return the main element
        return $main;
    }
}
