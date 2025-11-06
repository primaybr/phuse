<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Define a class for HTML link component
class Link implements ComponentInterface
{
    // Use the ComponentTrait trait
    use ComponentTrait;

	// A property to store the content of the link
	protected string $src;

    // A constructor to initialize the source of the link
    public function __construct(string $src = '')
    {
		$this->src = $src;
	}

    // A method to render the link as a string
    public function render(): string
    {
		 // Generate the attribute string
        $attributeString = $this->generateAttributeString();
		
		return "<link rel=\"stylesheet\" href=\"{$this->escape($this->src)}\" {$attributeString}>";
    }
}
