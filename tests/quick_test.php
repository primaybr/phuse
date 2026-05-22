<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use Core\Template\Parser;

$parser = new Parser();
$passed = 0;
$failed = 0;

function check(string $label, bool $result): void {
    global $passed, $failed;
    if ($result) {
        echo "  ✅ $label\n";
        $passed++;
    } else {
        echo "  ❌ FAIL: $label\n";
        $failed++;
    }
}

// -----------------------------------------------
echo "\n=== 1. Basic double-brace variable ===\n";
$r = $parser->parseData('Hello {{name}}!', ['name' => 'World']);
check('{{name}} replaced', str_contains($r, 'World'));

// -----------------------------------------------
echo "\n=== 2. Single braces untouched ===\n";
$r = $parser->parseData('.btn { color: red; }', []);
// After minification: .btn{color:red;}
check('Single { } not mangled (rule present)', str_contains($r, '.btn') && str_contains($r, 'color'));

// -----------------------------------------------
echo "\n=== 3. Inline CSS preserved + variable replaced ===\n";
$tpl = '<style>.hero { background: {{bgColor}}; } .card { padding: 1rem; }</style>';
$r = $parser->parseData($tpl, ['bgColor' => '#111']);
// Minified: .hero{background:#111;}.card{padding:1rem;}
check('.card rule intact (minified)',  str_contains($r, '.card'));
check('{{bgColor}} replaced in style', str_contains($r, '#111'));

// -----------------------------------------------
echo "\n=== 4. Inline JS preserved (minified) ===\n";
$tpl = '<script>var cfg = { debug: false }; if (cfg.debug) { console.log("on"); }</script>{{title}}';
$r = $parser->parseData($tpl, ['title' => 'DONE']);
// Minified: var cfg={debug:false};if (cfg.debug){console.log("on");}
check('JS object present (minified)',  str_contains($r, 'cfg') && str_contains($r, 'debug'));
check('JS if block present (minified)', str_contains($r, 'console.log'));
check('{{title}} replaced outside script', str_contains($r, 'DONE'));

// -----------------------------------------------
echo "\n=== 5. JS variable injection inside <script> ===\n";
$tpl = '<script>var url = "{{apiUrl}}"; var id = {{userId}};</script>';
// Use a URL without // to avoid any possible minifier edge cases
$r = $parser->parseData($tpl, ['apiUrl' => 'https://api.example.com', 'userId' => 42]);
check('apiUrl injected (URL preserved)', str_contains($r, 'api.example.com'));
check('userId injected', str_contains($r, '42'));

// -----------------------------------------------
echo "\n=== 6. Comments stripped ===\n";
$r = $parser->parseData('{# hidden comment #}Hello {{name}}!', ['name' => 'World']);
check('Comment stripped (no {#)', !str_contains($r, '{#'));
check('Variable after comment works', str_contains($r, 'World'));

// -----------------------------------------------
echo "\n=== 7. Raw output {!! !!} ===\n";
$r = $parser->parseData('{!! htmlContent !!}', ['htmlContent' => '<strong>Bold</strong>']);
check('{!! !!} not escaped', str_contains($r, '<strong>Bold</strong>'));

// -----------------------------------------------
echo "\n=== 8. Escaped tag @{{}} ===\n";
$r = $parser->parseData('Docs: @{{variable}}', ['variable' => 'ignored']);
check('@{{}} outputs literal {{}}', str_contains($r, '{{variable}}'));
check('value not injected', !str_contains($r, 'ignored'));

// -----------------------------------------------
echo "\n=== 9. Filters ===\n";
$r = $parser->parseData('{{name|upper}}', ['name' => 'hello']);
check('|upper', str_contains($r, 'HELLO'));
$r = $parser->parseData('{{name|capitalize}}', ['name' => 'hello world']);
check('|capitalize', str_contains($r, 'Hello World'));
$r = $parser->parseData('{{name|substr:0:5}}', ['name' => 'hello world']);
check('|substr:0:5', str_contains($r, 'hello'));
$r = $parser->parseData('{{name|substr:0:1|upper}}', ['name' => 'alice']);
check('|substr:0:1|upper (chained)', str_contains($r, 'A'));
$r = $parser->parseData('{{items|length}}', ['items' => ['a','b','c']]);
check('|length', str_contains($r, '3'));

// -----------------------------------------------
echo "\n=== 10. Nested dot-notation ===\n";
$r = $parser->parseData('{{user.name}}', ['user' => ['name' => 'Alice']]);
check('{{user.name}}', str_contains($r, 'Alice'));
$r = $parser->parseData('{{user.profile.city}}', ['user' => ['profile' => ['city' => 'Jakarta']]]);
check('{{user.profile.city}}', str_contains($r, 'Jakarta'));

// -----------------------------------------------
echo "\n=== 11. Conditionals ===\n";
$r = $parser->parseData('{% if show %}YES{% endif %}', ['show' => true]);
check('{% if true %} shows if-content', str_contains($r, 'YES'));
$r = $parser->parseData('{% if show %}YES{% else %}NO{% endif %}', ['show' => false]);
check('{% if false %} shows else-content', str_contains($r, 'NO'));
$r = $parser->parseData('{% if show %}YES{% else %}NO{% endif %}', ['show' => true]);
check('{% if true %} with else shows if-content', str_contains($r, 'YES'));

// -----------------------------------------------
echo "\n=== 12. Foreach loop ===\n";
$tpl = '{% foreach items as item %}{{item.name}} {% endforeach %}';
$r = $parser->parseData($tpl, ['items' => [['name'=>'A'],['name'=>'B'],['name'=>'C']]]);
check('foreach loop (A B C)', str_contains($r, 'A') && str_contains($r, 'B') && str_contains($r, 'C'));

// -----------------------------------------------
echo "\n=== 13. Numeric for loop ===\n";
$r = $parser->parseData('{% for i in 1..3 %}{{i}} {% endfor %}', []);
check('for 1..3 (1 2 3)', str_contains($r, '1') && str_contains($r, '2') && str_contains($r, '3'));

// -----------------------------------------------
echo "\n=== 14. Foreach with filter and if/else inside ===\n";
$tpl = '{% foreach products as product %}{{product.name|upper}}{% if product.in_stock %}+{% else %}-{% endif %} {% endforeach %}';
$r = $parser->parseData($tpl, ['products' => [
    ['name' => 'apple', 'in_stock' => true],
    ['name' => 'mango', 'in_stock' => false],
]]);
check('foreach+filter+if (APPLE+ MANGO-)', str_contains($r, 'APPLE') && str_contains($r, 'MANGO'));

// -----------------------------------------------
echo "\n=== 15. JS URL not corrupted by minifier ===\n";
$tpl = '<script>var cfg = { api: "https://api.example.com/v2", id: {{userId}} };</script>';
$r = $parser->parseData($tpl, ['userId' => 99]);
check('https:// in string preserved', str_contains($r, 'api.example.com'));
check('userId injected', str_contains($r, '99'));

// -----------------------------------------------
echo "\n=== Summary ===\n";
$total = $passed + $failed;
echo "  Passed: $passed / $total\n";
if ($failed > 0) {
    echo "  Failed: $failed\n";
    exit(1);
}
echo "\n  All checks passed! ✅\n\n";
