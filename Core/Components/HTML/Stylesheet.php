<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Define a class for HTML stylesheet component
class Stylesheet implements ComponentInterface
{
    // Use the HTMLAttributes trait
    use ComponentTrait;

    // A property to store the source of the stylesheet
    protected string $src;
	
	// A property to store the content of the stylesheet
	protected string $content;

    // A constructor to initialize the source of the stylesheet
    public function __construct(string $src, string $content = '')
    {
        $this->src = $src;
		$this->content = $content;
	}

    // A method to render the stylesheet as a string
    public function render(): string
    {
		 // Generate the attribute string
        $attributeString = $this->generateAttributeString();

		if($this->src === 'style')
		{
			return "<style>{$this->escape($this->content)}</style>";
		}
		else
		{
			// Return the stylesheet element
			return "<link rel=\"stylesheet\" href=\"{$this->src}\" {$attributeString}>";
		}
		
       
    }
}
