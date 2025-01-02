<?php

declare(strict_types=1);

namespace Core;

use Core\Exception\Handler;
use Core\Folder\Path;
use Core\Config;

/**
 * Class Base
 * Handles the core functionality of the application, including routing and environment configuration.
 */
class Base
{
    private const ERROR_REPORTING_LEVEL_PRODUCTION = E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE;
    private const ERROR_REPORTING_LEVEL_DEVELOPMENT = -1;

    private Config $config;
    private Handler $handler;
    
    public function __construct()
    {

        // check for php version required to run the framework
		if (version_compare(phpversion(), '8.2.0', '<='))
		{
			die('Minimum of PHP 8.2 is needed to run the framework, your php version is '.phpversion().' please upgrade your system!');
		}

        $this->config = new Config();
        $this->handler = new Handler();
        $this->init();
    }
    
    /**
     * Runs the application by loading the appropriate routes based on the environment.
     * Throws an exception if the environment is not set correctly.
     * 
     * @throws \Exception if the application environment is not set correctly.
     */
    public function run(): void
    {
        $routes = match ($this->config->getEnv()) {
            'development', 'local' => require_once Path::CONFIG . 'Routes.php',
            'production' => require_once Path::CONFIG . 'Routes.php',
            default => throw new \Exception('The application environment is not set correctly.')
        };

        $routes->run();
    }
    
    private function init(): void
    {
        ini_set('display_errors', $this->config->getEnv() === 'production' ? '0' : '1');
        error_reporting($this->config->getEnv() === 'production' ? self::ERROR_REPORTING_LEVEL_PRODUCTION : self::ERROR_REPORTING_LEVEL_DEVELOPMENT);

        set_error_handler([$this->handler, 'errorHandler']);
        ini_set('session.sid_length', '250');
        ini_set('session.sid_bits_per_character', '5');
        ini_set('session.save_path', realpath("../Session"));
        session_start();

        if (isset($_COOKIE[session_name()])) {
            setcookie(name: session_name(), value: $_COOKIE[session_name()]);
        }
    }
}
