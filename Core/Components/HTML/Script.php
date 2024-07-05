<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Define a class for HTML script component
class Script implements ComponentInterface
{
    // Use the HTMLAttributes trait
    use ComponentTrait;

    // A property to store the source of the script
    protected string $src;
	
	// A property to store the content of the script
	protected string $content;

    // A constructor to initialize the source of the script
    public function __construct(string $src, string $content = '')
    {
        $this->src = $src;
		$this->content = $content;
	}

    // A method to render the script as a string
    public function render(): string
    {
		 // Generate the attribute string
        $attributeString = $this->generateAttributeString();

		if($this->src === 'script')
		{
			return "<script>{$this->escape($this->content)}</script>";
		}
		else
		{
			// Return the script element
			return "<script src=\"{$this->src}\" {$attributeString}></script>";
		}
		
       
    }
	
}
