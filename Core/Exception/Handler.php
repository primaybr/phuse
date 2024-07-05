<?php

declare(strict_types=1);

namespace Core\Exception;

use Core\Folder\Path as Path;
use Core\Config as Config;
use Core\Debug as Debug;

class Handler
{
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
        [$error, $log] = $this->codeMap($code);
        $data = [
            'message' => '[' . date('Y-m-d H:i:s') . '] ' . "$error ($code): $message on line $line, in file $file",
            'level' => $log,
            'code' => $code,
            'error' => $error,
            'line' => $line,
            'path' => $file,
            'timestamp' => time()
        ];

        $errorMessage = $data['message'];

        //write log
        if (file_exists(Path::LOGS . 'php_error.log')) {
            $handler = fopen(Path::LOGS . 'php_error.log', 'r');
            $filesize = filesize(Path::LOGS . 'php_error.log');

            if ($filesize) {
                $content = fread($handler, $filesize);
                fclose($handler);

                $newContent = json_decode($content, true);
                array_push($newContent, $data);
                $data = json_encode($newContent);
            } else {
                $data = json_encode([$data]);
            }

            $handler = fopen(Path::LOGS . 'php_error.log', 'w');
            $write = fwrite($handler, $data);
            fclose($handler);
        } else {
            $content = json_encode([$data]);

            error_log($content, 3, Path::LOGS . 'php_error.log');
        }

        $config = new Config();
        $env = $config->get()->env;

        if ('development' === $env || 'local' === $env) {
            http_response_code(500);
            echo json_encode([
                'status' => false,
                'message' => $message,
                'error' => $error,
                'line' => $line,
                'path' => $file,
            ]);
        } else {
            $error = new Error();
            $error->show('php');
        }

        exit;
    }

    /**
     * Map an error code
     *
     * @param int $code Error code
     * @return array Array of error
     */
    private function codeMap(int $code): array
    {
        $error = $log = null;
        switch ($code) {
            case E_PARSE:
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                $error = 'Fatal Error';
                $log = LOG_ERR;
                break;
            case E_WARNING:
            case E_USER_WARNING:
            case E_COMPILE_WARNING:
            case E_RECOVERABLE_ERROR:
                $error = 'Warning';
                $log = LOG_WARNING;
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $error = 'Notice';
                $log = LOG_NOTICE;
                break;
            case E_STRICT:
                $error = 'Strict';
                $log = LOG_NOTICE;
                break;
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $error = 'Deprecated';
                $log = LOG_NOTICE;
                break;
            default:
                break;
        }
        return [$error, $log];
    }
}
