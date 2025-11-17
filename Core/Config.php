<?php

declare(strict_types=1);

namespace Core;

use Core\Http\URI;
use Exception;
use Throwable;

/**
 * Class Config
 * Manages application configuration settings, loading them from a configuration file and providing access to them.
 * 
 * @author Prima Yoga
 */
class Config
{
    // Use readonly properties to prevent accidental modification
    private readonly array $config;
    private readonly URI $uri;
    private readonly string $env;

    /**
     * Config constructor.
     * Loads the configuration from the specified config file and initializes the URI and environment settings.
     * 
     * @throws \Exception if the configuration file cannot be loaded.
     */
    public function __construct()
    {
        $configFile = Folder\Path::CONFIG . 'Config.php';
        if (!file_exists($configFile)) {
            throw new Exception("Configuration file not found: $configFile");
        }
        try {
            $this->config = require $configFile;
        } catch (Throwable $e) {
            throw new Exception("Error loading configuration file: " . $e->getMessage());
        }
        $this->uri = new URI;
        $this->env = $this->config['env'] ?? 'production'; // Default to 'production' if not set
    }

    /**
     * Get config data
     * 
     * @param array $data optional data to merge with config
     * @return object config object
     * @throws Exception if there's an error loading the configuration file
     */
    public function get(array $data = []): object
    {
        if (empty($data)) {
            try {
                $config = $this->config;
            } catch (Throwable $e) {
                throw new Exception("Error accessing configuration data: " . $e->getMessage());
            }
        } else {
            $config = $data;
        }
        
        $newConfig = $this->processConfigArray($config);
        
        // Add baseUrl to site configuration if it exists
        if (isset($newConfig['site'])) {
            $newConfig['site']->baseUrl = $this->buildBaseUrl($newConfig);
        }

        return (object) $newConfig;
    }
    
    /**
     * Process a configuration array recursively
     * 
     * @param array $config The configuration array to process
     * @return array The processed configuration array
     */
    private function processConfigArray(array $config): array
    {
        $result = [];
        
        foreach ($config as $key => $val) {
            if (is_array($val)) {
                $result[$key] = (object) $this->processConfigArray($val);
            } else {
                $result[$key] = $val;
            }
        }
        
        return $result;
    }
    
    /**
     * Build the base URL from configuration
     * 
     * @param array $config The configuration array
     * @return string The base URL
     */
    private function buildBaseUrl(array $config): string
    {
        $baseUrl = !empty($config['site']->baseUrl) ? $config['site']->baseUrl : basename(trim(ROOT, DS));
        return $this->uri->getProtocol() . $this->uri->getHost() . '/' . 
               (!empty($baseUrl) ? $baseUrl . '/' : '');
    }

    /**
     * Set config data
     * 
     * @param string $key config key
     * @param mixed $val config value
     * @return self config object
     */
    public function set(string $key, mixed $val): self
    {
        $this->config[$key] = $val;
        return $this;
    }
    
    /**
     * Get the current environment
     * 
     * @return string environment
     */
    public function getEnv(): string
    {
        return $this->env;
    }
}
