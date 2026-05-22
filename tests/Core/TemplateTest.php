<?php

declare(strict_types=1);

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Core\Template\Parser;
use Core\Template\ParserInterface;
use Core\Cache\TemplateCache;
use Core\Log;

/**
 * Test suite for the PHUSE Template Parser (v1.3.0 double-brace syntax)
 *
 * Covers:
 *  - {{variable}} double-brace output
 *  - {{nested.property}} dot-notation
 *  - {{var|filter}} and chained filters
 *  - {!! raw !!} unescaped output
 *  - {# comment #} stripping
 *  - @{{escaped}} literal output
 *  - {% if %} / {% else %} / {% endif %} conditionals
 *  - {% foreach %} loops with nested data
 *  - {% for %} numeric range loops
 *  - Inline CSS and JavaScript safety (single { } pass through unchanged)
 *  - Caching, method chaining, error handling
 */
class TemplateTest extends TestCase
{
    private Parser $parser;
    private string $testViewsDir;
    private string $testCacheDir;

    protected function setUp(): void
    {
        $this->parser = new Parser();

        $this->testViewsDir = dirname(__DIR__, 2) . '/tests/views';
        $this->testCacheDir = dirname(__DIR__, 2) . '/tests/cache/templates';

        if (!is_dir($this->testViewsDir)) {
            mkdir($this->testViewsDir, 0755, true);
        }
        if (!is_dir($this->testCacheDir)) {
            mkdir($this->testCacheDir, 0755, true);
        }

        $this->createTestTemplates();
    }

    protected function tearDown(): void
    {
        $cacheFiles = glob($this->testCacheDir . '/*');
        foreach ($cacheFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

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
        // All templates now use {{variable}} double-brace syntax
        file_put_contents($this->testViewsDir . '/basic.php',
            'Hello {{name}}!');

        file_put_contents($this->testViewsDir . '/data.php',
            'Name: {{name}}, Age: {{age}}, City: {{city}}');

        file_put_contents($this->testViewsDir . '/conditional.php',
            '{% if show_greeting %}Hello {{name}}!{% endif %}');

        file_put_contents($this->testViewsDir . '/foreach.php',
            'Items: {% foreach items as item %}{{item.name}} {% endforeach %}');

        file_put_contents($this->testViewsDir . '/nested.php',
            '{% foreach users as user %}{{user.name}}: {{user.profile.age}}{% endforeach %}');

        file_put_contents($this->testViewsDir . '/error.php',
            'Error: {{message}}');

        // Inline CSS / JS safety template
        file_put_contents($this->testViewsDir . '/css_js_safe.php',
            '<style>.btn { color: red; font-size: 1rem; }</style>' .
            '<p>{{greeting}}</p>' .
            '<script>var cfg = { debug: true }; if (cfg.debug) { console.log("ok"); }</script>');

        // Comment stripping template
        file_put_contents($this->testViewsDir . '/comments.php',
            '{# This comment is stripped #}Hello {{name}}!{# Another comment #}');

        // Raw output template
        file_put_contents($this->testViewsDir . '/raw_output.php',
            'Safe: {{content}} | Raw: {!! content !!}');

        // Escaped syntax template
        file_put_contents($this->testViewsDir . '/escaped.php',
            'Literal: @{{variable}} | Parsed: {{variable}}');

        // For loop template
        file_put_contents($this->testViewsDir . '/for_loop.php',
            '{% for i in 1..3 %}{{i}} {% endfor %}');

        // Filter template
        file_put_contents($this->testViewsDir . '/filters.php',
            '{{name|upper}} | {{name|lower}} | {{name|capitalize}} | {{name|substr:0:3}}');
    }

    // -----------------------------------------------------------------------
    // Interface & constructor
    // -----------------------------------------------------------------------

    public function testParserImplementsInterface(): void
    {
        $this->assertInstanceOf(ParserInterface::class, $this->parser);
    }

    // -----------------------------------------------------------------------
    // setTemplate / setData
    // -----------------------------------------------------------------------

    public function testSetTemplateWithValidFile(): void
    {
        // Use an existing App/Views template (default/welcome.php created by the framework)
        $result = $this->parser->setTemplate('default/welcome');
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
        $data   = ['name' => 'John', 'age' => 30];
        $result = $this->parser->setData($data);
        $this->assertInstanceOf(Parser::class, $result);
    }

    public function testSetDataWithInvalidInputThrowsException(): void
    {
        $this->expectException(\Core\Exception\Error::class);
        $this->expectExceptionMessage('must be an array');
        $this->parser->setData('invalid');
    }

    // -----------------------------------------------------------------------
    // Basic variable replacement  {{variable}}
    // -----------------------------------------------------------------------

    public function testRenderBasicTemplate(): void
    {
        $result = $this->parser->parseData('Hello {{name}}!', ['name' => 'World']);
        $this->assertEquals('Hello World!', $result);
    }

    public function testRenderDataTemplate(): void
    {
        $template = 'Name: {{name}}, Age: {{age}}, City: {{city}}';
        $data     = ['name' => 'Alice', 'age' => 25, 'city' => 'New York'];
        $result   = $this->parser->parseData($template, $data);
        $this->assertEquals('Name: Alice, Age: 25, City: New York', $result);
    }

    public function testUnknownVariablesAreLeftIntact(): void
    {
        // {{unknown}} with no matching data key must stay as-is (not stripped)
        $result = $this->parser->parseData('Hello {{name}} {{unknown}}!', ['name' => 'World']);
        $this->assertStringContainsString('World', $result);
        $this->assertStringContainsString('{{unknown}}', $result);
    }

    // -----------------------------------------------------------------------
    // Inline CSS & JS safety — single { } must pass through unchanged
    // -----------------------------------------------------------------------

    public function testInlineCssIsPreserved(): void
    {
        // CSS is minified by the HTML post-processor, but rules must not be corrupted.
        // We verify that selectors and values are present in whatever form (minified or not).
        $template = '<style>.btn { color: red; } .hero { background: #fff; }</style>{{title}}';
        $result   = $this->parser->parseData($template, ['title' => 'Test']);

        $this->assertStringContainsString('.btn', $result);   // selector preserved
        $this->assertStringContainsString('color', $result);  // property preserved
        $this->assertStringContainsString('red', $result);    // value preserved
        $this->assertStringContainsString('.hero', $result);  // selector preserved
        $this->assertStringContainsString('#fff', $result);   // value preserved
        $this->assertStringContainsString('Test', $result);
    }

    public function testInlineJavaScriptIsPreserved(): void
    {
        // JS is minified by the HTML post-processor, but code must not be corrupted.
        $template = '<script>var cfg = { debug: true }; if (cfg.debug) { console.log("ok"); }</script>{{greeting}}';
        $result   = $this->parser->parseData($template, ['greeting' => 'Hello']);

        $this->assertStringContainsString('cfg', $result);          // variable name preserved
        $this->assertStringContainsString('debug', $result);         // property preserved
        $this->assertStringContainsString('console.log', $result);   // function call preserved
        $this->assertStringContainsString('Hello', $result);
    }

    public function testJsVariablesInjectedInsideScriptTags(): void
    {
        // Variable values must be injected into <script> blocks.
        // URL must survive the JS minifier (// inside a string is NOT a comment).
        $template = '<script>var url = "{{apiUrl}}"; var id = {{userId}};</script>';
        $result   = $this->parser->parseData($template, ['apiUrl' => 'https://api.example.com', 'userId' => 42]);

        $this->assertStringContainsString('api.example.com', $result); // URL injected and intact
        $this->assertStringContainsString('42', $result);               // numeric value injected
    }

    public function testInlineStyleAttributeWithVariable(): void
    {
        // Inline style attributes are also minified (spaces around : and ; removed).
        $template = '<div style="color: {{color}}; font-size: {{size}}px;">Hello</div>';
        $result   = $this->parser->parseData($template, ['color' => 'red', 'size' => '16']);

        $this->assertStringContainsString('color', $result);   // property present
        $this->assertStringContainsString('red', $result);     // value injected
        $this->assertStringContainsString('16', $result);      // size value injected
        $this->assertStringContainsString('Hello', $result);
    }

    public function testCssCurlyBracesInStyleBlockDoNotTriggerParsing(): void
    {
        // Verifies single { } do NOT corrupt multi-rule CSS.
        // After HTML minification, spaces around { } are removed but rules remain intact.
        $template = '<style>h1 { color: blue; } p { margin: 0; }</style><h1>{{title}}</h1>';
        $result   = $this->parser->parseData($template, ['title' => 'Test']);

        $this->assertStringContainsString('h1', $result);     // h1 selector preserved
        $this->assertStringContainsString('color', $result);  // property preserved
        $this->assertStringContainsString('blue', $result);   // value preserved
        $this->assertStringContainsString('margin', $result); // p rule property preserved
        $this->assertEquals(1, substr_count($result, 'Test'));
    }

    // -----------------------------------------------------------------------
    // {# comment #} stripping
    // -----------------------------------------------------------------------

    public function testCommentsAreStripped(): void
    {
        $result = $this->parser->parseData('{# hidden comment #}Hello {{name}}!', ['name' => 'World']);
        $this->assertEquals('Hello World!', $result);
        $this->assertStringNotContainsString('hidden comment', $result);
    }

    public function testMultilineCommentsAreStripped(): void
    {
        $template = '{# This is a
multiline comment #}Hello!';
        $result = $this->parser->parseData($template, []);
        $this->assertEquals('Hello!', $result);
    }

    // -----------------------------------------------------------------------
    // {!! raw !!} unescaped output
    // -----------------------------------------------------------------------

    public function testRawOutputIsNotEscaped(): void
    {
        $html     = '<strong>Bold</strong>';
        $template = '{!! htmlContent !!}';
        $result   = $this->parser->parseData($template, ['htmlContent' => $html]);
        $this->assertStringContainsString('<strong>Bold</strong>', $result);
    }

    public function testRawOutputWithNestedProperty(): void
    {
        $html     = '<em>Italic</em>';
        $template = '{!! user.bio !!}';
        $result   = $this->parser->parseData($template, ['user' => ['bio' => $html]]);
        $this->assertStringContainsString('<em>Italic</em>', $result);
    }

    // -----------------------------------------------------------------------
    // @{{escaped}} literal output
    // -----------------------------------------------------------------------

    public function testEscapedTagOutputsLiteralDoubleBraces(): void
    {
        $result = $this->parser->parseData('Docs: @{{variable}}', ['variable' => 'ignored']);
        $this->assertStringContainsString('{{variable}}', $result);
        $this->assertStringNotContainsString('ignored', $result);
    }

    // -----------------------------------------------------------------------
    // Filters  {{var|filter}}
    // -----------------------------------------------------------------------

    public function testUpperFilter(): void
    {
        $result = $this->parser->parseData('{{name|upper}}', ['name' => 'hello']);
        $this->assertEquals('HELLO', $result);
    }

    public function testLowerFilter(): void
    {
        $result = $this->parser->parseData('{{name|lower}}', ['name' => 'HELLO']);
        $this->assertEquals('hello', $result);
    }

    public function testCapitalizeFilter(): void
    {
        $result = $this->parser->parseData('{{name|capitalize}}', ['name' => 'hello world']);
        $this->assertEquals('Hello World', $result);
    }

    public function testSubstrFilter(): void
    {
        $result = $this->parser->parseData('{{name|substr:0:3}}', ['name' => 'Hello']);
        $this->assertEquals('Hel', $result);
    }

    public function testLengthFilter(): void
    {
        $result = $this->parser->parseData('{{items|length}}', ['items' => ['a', 'b', 'c']]);
        $this->assertEquals('3', $result);
    }

    public function testDateFilter(): void
    {
        $result = $this->parser->parseData("{{ts|date:'Y'}}", ['ts' => mktime(0, 0, 0, 1, 1, 2024)]);
        $this->assertEquals('2024', $result);
    }

    public function testChainedFilters(): void
    {
        // substr first letter, then upper
        $result = $this->parser->parseData('{{name|substr:0:1|upper}}', ['name' => 'hello']);
        $this->assertEquals('H', $result);
    }

    public function testStarsFilter(): void
    {
        $result = $this->parser->parseData('{{score|stars}}', ['score' => 3]);
        $this->assertEquals('★★★☆☆', $result);
    }

    // -----------------------------------------------------------------------
    // Nested property  {{user.profile.age}}
    // -----------------------------------------------------------------------

    public function testNestedPropertyAccess(): void
    {
        $result = $this->parser->parseData('{{user.name}}', [
            'user' => ['name' => 'Alice']
        ]);
        $this->assertEquals('Alice', $result);
    }

    public function testDeepNestedPropertyAccess(): void
    {
        $result = $this->parser->parseData('{{user.profile.city}}', [
            'user' => ['profile' => ['city' => 'London']]
        ]);
        $this->assertEquals('London', $result);
    }

    // -----------------------------------------------------------------------
    // Conditionals  {% if %} / {% else %} / {% endif %}
    // -----------------------------------------------------------------------

    public function testRenderConditionalTrueBlock(): void
    {
        $template = '{% if show_greeting %}Hello {{name}}!{% endif %}';
        $result   = $this->parser->parseData($template, [
            'show_greeting' => true,
            'name'          => 'Bob',
        ]);
        $this->assertEquals('Hello Bob!', $result);
    }

    public function testRenderConditionalFalseBlock(): void
    {
        $template = '{% if show_greeting %}Hello {{name}}!{% endif %}';
        $result   = $this->parser->parseData($template, [
            'show_greeting' => false,
            'name'          => 'Bob',
        ]);
        $this->assertEquals('', $result);
    }

    public function testRenderConditionalElseBranch(): void
    {
        $template = '{% if logged_in %}Welcome!{% else %}Please login.{% endif %}';

        $logged   = $this->parser->parseData($template, ['logged_in' => true]);
        $guest    = $this->parser->parseData($template, ['logged_in' => false]);

        $this->assertStringContainsString('Welcome!', $logged);
        $this->assertStringContainsString('Please login.', $guest);
    }

    // -----------------------------------------------------------------------
    // Foreach loops
    // -----------------------------------------------------------------------

    public function testRenderForeachTemplate(): void
    {
        $template = 'Items: {% foreach items as item %}{{item.name}} {% endforeach %}';
        $data     = [
            'items' => [
                ['name' => 'Item 1'],
                ['name' => 'Item 2'],
                ['name' => 'Item 3'],
            ],
        ];
        $result = $this->parser->parseData($template, $data);
        // The HTML minifier trims trailing whitespace from the final output
        $this->assertEquals('Items: Item 1 Item 2 Item 3', trim($result));
    }

    public function testRenderNestedDataInForeach(): void
    {
        $template = '{% foreach users as user %}{{user.name}}: {{user.profile.age}}{% endforeach %}';
        $data     = [
            'users' => [
                ['name' => 'John', 'profile' => ['age' => 30]],
                ['name' => 'Jane', 'profile' => ['age' => 28]],
            ],
        ];
        $result = $this->parser->parseData($template, $data);
        $this->assertEquals('John: 30Jane: 28', $result);
    }

    public function testForeachWithSimpleScalars(): void
    {
        $template = '{% foreach tags as tag %}#{{tag}} {% endforeach %}';
        $result   = $this->parser->parseData($template, ['tags' => ['php', 'twig', 'blade']]);
        // The HTML minifier trims trailing whitespace from the final output
        $this->assertEquals('#php #twig #blade', trim($result));
    }

    // -----------------------------------------------------------------------
    // For numeric range loop
    // -----------------------------------------------------------------------

    public function testNumericForLoop(): void
    {
        $template = '{% for i in 1..3 %}{{i}} {% endfor %}';
        $result   = $this->parser->parseData($template, []);
        // The HTML minifier trims trailing whitespace from the final output
        $this->assertEquals('1 2 3', trim($result));
    }

    // -----------------------------------------------------------------------
    // Filters inside foreach loops
    // -----------------------------------------------------------------------

    public function testFilterInsideForeach(): void
    {
        $template = '{% foreach users as user %}{{user.name|upper}} {% endforeach %}';
        $result   = $this->parser->parseData($template, [
            'users' => [['name' => 'alice'], ['name' => 'bob']],
        ]);
        $this->assertStringContainsString('ALICE', $result);
        $this->assertStringContainsString('BOB', $result);
    }

    // -----------------------------------------------------------------------
    // Error handling
    // -----------------------------------------------------------------------

    public function testParseDataWithEmptyTemplateThrowsException(): void
    {
        $this->expectException(\Core\Exception\Error::class);
        $this->parser->parseData('', []);
    }

    public function testExceptionMethodThrowsError(): void
    {
        $this->expectException(\Core\Exception\Error::class);
        $this->parser->exception('Test error message');
    }

    // -----------------------------------------------------------------------
    // Caching
    // -----------------------------------------------------------------------

    public function testTemplateCaching(): void
    {
        $this->parser->enableCache(true);

        $result1 = $this->parser->parseData('Hello {{name}}!', ['name' => 'Cache']);
        $result2 = $this->parser->parseData('Hello {{name}}!', ['name' => 'Cache']);

        $this->assertEquals($result1, $result2);
        $this->assertEquals('Hello Cache!', $result1);
    }

    public function testClearCache(): void
    {
        // clearCache() without $force=true returns false unless autoClearInDevelopment is on.
        // This is by design — use clearCache(true) to force-clear.
        $this->parser->enableCache(true);
        $this->parser->parseData('Hello {{name}}!', ['name' => 'Cache']);
        $result = $this->parser->clearCache();
        $this->assertFalse($result);
    }

    public function testClearCacheWithForce(): void
    {
        $this->parser->enableCache(true);
        $this->parser->parseData('Hello {{name}}!', ['name' => 'Cache']);
        $result = $this->parser->clearCache(true);
        $this->assertTrue($result);
    }

    // -----------------------------------------------------------------------
    // Method chaining & data merging
    // -----------------------------------------------------------------------

    public function testMethodChaining(): void
    {
        $result = $this->parser
            ->setData(['name' => 'Chain'])
            ->enableCache(false);

        $this->assertInstanceOf(Parser::class, $result);

        $output = $this->parser->parseData('Hello {{name}}!', []);
        $this->assertEquals('Hello Chain!', $output);
    }

    public function testDataMerging(): void
    {
        $this->parser->setData(['name' => 'John']);
        $this->parser->setData(['age'  => 30]);

        $result = $this->parser->parseData('Name: {{name}}, Age: {{age}}', []);
        $this->assertEquals('Name: John, Age: 30', $result);
    }

    // -----------------------------------------------------------------------
    // Output buffering round-trip
    // -----------------------------------------------------------------------

    public function testParseDataOutputsCorrectly(): void
    {
        ob_start();
        echo $this->parser->parseData('Hello {{name}}!', ['name' => 'Test']);
        $output = ob_get_clean();
        $this->assertEquals('Hello Test!', $output);
    }
}
