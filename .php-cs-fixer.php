<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__)
    ->name('*.php')
    ->exclude('app')
    ->exclude('public')
    ->exclude('vendor')
    ->exclude('storage');

$config = new Config();
return $config->setRules([
    '@PSR12' => true,
    'array_syntax' => ['syntax' => 'short'],
    'binary_operator_spaces' => ['default' => 'align_single_space_minimal'],
    'blank_line_before_statement' => ['statements' => ['return']],
    'concat_space' => ['spacing' => 'one'],
    'no_unused_imports' => true,
    'single_quote' => true,
    'no_extra_blank_lines' => ['tokens' => ['extra']],
])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setUsingCache(true);
