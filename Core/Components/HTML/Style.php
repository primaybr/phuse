<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Define a class for HTML style component
class Style implements ComponentInterface
{
    // Use the HTMLAttributes trait
    use ComponentTrait;

	// A property to store the content of the style
	protected string $content;

    // A constructor to initialize the source of the style
    public function __construct(string $content = '')
    {
		$this->content = $content;
	}

    // A method to render the style as a string
    public function render(): string
    {
		 // Generate the attribute string
        $attributeString = $this->generateAttributeString();
		
		return "<style {$attributeString}>{$this->escape($this->content)}</style>";       
    }
}
