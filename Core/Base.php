<?php

declare(strict_types=1);

namespace Core;

use Core\Exception\Handler;
use Core\Exception\SystemException;
use Core\Folder\Path;
use Core\Config;
use Core\Container;
use Core\Middleware\MiddlewareStack;
use Core\Http\Session;
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
    private const ERROR_REPORTING_LEVEL_PRODUCTION = E_ALL & ~E_NOTICE & ~E_USER_NOTICE;
    
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
     * @var Log Logger instance
     */
    private Log $logger;
    
    /**
     * @var Config Configuration instance
     */
    private Config $config;
    
    /**
     * @var Handler Exception handler instance
     */
    private Handler $handler;
    
    /**
     * @var Session Session management instance
     */
    private Session $session;
    
    /**
     * @var Container Dependency injection container instance
     */
    private Container $container;
    
    /**
     * Constructor initializes the application
     * 
     * @throws SystemException If PHP version requirement is not met
     */
    public function __construct()
    {
        // Check for PHP version required to run the framework
        $this->checkPhpVersion();
        
        $this->container = new Container();
        $this->config = $this->container->set('config', Config::class, true)->get('config');
        $this->logger = new Log();
        $this->handler = new Handler($this->logger, $this->config);
        $this->session = new Session($this->logger);
        $this->init();
    }
    
    /**
     * Runs the application by loading the appropriate routes based on the environment.
     * Throws an exception if the environment is not set correctly.
     * 
     * @throws SystemException if the application environment is not set correctly.
     * @return void
     */
    public function run(): void
    {
        try {
            $env = $this->config->getEnv();
            $validEnvironments = ['development', 'local', 'production', 'testing'];
            
            if (!in_array($env, $validEnvironments)) {
                throw new SystemException(
                    "Invalid environment: {$env}. Expected one of: " . implode(', ', $validEnvironments),
                    ['environment' => $env, 'valid_environments' => $validEnvironments]
                );
            }
            
            // Create middleware stack with routing as the final handler
            $middlewareStack = new MiddlewareStack(function() {
                $routes = require_once Path::CONFIG . 'Routes.php';
                return $routes->run();
            });
            
            // Process through middleware stack
            $middlewareStack->process();
        } catch (Exception $e) {
            // Log the exception and display an appropriate error
            $this->logger->write(
                "Application Error: " . $e->getMessage(),
                'error',
                [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            );

            http_response_code(500);

            if ($this->config->getEnv() !== 'production') {
                echo json_encode([
                    'status' => false,
                    'error' => 'Application Error',
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'error' => 'Server Error',
                    'message' => 'The application encountered an error. Please try again later.',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
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
        // Session is now handled by the Session class in the constructor
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
     * Get the Session instance
     * 
     * @return Session The session management instance
     */
    public function getSession(): Session
    {
        return $this->session;
    }
    
    /**
     * Check if the PHP version meets the minimum requirement
     * 
     * @throws SystemException If PHP version is below minimum requirement
     * @return void
     */
    private function checkPhpVersion(): void
    {
        if (version_compare(phpversion(), self::MIN_PHP_VERSION, '<')) {
            throw new SystemException(
                sprintf(
                    'Minimum PHP %s is required to run the framework. Your PHP version is %s. Please upgrade your system!',
                    self::MIN_PHP_VERSION,
                    phpversion()
                ),
                [
                    'required_version' => self::MIN_PHP_VERSION,
                    'current_version' => phpversion()
                ]
            );
        }
    }
}
