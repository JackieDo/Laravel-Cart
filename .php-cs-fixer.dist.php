<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$rules = [
    '@PhpCsFixer' => true,
    'binary_operator_spaces' => [
        // 'default' => 'align_single_space_minimal',
    ],
    'blank_line_before_statement' => [
        'statements' => [
            // 'break',
            'case',
            'continue',
            'declare',
            'default',
            'phpdoc',
            'do',
            'exit',
            'for',
            'foreach',
            'while',
            'goto',
            'if',
            'include',
            'include_once',
            'require',
            'require_once',
            'return',
            'switch',
            'throw',
            'try',
        ],
    ],
    'concat_space' => [
        'spacing' => 'one',
    ],
    'new_with_braces' => false,
    'no_empty_comment' => false,
    'no_superfluous_phpdoc_tags' => false,
    'multiline_whitespace_before_semicolons' => [
        'strategy' => 'no_multi_line',
    ],
    'php_unit_method_casing' => [
        'case' => 'snake_case',
    ],
    'phpdoc_add_missing_param_annotation' => [
        'only_untyped' => false,
    ],
    'phpdoc_no_empty_return' => false,
    'phpdoc_no_package' => false,
];

$config = new Config;

return $config
    ->setFinder($finder)
    ->setRules($rules)
    ->setUsingCache(true);
