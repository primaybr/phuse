<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Define a class for HTML video component
class Video implements ComponentInterface
{
    // Use the ComponentTrait
    use ComponentTrait;

    // A property to store the source of the video
    protected array $src;

    // A constructor to initialize the source of the video
    public function __construct(string $src, string $type)
    {
        $this->src[$src] = $type;
    }
	
	// A method to set the source of the video
	public function setSource(string $src, string $type)
	{
		$this->src[$src] = $type;
	}

    // A method to render the video as a string
    public function render(): string
    {
        // Generate the attribute string
        $attributeString = $this->generateAttributeString();
		
		$source = '';
		// Generate the source element
		foreach($this->src as $src => $type)
		{
			$source .= "<source src='{$this->escape($src)}' type='{$this->escape($type)}'>";
		}

        // Return the video element
        return "<video {$attributeString}>{$source}</video>";
    }
}
