<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return (new Config())
    ->setUnsupportedPhpVersionAllowed(true)
    ->setRules([
        '@PSR12' => true,
        'no_useless_concat_operator' => false,
        'numeric_literal_separator' => true,
        'no_unused_imports' => true,
        'blank_line_before_statement' => true,
        'php_unit_attributes' => false,
        'no_extra_blank_lines' => true,
        'trailing_comma_in_multiline' => [
            'elements' => ['arguments', 'arrays', 'match', 'parameters'],
        ],
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
        ],
        'no_empty_phpdoc' => true,
        'no_superfluous_phpdoc_tags' => true,
        'phpdoc_trim' => true,
        'no_extra_blank_lines' => [
            'tokens' => ['break', 'continue', 'curly_brace_block', 'extra', 'parenthesis_brace_block', 'return', 'square_brace_block', 'throw', 'use'],
        ],
    ])
    ->setFinder(
        (new Finder())
            ->ignoreDotFiles(false)
            ->ignoreVCSIgnored(true)
            ->in(__DIR__),
    )
;
