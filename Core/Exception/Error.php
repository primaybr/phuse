<?php

declare(strict_types=1);

namespace Core\Exception;

use Core\Log;
use Core\Folder\Path as Path;
use Core\Debug as Debug;
use Core\Http\Session as Session;
use Core\Config as Config;

class Error extends \Exception
{
    private readonly Log $logger;
    private readonly Config $config;
    private readonly Debug $debug;
    private readonly Session $session;

    /**
     * Constructor
     *
     * @param string|Log|null $message Error message or Log instance (for backward compatibility)
     * @param Log|Config|null $logger Logger instance or Config instance (for backward compatibility)
     * @param Config|null $config Configuration instance
     */
    public function __construct(string|Log|null $message = '', Log|Config|null $logger = null, ?Config $config = null)
    {
        // Handle backward compatibility: if first param is Log instance, it's the old signature
        if ($message instanceof Log) {
            $logger = $message;
            $message = '';
        }

        // Handle backward compatibility: if second param is Config instance, it's the old signature
        if ($logger instanceof Config) {
            $config = $logger;
            $logger = null;
        }

        parent::__construct((string) $message);
        $this->logger = $logger ?? new Log();
        $this->config = $config ?? new Config();
        $this->debug = new Debug();
        $this->session = new Session();
    }

    /**
     * Show error template
     *
     * @param int|string $type HTTP response code or error type
     * @param bool $return Whether to return template content instead of outputting
     * @return string|null Template content if $return is true
     */
    public function show(int|string $type = 404, bool $return = false): ?string
    {
        // Set HTTP response code for numeric types
        if (is_numeric($type)) {
            http_response_code((int) $type);
        }

        // Get error template path
        $templatePath = Path::VIEWS . "error/{$type}.php";

        // Check if template exists, fallback to default
        if (!file_exists($templatePath)) {
            $templatePath = Path::VIEWS . 'error/default.php';
            $this->logger->write("Error template not found: {$type}.php, using default.php", LOG_WARNING);
        }

        // Get error details if available
        $lastError = error_get_last();
        $errorType = $lastError['type'] ?? null;
        $errorMessage = $lastError['message'] ?? null;

        // Log error if present
        if ($errorType && $errorMessage) {
            $this->logger->write(
                "Error displayed: {$errorMessage}",
                $this->getLogLevelFromErrorType($errorType),
                [
                    'error_type' => $errorType,
                    'template' => $type,
                    'file' => $lastError['file'] ?? 'unknown',
                    'line' => $lastError['line'] ?? 'unknown'
                ]
            );
        }

        // Load and return template
        if ($return) {
            return $this->loadTemplate($templatePath);
        }

        // Output template and exit
        echo $this->loadTemplate($templatePath);
        exit;
    }

    /**
     * Load error template content
     *
     * @param string $templatePath Path to template file
     * @return string Template content
     */
    private function loadTemplate(string $templatePath): string
    {
        if (!file_exists($templatePath)) {
            return $this->getDefaultErrorTemplate();
        }

        try {
            ob_start();
            require $templatePath;
            return ob_get_clean();
        } catch (\Throwable $e) {
            $this->logger->write(
                "Failed to load error template: {$templatePath}",
                LOG_ERR,
                ['exception' => $e->getMessage()]
            );

            return $this->getDefaultErrorTemplate();
        }
    }

    /**
     * Get default error template content
     *
     * @return string Default error template
     */
    private function getDefaultErrorTemplate(): string
    {
        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .error-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .error-title { color: #e74c3c; font-size: 24px; margin-bottom: 10px; }
        .error-message { color: #666; margin-bottom: 20px; }
        .error-details { background: #f8f9fa; padding: 15px; border-radius: 4px; font-family: monospace; font-size: 14px; }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-title">Oops! Something is not right.</h1>
        <p class="error-message">We encountered an unexpected error. Please try again later.</p>
        <div class="error-details">
            <strong>Error Code:</strong> ' . http_response_code() . '<br>
            <strong>Timestamp:</strong> ' . date('Y-m-d H:i:s') . '<br>
            <strong>Environment:</strong> ' . $this->config->getEnv() . '
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Convert PHP error type to log level
     *
     * @param int $errorType PHP error type constant
     * @return string Log level
     */
    private function getLogLevelFromErrorType(int $errorType): string
    {
        return match ($errorType) {
            E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR => 'error',
            E_WARNING, E_USER_WARNING, E_COMPILE_WARNING, E_RECOVERABLE_ERROR => 'warning',
            E_NOTICE, E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED => 'notice',
            default => 'info',
        };
    }

    /**
     * Display fatal error with debugging information
     *
     * @param string $message Error message
     * @param string $template Template content
     * @return string Modified template
     */
    private function showFatalError(string $message, string $template): string
    {
        $this->logger->write("Fatal error captured: {$message}", LOG_ERR);

        echo '<div style="background: #ffebee; border: 1px solid #f44336; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <strong style="color: #c62828;">Fatal Error Captured:</strong><br>
            <span style="color: #666;">' . htmlspecialchars($message) . '</span>
        </div>';

        $this->debug->pre(error_get_last());

        return $template;
    }

    /**
     * Output template or return it
     *
     * @param string $template Template content
     * @param bool $return Whether to return instead of output
     * @return string|null Template content if returning
     */
    private function outputTemplate(string $template, bool $return): ?string
    {
        if ($return) {
            return $template;
        }

        echo $template;
        return null;
    }
}
