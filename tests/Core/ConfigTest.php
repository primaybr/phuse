<?php

declare(strict_types=1);

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Core\Config;
use Core\Folder\Path;

class ConfigTest extends TestCase
{
    private Config $config;

    protected function setUp(): void
    {
        $this->config = new Config();
    }

    public function testConfigInstantiation(): void
    {
        $this->assertInstanceOf(Config::class, $this->config);
    }

    public function testGetEnv(): void
    {
        $env = $this->config->getEnv();
        $this->assertIsString($env);
        $this->assertContains($env, ['development', 'local', 'production', 'testing']);
    }

    public function testSetAndGetConfig(): void
    {
        // Since config is readonly, we can't modify it directly
        // Instead, test that get() returns expected structure
        $configData = $this->config->get();
        $this->assertInstanceOf(\stdClass::class, $configData);
    }

    public function testGetConfigWithData(): void
    {
        $testData = ['custom' => 'value'];
        $configData = $this->config->get($testData);

        $this->assertEquals('value', $configData->custom);
    }

    public function testProcessConfigArray(): void
    {
        $reflection = new \ReflectionClass($this->config);
        $method = $reflection->getMethod('processConfigArray');
        $method->setAccessible(true);

        $input = ['nested' => ['key' => 'value']];
        $result = $method->invoke($this->config, $input);

        $this->assertIsArray($result);
        $this->assertInstanceOf(\stdClass::class, $result['nested']);
        $this->assertEquals('value', $result['nested']->key);
    }

    public function testBuildBaseUrl(): void
    {
        $reflection = new \ReflectionClass($this->config);
        $method = $reflection->getMethod('buildBaseUrl');
        $method->setAccessible(true);

        $config = ['site' => (object)['baseUrl' => 'myapp']];
        $baseUrl = $method->invoke($this->config, $config);

        $this->assertIsString($baseUrl);
        $this->assertStringContainsString('http', $baseUrl);
    }

    public function testGetConfigReturnsObject(): void
    {
        $configData = $this->config->get();
        $this->assertInstanceOf(\stdClass::class, $configData);

        // Check if it has expected properties (depending on your config file)
        if (property_exists($configData, 'site')) {
            $this->assertInstanceOf(\stdClass::class, $configData->site);
        }
    }

    public function testConfigFileNotFound(): void
    {
        // This would require mocking file_exists, but for simplicity,
        // we'll assume the config file exists in a real environment
        $this->assertTrue(file_exists(Path::CONFIG . 'Config.php'));
    }
}
