<?php
/*
 * This file is part of the LongitudeOne Spatial project.
 *
 * Copyright (c) 2026 Alexandre Tranchant <alexandre.tranchant@gmail.com>
 *
 * See LICENSE for copyright and license information.
 *
 */

declare(strict_types=1);

// Replace the value of this variable with the project's launch year.
const FIRST_YEAR = 2026;

function __copyright(int $launchYear): string
{
    $currentYear = (int) date('Y');
    if ($currentYear === $launchYear) {
        return (string) $currentYear;
    }

    return sprintf('%d-%d', $launchYear, $currentYear);
}

$header = file_get_contents(__DIR__.'/headers.txt');
$header = str_replace('%year%', __copyright(FIRST_YEAR), $header);

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__.'/../../lib/',
        __DIR__.'/../../tests/',
        __DIR__.'/../../quality/',
    ])
    ->append([
        __FILE__,
    ])
;

$config = new PhpCsFixer\Config();

return $config
    ->setRiskyAllowed(true)
    ->setRules([
        // Coding style
        '@Symfony' => true,
        '@Symfony:risky' => true,

        // PHP version
        '@autoPHPMigration' => true,
        '@autoPHPMigration:risky' => true,

        // PHP Unit Version
        '@autoPHPUnitMigration:risky' => true,

        // Headers
        'header_comment' => [
            'comment_type' => 'comment',
            'header' => $header,
            'location' => 'after_open',
            'separate' => 'bottom',
        ],

        // Order
        'ordered_interfaces' => [
            'direction' => 'ascend',
            'order' => 'alpha',
        ],

        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'case',
                'constant_public',
                'constant_protected',
                'constant_private',
                'constant',
                'property_public_static',
                'property_protected_static',
                'property_private_static',
                'property_static',
                'property_public',
                'property_protected',
                'property_private',
                'property',
                'construct',
                'destruct',
                'phpunit',
                'method_public_static',
                'method_protected_static',
                'method_private_static',
                'method_static',
                'method_public',
                'method_protected',
                'method_private',
                'method',
                'magic',
            ],
            'sort_algorithm' => 'alpha',
        ],

        // LongitudeOne conventions / overrides
        'declare_strict_types' => true,
        'php_unit_test_case_static_method_calls' => [
            'call_type' => 'self',
            'target' => 'newest',
        ],

        'new_expression_parentheses' => [
            'use_parentheses' => false,
        ],
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__.'/.php_cs.cache')
;
