<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Define a class for HTML lists component
class Lists implements ComponentInterface
{
    // Use the ComponentTrait
    use ComponentTrait;

    // A property to store the items of the lists
    protected array $items = [];

    // A property to store the type of the lists
    protected string $type;

    // A constructor to initialize the type of the lists
    public function __construct(string $type)
    {
        $this->type = $type;
    }

    // A method to add an item to the lists
    public function addItem(string $item): self
    {
        $this->items[] = $item;
        return $this;
    }

    // A method to render the lists as a string
    public function render(): string
    {
        // Generate the attribute string
        $attributeString = $this->generateAttributeString();

        // Start the lists element
        $lists = "<{$this->type} {$attributeString}>";

        // First render any child components
        foreach ($this->components as $component) {
            $lists .= "<li>{$component->render()}</li>";
        }

        // Then render text items
        foreach ($this->items as $item) {
            // Add the item element
            $lists .= "<li>{$this->escape($item)}</li>";
        }

        // End the lists element
        $lists .= "</{$this->type}>";

        // Return the lists element
        return $lists;
    }
}
