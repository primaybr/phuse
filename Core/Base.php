<?php

declare(strict_types=1);

namespace Core;

use Core\Exception\Handler;
use Core\Folder\Path;
use Core\Config;
use Exception;

/**
 * Class Base
 * Handles the core functionality of the application, including routing and environment configuration.
 * 
 * @author Prima Yoga
 */
class Base
{
    /**
     * Error reporting level for production environment
     * Excludes notices, strict standards, and user notices
     */
    private const ERROR_REPORTING_LEVEL_PRODUCTION = E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE;
    
    /**
     * Error reporting level for development environment
     * Reports all possible errors
     */
    private const ERROR_REPORTING_LEVEL_DEVELOPMENT = -1;
    
    /**
     * Minimum PHP version required
     */
    private const MIN_PHP_VERSION = '8.2.0';

    /**
     * @var Config Configuration instance
     */
    private Config $config;
    
    /**
     * @var Handler Exception handler instance
     */
    private Handler $handler;
    
    /**
     * Constructor initializes the application
     * 
     * @throws Exception If PHP version requirement is not met
     */
    public function __construct()
    {
        // Check for PHP version required to run the framework
        $this->checkPhpVersion();
        
        $this->config = new Config();
        $this->handler = new Handler();
        $this->init();
    }
    
    /**
     * Runs the application by loading the appropriate routes based on the environment.
     * Throws an exception if the environment is not set correctly.
     * 
     * @throws \Exception if the application environment is not set correctly.
     * @return void
     */
    public function run(): void
    {
        try {
            $env = $this->config->getEnv();
            $validEnvironments = ['development', 'local', 'production', 'testing'];
            
            if (!in_array($env, $validEnvironments)) {
                throw new Exception("Invalid environment: {$env}. Expected one of: " . implode(', ', $validEnvironments));
            }
            
            // Since both environments use the same file, we can simplify this
            $routes = require_once Path::CONFIG . 'Routes.php';
            $routes->run();
        } catch (Exception $e) {
            // Log the exception and display an appropriate error
            error_log("Application Error: " . $e->getMessage());
            http_response_code(500);
            
            if ($this->config->getEnv() !== 'production') {
                echo "<h1>Application Error</h1>";
                echo "<p>{$e->getMessage()}</p>";
            } else {
                echo "<h1>Server Error</h1>";
                echo "<p>The application encountered an error. Please try again later.</p>";
            }
            exit(1);
        }
    }
    
    /**
     * Initialize application settings, error handling, and session configuration
     * 
     * @return void
     */
    private function init(): void
    {
        $this->configureErrorHandling();
        $this->configureSessionSecurity();
        $this->startSession();
    }
    
    /**
     * Configure error handling based on environment
     * 
     * @return void
     */
    private function configureErrorHandling(): void
    {
        $isProduction = $this->config->getEnv() === 'production';
        
        // Configure error display and reporting based on environment
        ini_set('display_errors', $isProduction ? '0' : '1');
        error_reporting($isProduction ? self::ERROR_REPORTING_LEVEL_PRODUCTION : self::ERROR_REPORTING_LEVEL_DEVELOPMENT);
        
        // Set custom error handler
        set_error_handler([$this->handler, 'errorHandler']);
    }
    
    /**
     * Configure session security settings
     * 
     * @return void
     */
    private function configureSessionSecurity(): void
    {
        // Increase session ID length for better security
        ini_set('session.sid_length', '64');
        ini_set('session.sid_bits_per_character', '5');
        
        // Set session cookie parameters for better security
        $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        $sessionPath = realpath(Path::SESSION);
        
        if (!$sessionPath) {
            // If the path doesn't exist, create it relative to ROOT
            $sessionPath = ROOT . 'Session';
            if (!is_dir($sessionPath)) {
                mkdir($sessionPath, 0755, true);
            }
        }
        
        ini_set('session.save_path', $sessionPath);
        //ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', $isSecure ? '1' : '0');
        ini_set('session.cookie_samesite', 'Lax');
    }
    
    /**
     * Start the session if not already started
     * 
     * @return void
     */
    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Regenerate session ID periodically to prevent session fixation attacks
        
        if (isset($_SESSION['last_regeneration'])) {
            $regenerationTime = 30 * 60; // 30 minutes
            if (time() - $_SESSION['last_regeneration'] > $regenerationTime) {
                session_regenerate_id(true);
                $_SESSION['last_regeneration'] = time();
            }
        } else {
            $_SESSION['last_regeneration'] = time();
        }
        
    }
    
    /**
     * Check if the PHP version meets the minimum requirement
     * 
     * @throws Exception If PHP version is below minimum requirement
     * @return void
     */
    private function checkPhpVersion(): void
    {
        if (version_compare(phpversion(), self::MIN_PHP_VERSION, '<')) {
            throw new Exception(
                sprintf(
                    'Minimum PHP %s is required to run the framework. Your PHP version is %s. Please upgrade your system!',
                    self::MIN_PHP_VERSION,
                    phpversion()
                )
            );
        }
    }
}
