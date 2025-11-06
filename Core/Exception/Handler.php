<?php

declare(strict_types=1);

namespace Core\Exception;

use Core\Log;
use Core\Folder\Path as Path;
use Core\Config as Config;
use Core\Debug as Debug;
use Core\Exception\Error;

class Handler
{
    private readonly Log $logger;
    private readonly Config $config;

    /**
     * Constructor
     *
     * @param Log|null $logger Logger instance
     * @param Config|null $config Configuration instance
     */
    public function __construct(?Log $logger = null, ?Config $config = null)
    {
        $this->logger = $logger ?? new Log();
        $this->config = $config ?? new Config();
    }

    /**
     * Custom error handler
     *
     * @param int $code Error code
     * @param string $message Error message
     * @param string $file File name
     * @param int $line Line number
     * @return void
     */
    public function errorHandler(int $code, string $message, string $file, int $line): void
    {
        [$error, $logLevel] = $this->codeMap($code);

        // Create error message with context
        $errorMessage = sprintf(
            '%s (%d): %s on line %d, in file %s',
            $error,
            $code,
            $message,
            $line,
            $file
        );

        // Prepare context data
        $context = [
            'error_code' => $code,
            'error_type' => $error,
            'file' => $file,
            'line' => $line,
            'memory_usage' => memory_get_usage(true),
            'php_version' => PHP_VERSION,
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Log error using framework's logging system
        $this->logger->write($errorMessage, $logLevel, $context);

        $env = $this->config->getEnv();

        if ('development' === $env || 'local' === $env) {
            http_response_code(500);
            echo json_encode([
                'status' => false,
                'message' => $message,
                'error' => $error,
                'line' => $line,
                'path' => $file,
                'timestamp' => date('Y-m-d H:i:s'),
                'memory_usage' => memory_get_usage(true),
                'php_version' => PHP_VERSION
            ]);
        } else {
            $errorDisplay = new Error('', $this->logger, $this->config);
            $errorDisplay->show('php');
        }

        exit;
    }

    /**
     * Map an error code to severity and log level
     *
     * @param int $code Error code
     * @return array Array of [error_type, log_level]
     */
    private function codeMap(int $code): array
    {
        return match ($code) {
            E_PARSE, E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR => ['Fatal Error', LOG_ERR],
            E_WARNING, E_USER_WARNING, E_COMPILE_WARNING, E_RECOVERABLE_ERROR => ['Warning', LOG_WARNING],
            E_NOTICE, E_USER_NOTICE => ['Notice', LOG_NOTICE],
            E_DEPRECATED, E_USER_DEPRECATED => ['Deprecated', LOG_NOTICE],
            default => ['Unknown Error', LOG_ERR],
        };
    }
}
