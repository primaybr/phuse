<?php

declare(strict_types=1);

namespace Core\Template;

use Core\Folder\Path as Path;
use Core\Exception\Error as Error;
use Core\Text\HTML;

class Parser implements ParserInterface
{
	use ParserTrait;

    // The path to the template file
	protected string $template;
	// The data to be passed to the template
	protected array $data;

    /**
     * The "setTemplate" method.
     *
     * Sets the template file to be rendered.
     *
     * @param string $template The name of the template file, relative to the views folder
     * @return self The template object
     * @throws Error If the template file is not found or not readable
     */
    public function setTemplate(string $template): self
    {
        // Normalize the directory separator
        $template = str_replace(['\\','/'], DS, $template);
        // Prepend the views folder path
        $template = Path::VIEWS . $template . '.php';

        // Check if the template file exists and is readable
        if (!is_file($template) || !is_readable($template)) {
            $this->exception("The template '{$template}' not found.");
        }

        // Assign the template file to the property
        $this->template = $template;

        return $this;
    }

    /**
     * The "setData" method.
     *
     * Sets the data to be passed to the template.
     *
     * @param array $data An associative array of key-value pairs
     * @return self The template object
     */
    public function setData(array $data): self
    {
        // Merge the data with the existing data
        if (!empty($this->data)) {
            $this->data = array_merge($this->data, $data);
        } else {
            $this->data = $data;
        }

        return $this;
    }

     /**
     * The "render" method.
     *
     * Renders the template with the data and outputs or returns the result.
     *
     * @param string $template Optional. The name of the template file, relative to the views folder
     * @param array $data Optional. An associative array of key-value pairs
     * @param bool $return Optional. Whether to return the result or output it
     * @return string|null The rendered template or null
     * @throws Error If the template is empty
     */
    public function render(string $template = "", array $data = [], bool $return = false): ?string
    {
        // Set the template file if provided
        if (!empty($template)) {
            $this->setTemplate($template);
        }

        // Set the data if provided
        if (!empty($data)) {
            $this->setData($data);
        }

        // Start output buffering
        ob_start();
        // Include the template file
        include $this->template;
        // Get the output buffer contents
        $output = ob_get_contents();
        // End output buffering and clean
        ob_end_clean();

        // Parse the output with the data
        $parser = $this->parseData($output, $this->data);

        // Return or output the result
        if ($return) {
            return $parser;
        }
		
		// Check if the parser is empty
		if (empty($parser)) {
			// Show an error template
			$error = new Error;
			$error->show();
		} else {
			// Output the parser
			echo $parser;
		}
        exit;
    }

    /**
     * The "exception" method.
     *
     * Renders an error template with a message and exits.
     *
     * @param string $message The error message to display
     * @param string $template Optional. The name of the error template file, relative to the views folder
     * @return void
     */
    public function exception(string $message, string $template = "error/default"): void
    {
        // Render the error template with the message and the date
        $this->render($template, ['message' => $message, 'date' => date('Y')]);
        // Exit the script
        exit;
    }

    /**
     * Parses the template with the data and replaces the placeholders with the values.
     *
     * @param string $template The template to be parsed
     * @param array $data The data to be passed to the template
     * @return string The parsed template
     * @throws Error If the template is empty
     */
    public function parseData(string $template, array $data): string
    {
        // Check if the template is empty
        if (empty($template)) {
            $this->exception("The template '{$template}' not found.");
        }
		
		// Minify the template
        $template = (new HTML)->minify($template);
	
        return $this->parseTemplate($template,$data);
    }
	
}
