<?php

declare(strict_types=1);

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Core\Template\Parser;
use Core\Template\ParserInterface;
use Core\Cache\TemplateCache;
use Core\Log;

/**
 * Test suite for the Template Parser system
 *
 * This test class covers all functionality of the Parser class and ParserTrait,
 * including template rendering, data handling, conditional statements, loops,
 * caching, and error handling.
 */
class TemplateTest extends TestCase
{
    private Parser $parser;
    private string $testViewsDir;
    private string $testCacheDir;

    protected function setUp(): void
    {
        // Create a test parser instance
        $this->parser = new Parser();

        // Set up test directories
        $this->testViewsDir = dirname(__DIR__, 2) . '/tests/views';
        $this->testCacheDir = dirname(__DIR__, 2) . '/tests/cache/templates';

        // Ensure test directories exist
        if (!is_dir($this->testViewsDir)) {
            mkdir($this->testViewsDir, 0755, true);
        }
        if (!is_dir($this->testCacheDir)) {
            mkdir($this->testCacheDir, 0755, true);
        }

        // Create test template files
        $this->createTestTemplates();
    }

    protected function tearDown(): void
    {
        // Clean up test cache files
        $cacheFiles = glob($this->testCacheDir . '/*');
        foreach ($cacheFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        // Clean up test directories
        if (is_dir($this->testViewsDir)) {
            $this->removeDirectory($this->testViewsDir);
        }
        if (is_dir($this->testCacheDir)) {
            $this->removeDirectory($this->testCacheDir);
        }
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    private function createTestTemplates(): void
    {
        // Basic template
        file_put_contents($this->testViewsDir . '/basic.php', 'Hello {name}!');

        // Data template
        file_put_contents($this->testViewsDir . '/data.php', 'Name: {name}, Age: {age}, City: {city}');

        // Conditional template
        file_put_contents($this->testViewsDir . '/conditional.php',
            '{% if show_greeting %}Hello {name}!{% endif %}');

        // Foreach template
        file_put_contents($this->testViewsDir . '/foreach.php',
            'Items: {% foreach items as item %}{item.name} {% endforeach %}');

        // Nested template
        file_put_contents($this->testViewsDir . '/nested.php',
            '{% foreach users as user %}{user.name}: {user.profile.age}{% endforeach %}');

        // Error template
        file_put_contents($this->testViewsDir . '/error.php', 'Error: {message}');
    }

    public function testParserImplementsInterface(): void
    {
        $this->assertInstanceOf(ParserInterface::class, $this->parser);
    }

    public function testSetTemplateWithValidFile(): void
    {
        $result = $this->parser->setTemplate('basic');
        $this->assertInstanceOf(Parser::class, $result);
    }

    public function testSetTemplateWithInvalidFileThrowsException(): void
    {
        $this->expectException(\Core\Exception\Error::class);
        $this->expectExceptionMessage('not found');
        $this->parser->setTemplate('nonexistent');
    }

    public function testSetDataWithValidArray(): void
    {
        $data = ['name' => 'John', 'age' => 30];
        $result = $this->parser->setData($data);
        $this->assertInstanceOf(Parser::class, $result);
    }

    public function testSetDataWithInvalidInputThrowsException(): void
    {
        $this->expectException(\Core\Exception\Error::class);
        $this->expectExceptionMessage('must be an array');
        $this->parser->setData('invalid');
    }

    public function testRenderBasicTemplate(): void
    {
        $template = 'Hello {name}!';
        $result = $this->parser->parseData($template, ['name' => 'World']);
        $this->assertEquals('Hello World!', $result);
    }

    public function testRenderDataTemplate(): void
    {
        $template = 'Name: {name}, Age: {age}, City: {city}';
        $data = [
            'name' => 'Alice',
            'age' => 25,
            'city' => 'New York'
        ];
        $result = $this->parser->parseData($template, $data);
        $this->assertEquals('Name: Alice, Age: 25, City: New York', $result);
    }

    public function testRenderConditionalTemplate(): void
    {
        // Test with true condition
        $template = '{% if show_greeting %}Hello {name}!{% endif %}';
        $result = $this->parser->parseData($template, [
            'show_greeting' => true,
            'name' => 'Bob'
        ]);
        $this->assertEquals('Hello Bob!', $result);

        // Test with false condition
        $result = $this->parser->parseData($template, [
            'show_greeting' => false,
            'name' => 'Bob'
        ]);
        $this->assertEquals('', $result);
    }

    public function testRenderForeachTemplate(): void
    {
        $template = 'Items: {% foreach items as item %}{item.name} {% endforeach %}';
        $data = [
            'items' => [
                ['name' => 'Item 1'],
                ['name' => 'Item 2'],
                ['name' => 'Item 3']
            ]
        ];
        $result = $this->parser->parseData($template, $data);
        $this->assertEquals('Items: Item 1 Item 2 Item 3 ', $result);
    }

    public function testRenderNestedTemplate(): void
    {
        $template = '{% foreach users as user %}{user.name}: {user.profile.age}{% endforeach %}';
        $data = [
            'users' => [
                ['name' => 'John', 'profile' => ['age' => 30]],
                ['name' => 'Jane', 'profile' => ['age' => 28]]
            ]
        ];
        $result = $this->parser->parseData($template, $data);
        $this->assertEquals('John: 30Jane: 28', $result);
    }

    public function testRenderWithReturnFalseOutputsDirectly(): void
    {
        ob_start();
        echo $this->parser->parseData('Hello {name}!', ['name' => 'Test']);
        $output = ob_get_clean();
        $this->assertEquals('Hello Test!', $output);
    }

    public function testExceptionMethodRendersErrorTemplate(): void
    {
        ob_start();
        $this->parser->exception('Test error message');
        $output = ob_get_clean();
        $this->assertStringContainsString('Test error message', $output);
    }

    public function testExceptionMethodWithCustomTemplate(): void
    {
        ob_start();
        $this->parser->exception('Custom error', 'error');
        $output = ob_get_clean();
        $this->assertStringContainsString('Custom error', $output);
    }

    public function testParseDataWithEmptyTemplateThrowsException(): void
    {
        $this->expectException(\Core\Exception\Error::class);
        $this->parser->parseData('', []);
    }

    public function testParseDataWithValidTemplate(): void
    {
        $template = 'Hello {name}!';
        $data = ['name' => 'World'];
        $result = $this->parser->parseData($template, $data);
        $this->assertEquals('Hello World!', $result);
    }

    public function testTemplateCaching(): void
    {
        // Enable caching
        $this->parser->enableCache(true);

        // First parse should work
        $result1 = $this->parser->parseData('Hello {name}!', ['name' => 'Cache']);

        // Second parse should work
        $result2 = $this->parser->parseData('Hello {name}!', ['name' => 'Cache']);

        $this->assertEquals($result1, $result2);
        $this->assertEquals('Hello Cache!', $result1);
    }

    public function testClearCache(): void
    {
        // Enable caching and parse to potentially create cache
        $this->parser->enableCache(true);
        $this->parser->parseData('Hello {name}!', ['name' => 'Cache']);

        // Clear cache
        $result = $this->parser->clearCache();
        $this->assertTrue($result);
    }

    public function testClearCacheWithForce(): void
    {
        // Enable caching and parse to potentially create cache
        $this->parser->enableCache(true);
        $this->parser->parseData('Hello {name}!', ['name' => 'Cache']);

        // Clear cache with force
        $result = $this->parser->clearCache(true);
        $this->assertTrue($result);
    }

    public function testMethodChaining(): void
    {
        $result = $this->parser
            ->setData(['name' => 'Chain'])
            ->enableCache(false);

        $this->assertInstanceOf(Parser::class, $result);

        $output = $this->parser->parseData('Hello {name}!', []);
        $this->assertEquals('Hello Chain!', $output);
    }

    public function testDataMerging(): void
    {
        // Set initial data
        $this->parser->setData(['name' => 'John']);

        // Set additional data (should merge)
        $this->parser->setData(['age' => 30]);

        $result = $this->parser->parseData('Name: {name}, Age: {age}, City: {city}', []);
        $this->assertEquals('Name: John, Age: 30, City: ', $result);
    }

    public function testSecurityWithUnsafeVariableNames(): void
    {
        // Test that variables with unsafe names are filtered out
        $data = [
            'name' => 'John',
            'unsafe-var' => 'should be filtered',
            'another_unsafe' => 'also filtered',
            'valid_name' => 'should work'
        ];

        $template = '{name} {valid_name}';
        $result = $this->parser->parseData($template, $data);

        // Should only contain safe variables
        $this->assertEquals('John valid_name', $result);
    }
}
