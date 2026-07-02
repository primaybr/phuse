<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in([__DIR__ . '/Core', __DIR__ . '/App'])
    ->name('*.php')
    ->exclude(['vendor', 'tests']);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        'declare_strict_types' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'trailing_comma_in_multiline' => true,
        'single_quote' => true,
        'no_trailing_whitespace' => true,
        'blank_line_after_namespace' => true,
        'blank_line_after_opening_tag' => true,
        'no_extra_blank_lines' => true,
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
    ])
    ->setFinder($finder);
