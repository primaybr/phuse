<?php

declare(strict_types=1);

namespace Config;

/**
 * Template Configuration
 * 
 * This file contains configuration options for the template system,
 * including caching settings and other template-related options.
 */
class Template
{
    /**
     * Enable or disable template caching
     * 
     * @var bool
     */
    public bool $enableCache = true;

    /**
     * Cache lifetime in seconds
     * 
     * @var int
     */
    public int $cacheLifetime = 3600; // 1 hour

    /**
     * Cache directory (relative to the main cache directory)
     * 
     * @var string
     */
    public string $cacheDir = 'templates';

    /**
     * Whether to automatically clear the cache when in development mode
     * 
     * @var bool
     */
    public bool $autoClearInDevelopment = true;

    /**
     * @var \Core\Config Configuration instance
     */
    protected $config;

    /**
     * Constructor
     * 
     * Loads environment-specific settings using Core\Config
     */
    public function __construct()
    {
        try {
            $this->config = new \Core\Config();
            
            // Check if we're in development mode
            if ($this->config->get()->env === 'development') {
                $this->autoClearInDevelopment = true;
            }
        } catch (\Exception $e) {
            // Fallback to default settings if config loading fails
            $this->autoClearInDevelopment = true;
        }
    }
}
