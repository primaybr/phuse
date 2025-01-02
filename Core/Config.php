<?php

declare(strict_types=1);

namespace Core;

use Core\Http\URI;
use Exception;
use Throwable;

/**
 * Class Config
 * Manages application configuration settings, loading them from a configuration file and providing access to them.
 */
class Config
{
    // Use readonly properties to prevent accidental modification
    private readonly array $config;
    private URI $uri;
    private string $env;

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
     */
    public function get(array $data = []): object
    {
        $newConfig = [];

        if (!$data) {
            try {
                $config = require Folder\Path::CONFIG.'Config.php';
            } catch (Throwable $e) {
                throw new Exception("Error loading configuration file: " . $e->getMessage());
            }
            if(!empty($this->config))
            {
                $config = array_merge($config,$this->config);
            }
            foreach ($config as $key => $val) {
                if (is_array($val)) {
                    $newConfig[$key] = $this->get($val);
                } else {
                    $newConfig[$key] = is_string($val) ? $val : (object) $val;
                }
            }
        } else {
            foreach ($data as $key => $val) {
                $newConfig[$key] = is_string($val) ? $val : (object) $val;
            }
        }

        if(isset($newConfig['site']))
        {
            $newConfig['site']->baseUrl = $this->uri->getProtocol().$this->uri->getHost().'/'.((isset($newConfig['site']->baseUrl) && !empty($newConfig['site']->baseUrl)) ? $newConfig['site']->baseUrl.'/' : '');
        }

        return (object) $newConfig;
    }

    /**
     * Set config data
     * 
     * @param string $key config key
     * @param string $val config value
     * @return self config object
     */
    public function set(string $key, string $val): self
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
