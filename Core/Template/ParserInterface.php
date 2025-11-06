<?php

declare(strict_types=1);

namespace Core\Template;

/**
 * Interface for template parser implementations
 *
 * Defines the contract for template parsing functionality including
 * template loading, data setting, rendering, and error handling.
 */
interface ParserInterface
{
    /**
     * Set the template file to be rendered
     *
     * @param string $template The name of the template file, relative to the views folder
     * @return self Returns the parser instance for method chaining
     * @throws \Core\Exception\Error If the template file is not found or not readable
     */
    public function setTemplate(string $template): self;

    /**
     * Set the data to be passed to the template
     *
     * @param array $data An associative array of key-value pairs
     * @return self Returns the parser instance for method chaining
     * @throws \Core\Exception\Error If the data is not an array
     */
    public function setData(mixed $data): self;

    /**
     * Render the template with the provided data
     *
     * @param string $template Optional. The name of the template file, relative to the views folder
     * @param array $data Optional. An associative array of key-value pairs
     * @param bool $return Optional. Whether to return the result or output it
     * @return string|null The rendered template content or null if outputting directly
     * @throws \Core\Exception\Error If the template is empty
     */
    public function render(string $template = "", array $data = [], bool $return = false): ?string;

    /**
     * Render an error template with a message and exit
     *
     * @param string $message The error message to display
     * @param string $template Optional. The name of the error template file, relative to the views folder
     * @return never This method always exits the script
     * @throws \Core\Exception\Error If the message is empty
     */
    public function exception(string $message, string $template = "error/default"): never;

    /**
     * Parse the template with the data and replace placeholders with values
     *
     * @param string $template The template content to be parsed
     * @param array $data The data array used for replacement
     * @return string The parsed template content
     * @throws \Core\Exception\Error If the template is empty
     */
    public function parseData(string $template, array $data): string;
}