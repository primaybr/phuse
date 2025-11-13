<?php

declare(strict_types=1);

namespace Core;

use Core\Folder\Path as Path;

/**
 * Class Log
 *
 * Handles logging functionality for the application, including writing messages to log files.
 *
 * @package Core
 * @author  Prima Yoga
 */
class Log
{
    private string $logFile;
    private $pointer = null;
    private string $time;
    private bool $fileExists = true;

    /**
     * Log constructor.
     * Initializes the log instance and sets the current timestamp.
     *
     * @throws Exception If the log file cannot be opened.
     */
    public function __construct()
    {
        $this->time = date('[Y-m-d H:i:s]');
    }

    /**
     * Sets the log file name.
     *
     * @param string $name The name of the log file (without extension).
     * @return self
     */
    public function setLogName(string $name): self
    {
        $this->logFile = Path::LOGS . $name . '.log';

        return $this;
    }

    /**
     * Writes a message to the log file.
     *
     * @param string $message The message to be logged.
     * @return void
     */
    public function write(string $message): void
    {
        // if file pointer doesn't exist, then open log file
        if (!is_resource($this->pointer)) {
            $this->open();
        }
        // define script name
        $scriptName = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);

        fwrite($this->pointer, "{$this->time} ({$scriptName}) {$message}" . PHP_EOL);

        if (!$this->fileExists) {
            chmod($this->logFile, 0644);
        }

        fclose($this->pointer);
    }

    /**
     * Opens the log file for writing.
     *
     * @return void
     */
    private function open(): void
    {
        $logFileDefault = Path::LOGS . 'log_' . date('Ymd') . '.log';
        // define log file from path method or use previously set default
        $this->logFile ??= $logFileDefault;

        if (!file_exists($this->logFile)) {
            $this->fileExists = false;
            mkdir(Path::LOGS, 0777, true);
        }

        $this->pointer = fopen($this->logFile, 'a') or exit("Can't open {$this->logFile}!");
    }
}
