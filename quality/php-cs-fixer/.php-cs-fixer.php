<?php
/**
 * This file is part of the spatial-encoder project.
 *
 * PHP 8.4 | 8.5
 *
 * Copyright Alexandre Tranchant <alexandre.tranchant@gmail.com> 2026
 * Copyright Longitude One 2026
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

declare(strict_types=1);

/**
 * This file is part of the BB-One Project.
 *
 * PHP 8.2 | Symfony 6.3.*
 *
 * Copyright LongitudeOne - Alexandre Tranchant
 * Copyright 2023
 */

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
        __DIR__.'/../../quality/',
        __DIR__.'/../../tests/',
    ])
    ->append([__FILE__]);

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
            'comment_type' => 'PHPDoc',
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

        // Required for PHPMD compatibility.
        'new_expression_parentheses' => [
            'use_parentheses' => true,
        ],
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__.'/.php_cs.cache')
;
