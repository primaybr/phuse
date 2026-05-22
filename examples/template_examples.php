<?php

declare(strict_types=1);

/**
 * PHUSE FRAMEWORK — TEMPLATE SYSTEM EXAMPLES (v1.3.0)
 *
 * Demonstrates the double-brace {{variable}} syntax introduced in v1.3.0.
 * Single { } are no longer parsed, so inline CSS and JavaScript are safe.
 *
 * Quick syntax reference:
 *   {{variable}}                     Output a variable
 *   {{user.profile.age}}             Dot-notation nested access
 *   {{name|upper}}                   Filter
 *   {{name|substr:0:1|upper}}        Chained filters
 *   {!! htmlContent !!}              Raw / unescaped HTML
 *   {# This is a comment #}          Template comment (stripped)
 *   @{{variable}}                    Escaped — outputs literal {{variable}}
 *   {% if condition %}…{% endif %}   Conditional block
 *   {% foreach items as item %}…{% endforeach %}  Loop
 *   {% for i in 1..10 %}…{% endfor %}             Numeric range loop
 *
 * @package Examples
 * @author  Prima Yoga
 */

namespace Examples;

use Core\Template\Parser;
use Core\Cache\TemplateCache;

// ---------------------------------------------------------------------------
// EXAMPLE 1 — Basic Variable Replacement
// ---------------------------------------------------------------------------
/**
 * The simplest usage: pass scalar values and reference them with {{variable}}.
 *
 * Template syntax (was):  Hello {name}!
 * Template syntax (now):  Hello {{name}}!
 */
function example_basic_variables(): void
{
    $parser = new Parser();

    $template = 'Hello {{name}}! Welcome to {{framework}}.';

    $result = $parser->parseData($template, [
        'name'      => 'John Doe',
        'framework' => 'PHUSE',
    ]);

    echo "=== 1. Basic Variables ===\n";
    echo $result . "\n";
    // Output: Hello John Doe! Welcome to PHUSE.
    echo "\n";
}

// ---------------------------------------------------------------------------
// EXAMPLE 2 — Nested / Dot-Notation Access
// ---------------------------------------------------------------------------
/**
 * Access nested arrays and objects with dot notation inside {{…}}.
 *
 *   {{user.name}}               — one level deep
 *   {{user.profile.city}}       — two levels deep
 *   {{user.address.geo.lat}}    — unlimited depth
 */
function example_nested_access(): void
{
    $parser = new Parser();

    $template = 'User: {{user.name}} | City: {{user.profile.city}} | Role: {{user.role|capitalize}}';

    $result = $parser->parseData($template, [
        'user' => [
            'name'    => 'alice',
            'role'    => 'admin',
            'profile' => ['city' => 'Jakarta'],
        ],
    ]);

    echo "=== 2. Nested Access ===\n";
    echo $result . "\n";
    // Output: User: alice | City: Jakarta | Role: Admin
    echo "\n";
}

// ---------------------------------------------------------------------------
// EXAMPLE 3 — Filters
// ---------------------------------------------------------------------------
/**
 * Pipe filters work exactly like Twig:
 *
 *   {{name|upper}}                   JOHN
 *   {{name|lower}}                   john
 *   {{name|capitalize}}              John Doe
 *   {{name|substr:0:1|upper}}        J  (chained)
 *   {{items|length}}                 3
 *   {{date|date:'M d, Y'}}           Jan 01, 2024
 *   {{score|stars}}                  ★★★☆☆
 *   {{score|round}}                  4
 */
function example_filters(): void
{
    $parser = new Parser();

    $tests = [
        ['{{name|upper}}',            ['name'  => 'hello world']],
        ['{{name|capitalize}}',        ['name'  => 'hello world']],
        ['{{name|substr:0:5|upper}}',  ['name'  => 'hello world']],
        ['{{items|length}}',           ['items' => ['a','b','c']]],
        ['{{score|stars}}',            ['score' => 4]],
        ["{{ts|date:'Y-m-d'}}",        ['ts'    => mktime(0,0,0,1,1,2024)]],
    ];

    echo "=== 3. Filters ===\n";
    foreach ($tests as [$tpl, $data]) {
        echo sprintf("  %-40s => %s\n", $tpl, $parser->parseData($tpl, $data));
    }
    echo "\n";
}

// ---------------------------------------------------------------------------
// EXAMPLE 4 — Raw HTML Output  {!! var !!}
// ---------------------------------------------------------------------------
/**
 * Use {!! var !!} to output trusted HTML without escaping.
 * Ideal for rich-text content stored in a database.
 *
 * NOTE: Never use {!! !!} with untrusted user input.
 */
function example_raw_output(): void
{
    $parser = new Parser();

    $template = '<p>Safe:  {{body}}</p><p>Raw: {!! body !!}</p>';

    $result = $parser->parseData($template, [
        'body' => '<strong>Bold</strong> & <em>Italic</em>',
    ]);

    echo "=== 4. Raw HTML Output ===\n";
    echo $result . "\n";
    // {{body}} would show the HTML as text; {!! body !!} renders the tags.
    echo "\n";
}

// ---------------------------------------------------------------------------
// EXAMPLE 5 — Template Comments  {# … #}
// ---------------------------------------------------------------------------
/**
 * Comments are stripped entirely — they never appear in the rendered output,
 * not even as HTML comments.
 */
function example_comments(): void
{
    $parser = new Parser();

    $template = <<<'TPL'
{# Page title section — keep in sync with SEO meta #}
<h1>{{title}}</h1>

{# TODO: add breadcrumb nav here #}
<p>{{description}}</p>
TPL;

    $result = $parser->parseData($template, [
        'title'       => 'PHUSE Framework',
        'description' => 'PHP Easy To Use.',
    ]);

    echo "=== 5. Comments ===\n";
    echo $result . "\n";
    // Comments are gone; only <h1> and <p> remain.
    echo "\n";
}

// ---------------------------------------------------------------------------
// EXAMPLE 6 — Escaped Output  @{{variable}}
// ---------------------------------------------------------------------------
/**
 * Use @{{variable}} to output the literal text {{variable}} — useful when
 * writing documentation or showing template syntax inside a template.
 * Mirrors Laravel Blade's @{{ }} mechanism.
 */
function example_escaped_output(): void
{
    $parser = new Parser();

    $template = 'Syntax: @{{name}} renders the value of <em>name</em>. Current value: {{name}}';

    $result = $parser->parseData($template, ['name' => 'Alice']);

    echo "=== 6. Escaped Output ===\n";
    echo $result . "\n";
    // Output: Syntax: {{name}} renders the value of name. Current value: Alice
    echo "\n";
}

// ---------------------------------------------------------------------------
// EXAMPLE 7 — Conditional Statements  {% if %}
// ---------------------------------------------------------------------------
/**
 * Control flow uses {% %} tags — identical to Twig.
 *
 *   {% if condition %}…{% endif %}
 *   {% if condition %}…{% else %}…{% endif %}
 *   Supports: ==, !=, >, <, >=, <=, &&, ||, not
 */
function example_conditionals(): void
{
    $parser = new Parser();

    $template = <<<'TPL'
{% if logged_in %}
  Welcome back, {{username}}!
  {% if role == 'admin' %}
    <span>You have admin access.</span>
  {% endif %}
{% else %}
  Please <a href="/login">login</a>.
{% endif %}
TPL;

    $admin = $parser->parseData($template, [
        'logged_in' => true,
        'username'  => 'johndoe',
        'role'      => 'admin',
    ]);

    $guest = $parser->parseData($template, [
        'logged_in' => false,
        'username'  => '',
        'role'      => 'guest',
    ]);

    echo "=== 7. Conditionals ===\n";
    echo "Admin  => " . trim(strip_tags($admin)) . "\n";
    echo "Guest  => " . trim(strip_tags($guest)) . "\n";
    echo "\n";
}

// ---------------------------------------------------------------------------
// EXAMPLE 8 — Foreach Loops  {% foreach %}
// ---------------------------------------------------------------------------
/**
 *   {% foreach items as item %}
 *     {{item.name}} — {{item.price}}
 *   {% endforeach %}
 *
 * Filters, nested foreach, and if/else blocks are all supported inside loops.
 */
function example_foreach_loops(): void
{
    $parser = new Parser();

    $template = <<<'TPL'
{% foreach products as product %}
  {{product.name|capitalize}} — ${{product.price}}
  {% if product.in_stock %}[In Stock]{% else %}[Out of Stock]{% endif %}
{% endforeach %}
TPL;

    $result = $parser->parseData($template, [
        'products' => [
            ['name' => 'laptop',   'price' => 999,  'in_stock' => true],
            ['name' => 'mouse',    'price' => 25,   'in_stock' => true],
            ['name' => 'headset',  'price' => 149,  'in_stock' => false],
        ],
    ]);

    echo "=== 8. Foreach Loops ===\n";
    echo trim($result) . "\n";
    echo "\n";
}

// ---------------------------------------------------------------------------
// EXAMPLE 9 — Numeric For Loop  {% for %}
// ---------------------------------------------------------------------------
/**
 *   {% for i in 1..5 %}{{i}} {% endfor %}
 *   → 1 2 3 4 5
 */
function example_for_loop(): void
{
    $parser = new Parser();

    $result = $parser->parseData(
        '<select>{% for year in 2020..2025 %}<option>{{year}}</option>{% endfor %}</select>',
        []
    );

    echo "=== 9. For Loop ===\n";
    echo $result . "\n";
    echo "\n";
}

// ---------------------------------------------------------------------------
// EXAMPLE 10 — Inline CSS & JavaScript Safety
// ---------------------------------------------------------------------------
/**
 * This is the primary motivation for the v1.3.0 syntax change.
 *
 * With the old {variable} syntax, CSS rules like  .btn { color: red; }
 * and JS objects like  var cfg = { debug: true };  would confuse the parser.
 *
 * With the new {{variable}} syntax, single { } pass through completely
 * unchanged — only {{ }} triggers variable substitution.
 */
function example_inline_assets_safety(): void
{
    $parser = new Parser();

    $template = <<<'TPL'
<style>
  /* CSS curly braces are 100% safe */
  .hero {
    background: {{bgColor}};
    color: {{textColor}};
  }
  .card { border-radius: 0.5rem; }
</style>

<div class="hero">
  <h1>{{title}}</h1>
</div>

<script>
  // Plain JS objects — completely untouched
  var config = { debug: false, version: "1.0" };

  // Dynamic values from PHP via {{variable}}
  var apiUrl  = "{{apiUrl}}";
  var userId  = {{userId}};

  if (config.debug) { console.log("Debug on"); }
</script>
TPL;

    $result = $parser->parseData($template, [
        'bgColor'   => '#1e293b',
        'textColor' => '#f8fafc',
        'title'     => 'Hello from PHUSE',
        'apiUrl'    => 'https://api.example.com/v2',
        'userId'    => 42,
    ]);

    echo "=== 10. Inline CSS & JS Safety ===\n";

    // Verify CSS is intact
    assert(str_contains($result, '.card { border-radius: 0.5rem; }'), 'CSS rules preserved');
    assert(str_contains($result, 'var config = { debug: false, version: "1.0" }'), 'JS object preserved');
    assert(str_contains($result, 'if (config.debug) { console.log("Debug on"); }'), 'JS if block preserved');

    // Verify variables were injected
    assert(str_contains($result, 'background: #1e293b'), 'CSS variable injected');
    assert(str_contains($result, 'var apiUrl  = "https://api.example.com/v2"'), 'JS variable injected');
    assert(str_contains($result, 'var userId  = 42'), 'JS variable injected');

    echo "  ✅ CSS rules with { } preserved\n";
    echo "  ✅ JavaScript objects with { } preserved\n";
    echo "  ✅ Dynamic {{variables}} correctly injected into CSS and JS\n";
    echo "\n";
}

// ---------------------------------------------------------------------------
// EXAMPLE 11 — Method Chaining (Fluent Interface)
// ---------------------------------------------------------------------------
function example_method_chaining(): void
{
    $parser = new Parser();

    $output = $parser
        ->setTemplate('examples/blog_post')
        ->setData([
            'title'    => 'My Blog Post',
            'author'   => 'Jane Doe',
            'date'     => '2024-01-15',
            'content'  => 'This is the main content of the blog post.',
            'tags'     => ['php', 'templates', 'phuse'],
            'comments' => [
                ['author' => 'Alice', 'text' => 'Great post!'],
                ['author' => 'Bob',   'text' => 'Very helpful!'],
            ],
            'year'      => date('Y'),
            'assetsUrl' => '/assets/',
        ])
        ->enableCache(false)
        ->render('', [], true);

    echo "=== 11. Method Chaining ===\n";
    echo "  Blog post rendered: " . strlen($output) . " characters\n";
    echo "\n";
}

// ---------------------------------------------------------------------------
// EXAMPLE 12 — Error Handling
// ---------------------------------------------------------------------------
function example_error_handling(): void
{
    $parser = new Parser();

    echo "=== 12. Error Handling ===\n";

    try {
        $parser->parseData('', []);
    } catch (\Core\Exception\Error $e) {
        echo "  ✅ Empty template: " . $e->getMessage() . "\n";
    }

    try {
        $parser->setTemplate('nonexistent_template_xyz');
    } catch (\Core\Exception\Error $e) {
        echo "  ✅ Missing template: caught correctly\n";
    }

    try {
        $parser->setData('invalid_string');
    } catch (\Core\Exception\Error $e) {
        echo "  ✅ Invalid data type: " . $e->getMessage() . "\n";
    }

    echo "\n";
}

// ---------------------------------------------------------------------------
// Run all examples from CLI
// ---------------------------------------------------------------------------
if (php_sapi_name() === 'cli') {
    echo "\nPHUSE Template System Examples — v1.3.0\n";
    echo str_repeat('=', 50) . "\n\n";

    example_basic_variables();
    example_nested_access();
    example_filters();
    example_raw_output();
    example_comments();
    example_escaped_output();
    example_conditionals();
    example_foreach_loops();
    example_for_loop();
    example_inline_assets_safety();
    example_error_handling();

    echo "\n🌐 Web examples:\n";
    echo "  /examples              — Example index\n";
    echo "  /examples/basic        — Basic variable replacement\n";
    echo "  /examples/conditional  — If/else logic\n";
    echo "  /examples/foreach      — Array iteration\n";
    echo "  /examples/nested       — Nested data access\n";
    echo "  /examples/blog         — Blog post template\n";
    echo "  /examples/dashboard    — Admin dashboard\n";
    echo "  /examples/product      — E-commerce product page\n";
    echo "  /examples/inline-assets — Inline CSS/JS safety demo ✨ NEW\n";
    echo "\n📖 Docs: docs/template-system.md\n";
}
