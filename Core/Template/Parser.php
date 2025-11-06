<?php

declare(strict_types=1);

namespace Core\Template;

use Core\Folder\Path as Path;
use Core\Exception\Error as Error;
use Core\Text\HTML;
use Core\Log as Log;
use Core\Cache\TemplateCache;

/**
 * Template Parser Class
 *
 * This class handles template rendering with support for variable replacement,
 * conditional statements, loops, and caching. It provides a flexible template
 * system for PHP applications.
 *
 * @package Core\Template
 * @author  Prima Yoga
 */
class Parser implements ParserInterface
{
    use ParserTrait;

    /** @var string The path to the template file */
    protected string $template;

    /** @var array The data to be passed to the template */
    protected array $data;

    /** @var Log The log object for logging operations */
    protected Log $log;

    /** @var TemplateCache The template cache instance */
    protected TemplateCache $cache;

    /** @var bool Whether to use template caching */
    protected bool $useCache;

    /** @var \Config\Template Configuration instance */
    protected \Config\Template $config;

    /**
     * Set the template file to be rendered
     *
     * @param string $template The name of the template file, relative to the views folder
     * @return self Returns the parser instance for method chaining
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
     * Set the data to be passed to the template
     *
     * @param array $data An associative array of key-value pairs
     * @return self Returns the parser instance for method chaining
     * @throws Error If the data is not an array
     */
    public function setData(mixed $data): self
    {
        // Validate that data is an array
        if (!is_array($data)) {
            $this->exception("The data must be an array.");
        }

        // Merge the data with the existing data
        if (!empty($this->data) && is_array($this->data)) {
            $this->data = array_merge($this->data, $data);
        } else {
            $this->data = $data;
        }

        return $this;
    }

    /**
     * Enable or disable template caching
     *
     * @param bool $enabled Whether to enable caching (default: true)
     * @return self Returns the parser instance for method chaining
     */
    public function enableCache(bool $enabled = true): self
    {
        $this->useCache = $enabled;
        return $this;
    }

    /**
     * Clear all cached templates
     *
     * @param bool $force Force clear even if auto-clear is disabled (default: false)
     * @return bool True on success, false on failure
     */
    public function clearCache(bool $force = false): bool
    {
        if ($force || $this->config->autoClearInDevelopment) {
            return $this->cache->clear();
        }
        return false;
    }

    public function __construct()
    {
        $this->config = new \Config\Template();
        $this->log = new Log();
        $this->cache = new TemplateCache();
        $this->useCache = $this->config->enableCache;
        $this->data = [];
    }

    /**
     * Render the template with the provided data
     *
     * @param string $template Optional. The name of the template file, relative to the views folder
     * @param array $data Optional. An associative array of key-value pairs
     * @param bool $return Optional. Whether to return the result or output it (default: false)
     * @return string|null The rendered template content or null if outputting directly
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

        // Check for cached version if caching is enabled
        $cacheKey = null;
        if ($this->useCache) {
            $cacheKey = $this->cache->generateKey($this->template, $this->data);
            
            if ($this->cache->hasValidCache($cacheKey)) {
                if ($return) {
                    return file_get_contents($this->cache->getCachePath($cacheKey));
                }
                include $this->cache->getCachePath($cacheKey);
                return null;
            }
        }

        // Start output buffering
        ob_start();

        // Extract the data to variables with security considerations
        if (!empty($this->data)) {
            // Use EXTR_SKIP to avoid overwriting existing variables
            // and only extract variables that are safe (no special characters in keys)
            $safeData = [];
            foreach ($this->data as $key => $value) {
                // Only extract variables with safe names (alphanumeric and underscore only)
                if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', (string) $key)) {
                    $safeData[$key] = $value;
                }
            }
            extract($safeData, EXTR_SKIP);
        }

        // Include the template file
        include $this->template;

        // Get the buffered content
        $content = ob_get_clean();

        // Parse the content with the data
        $content = $this->parseData($content, $this->data);

        // Cache the compiled template if caching is enabled
        if ($this->useCache && $cacheKey) {
            $this->cache->store($cacheKey, $content);
        }

        // Output or return the content
        if ($return) {
            return $content;
        }

        echo $content;
        return null;
    }

    /**
     * Render an error template with a message and exit
     *
     * @param string $message The error message to display
     * @param string $template Optional. The name of the error template file, relative to the views folder (default: "error/default")
     * @return never This method always exits the script
     * @throws Error If the message is empty
     */
    public function exception(string $message, string $template = "error/default"): never
    {
        // Check if the message is empty
        if (empty($message)) {
            $message = "An error occurred but no message was provided.";
        }

        $this->log->write($message);

        // Throw exception instead of exiting, allowing it to be caught
        throw new \Core\Exception\Error($message, $this->log, new \Core\Config());
    }

    /**
     * Parse the template with the data and replace placeholders with values
     *
     * @param string $template The template content to be parsed
     * @param array $data The data array used for replacement
     * @return string The parsed template content
     * @throws Error If the template is empty
     */
    public function parseData(string $template, array $data): string
    {
        // Check if the template is empty
        if (empty($template)) {
            $this->exception("The template '{$template}' not found.");
        }

        // Merge provided data with stored data
        $mergedData = $this->data;
        if (!empty($data)) {
            $mergedData = array_merge($mergedData, $data);
        }

        $template = $this->parseTemplate($template, $mergedData);

        //Minify the template
        $html = new HTML();
        $template = $html->minify($template);

        return $template;
    }
	
}
