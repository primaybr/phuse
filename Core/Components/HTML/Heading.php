<?php

declare(strict_types=1);

namespace Core\Components\HTML;

// Define a class for HTML heading component
class Heading implements ComponentInterface
{
    // Use the ComponentTrait
    use ComponentTrait;

    // A property to store the level of the heading
    protected int $level;

    // A property to store the content of the heading
    protected string $content;

    // A constructor to initialize the level and the content of the heading
    public function __construct(int $level, string $content)
    {
        $this->level = $level;
        $this->content = $content;
    }

    // A method to render the heading as a string
    public function render(): string
    {
        // Generate the attribute string
        $attributeString = $this->generateAttributeString();

        // Return the heading element
        return "<h{$this->level} {$attributeString}>{$this->content}</h{$this->level}>";
    }
}
