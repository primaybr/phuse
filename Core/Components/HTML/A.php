<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Define a class for HTML a component
class A implements HTMLComponent
{
    // Use the HTMLAttributes trait
    use ComponentTrait;

    // A property to store the URL of the a
    protected string $url;

    // A property to store the text of the a
    protected string $text;

    // A constructor to initialize the URL and the text of the a
    public function __construct(string $url, string $text)
    {
        $this->url = $url;
        $this->text = $text;
    }

    // A method to render the a as a string
    public function render(): string
    {
        // Generate the attribute string
        $attributeString = $this->generateAttributeString();

        // Return the a element
        return "<a href=\"{$this->url}\" {$attributeString}>{$this->text}</a>";
    }
}
