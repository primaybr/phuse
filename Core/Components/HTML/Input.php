<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Define a class for HTML input component
class Input implements ComponentInterface
{
    // Use the ComponentTrait
    use ComponentTrait;

    // A property to store the type of the input
    protected string $type;

    // A property to store the name of the input
    protected string $name;

    // A property to store the value of the input
    protected string $value;

    // A constructor to initialize the type, the name, and the value of the input
    public function __construct(string $type, string $name, string $value)
    {
        $this->type = $type;
        $this->name = $name;
        $this->value = $value;
    }

    // A method to render the input as a string
    public function render(): string
    {
        // Generate the attribute string
        $attributeString = $this->generateAttributeString();

        // Return the input element
        return "<input type=\"{$this->type}\" name=\"{$this->name}\" value=\"{$this->value}\" {$attributeString}>";
    }
}