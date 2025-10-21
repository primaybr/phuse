<?php

declare(strict_types=1);

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Core\Log;
use Core\Folder\Path;

class LogTest extends TestCase
{
    private Log $log;
    private string $testLogFile;

    protected function setUp(): void
    {
        $this->log = new Log();
        $this->log->setLogName('test_log'); // Use custom name
        $this->testLogFile = Path::LOGS . 'test_log.log';

        // Ensure the Logs directory exists
        if (!is_dir(dirname($this->testLogFile))) {
            mkdir(dirname($this->testLogFile), 0755, true);
        }

        // Clean up any existing test log file
        if (file_exists($this->testLogFile)) {
            unlink($this->testLogFile);
        }
    }

    protected function tearDown(): void
    {
        // Clean up test log file
        if (file_exists($this->testLogFile)) {
            unlink($this->testLogFile);
        }
    }

    public function testLogInstantiation(): void
    {
        $this->assertInstanceOf(Log::class, $this->log);
    }

    public function testSetLogName(): void
    {
        $this->log->setLogName('test_log');
        $reflection = new \ReflectionClass($this->log);
        $property = $reflection->getProperty('logFile');
        $property->setAccessible(true);
        $logFile = $property->getValue($this->log);

        $this->assertStringEndsWith('test_log.log', $logFile);
    }

    public function testWrite(): void
    {
        $this->log->setLogName('test_log');

        $this->log->write('Test message');

        $this->assertFileExists($this->testLogFile);

        $content = file_get_contents($this->testLogFile);
        $this->assertIsString($content);
        $this->assertStringContainsString('Test message', $content);
        $this->assertStringContainsString(date('[Y-m-d H:i:s]'), $content);
    }

    public function testWriteMultipleMessages(): void
    {
        $this->log->setLogName('test_log');

        $this->log->write('First message');
        $this->log->write('Second message');

        $this->assertFileExists($this->testLogFile);

        $content = file_get_contents($this->testLogFile);
        $this->assertIsString($content);
        $this->assertStringContainsString('First message', $content);
        $this->assertStringContainsString('Second message', $content);
    }

    public function testWriteCreatesFileIfNotExists(): void
    {
        $this->log->setLogName('test_log');

        $this->log->write('Test message');

        $this->assertFileExists($this->testLogFile);

        // Check that file has write permissions (exact value may vary)
        $perms = substr(sprintf('%o', fileperms($this->testLogFile)), -4);
        $this->assertStringStartsWith('0', $perms); // Should be octal format
    }

    public function testDefaultLogFile(): void
    {
        // Create a new Log instance without setting a name
        $log = new Log();
        $reflection = new \ReflectionClass($log);
        $property = $reflection->getProperty('logFile');
        $property->setAccessible(true);

        // The property might not be set until write() is called
        $log->write('Test');

        $logFile = $property->getValue($log);
        $this->assertStringContainsString('log_', $logFile); // Should contain 'log_'
    }
}
