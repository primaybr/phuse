<?php

declare(strict_types=1);

namespace Core;

use Core\Http\URI;

class Config
{
    // Use readonly properties to prevent accidental modification
    private readonly array $config;
	private URI $uri;

    public function __construct()
    {
        $this->config = require Folder\Path::CONFIG . 'Config.php' ?? [];
		$this->uri = new URI;
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
            $config = require Folder\Path::CONFIG.'Config.php';
			
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
	
}
