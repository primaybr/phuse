<?php

declare(strict_types=1);

namespace Core;

use Core\Folder\Path as Path;

class Log
{
    private string $logFile;
    private $pointer = null;
    private string $time;
    private bool $fileExists = true;

    public function __construct()
    {
        $this->time = date('[Y-m-d H:i:s]');
    }

    // set log file name
    public function setLogName(string $name): self
    {
        $this->logFile = Path::LOGS . $name . '.log';

        return $this;
    }

    // write message to the log file
    public function write(string $message): void
    {
        // if file pointer doesn't exist, then open log file
        if (!is_resource($this->pointer)) {
            $this->open();
        }
        // define script name
        $scriptName = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);

        fwrite($this->pointer, "{$this->time} ({$script_name}) {$message}" . PHP_EOL);

        if (!$this->fileExists) {
            chmod($this->logFile, 0644);
        }

        fclose($this->pointer);
    }

    private function open(): void
    {
        $logFileDefault = Path::LOGS . 'log_' . date('Ymd') . '.log';
        // define log file from path method or use previously set default
        $this->logFile ??= $logFileDefault;

        if (!fileExists($this->logFile)) {
            $this->fileExists = false;
        }

        $this->pointer = fopen($this->logFile, 'a') or exit("Can't open {$this->logFile}!");
    }
}
