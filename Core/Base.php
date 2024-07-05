<?php

declare(strict_types=1);

namespace Core;

use Core\Exception\Handler;
use Core\Folder\Path;

class Base
{
    public $config;

    public function __construct()
    {
		// check for php version required to run the framework
		if (version_compare(phpversion(), '8.2.0', '<='))
		{
			die('Minimum of PHP 8.2 is needed to run the framework, your php version is '.phpversion().' please upgrade your system!');
		}
		
        $this->init();
    }

    public function run(): void
    {		
        $routes = match ($this->config->env) {
            'development', 'local' => require_once Path::CONFIG . 'Routes.php',
            'production' => require_once Path::CONFIG . 'Routes.php',
            default => throw new \Exception('The application environment is not set correctly.')
        };

        $routes->run();
    }

    private function init(): void
    {
        $this->config = (new Config())->get();
        
        ini_set(option: 'display_errors', value: $this->config->env === 'production' ? '0' : '1');
        error_reporting($this->config->env === 'production' ? E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE : -1);

        set_error_handler([new Handler(), 'errorHandler']);
        ini_set(option: 'session.save_path', value: realpath("../Session"));
        session_start();

        if (isset($_COOKIE[session_name()])) {
            setcookie(name: session_name(), value: $_COOKIE[session_name()]);
        }
    }
}
