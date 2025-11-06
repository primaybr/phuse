<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Define a trait for common methods of HTML components
trait ComponentTrait
{
	// An array to store the attributes of the component
    protected array $attributes = [];
	
	// A property to store the components
    protected array $components = [];
	
    // Define a method to escape HTML special characters
    protected function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
    }
	
	// A method to get an attribute of the component
    public function getAttribute(string $name): string
    {
        return $this->attributes[$name] ?? '';
    }

	// A method to add a component
    public function addComponent(ComponentInterface $component): self
    {
        $this->components[] = $component;
        return $this;
    }
    
    // A method to set an attribute of the component
    public function setAttribute(string $name, string $value): self
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    // A method to set multiple attributes at once
    public function setAttributes(array $attributes): self
    {
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }
        return $this;
    }

    // A method to add a CSS class
    public function addClass(string $class): self
    {
        $current = $this->getAttribute('class');
        $this->setAttribute('class', $current ? $current . ' ' . $class : $class);
        return $this;
    }

    // A method to set an ID
    public function setId(string $id): self
    {
        return $this->setAttribute('id', $id);
    }

    // A method to remove an attribute
    public function removeAttribute(string $name): self
    {
        unset($this->attributes[$name]);
        return $this;
    }

    // A method to check if an attribute exists
    public function hasAttribute(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    // A method to get all attributes
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    // A method to generate the attribute string for the component
    protected function generateAttributeString(): string
    {
        $attributeString = '';
        foreach ($this->attributes as $name => $value) {
            $attributeString .= "{$name}=\"{$this->escape($value)}\"";
        }
        return $attributeString;
    }
}
