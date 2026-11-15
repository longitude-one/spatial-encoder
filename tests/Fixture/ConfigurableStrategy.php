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

namespace LongitudeOne\SpatialEncoder\Tests\Fixture;

use LongitudeOne\SpatialEncoder\Strategy\AbstractConfigurableStrategy;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** Fixture demonstrating custom defaults, allowed values and normalization. */
final class ConfigurableStrategy extends AbstractConfigurableStrategy
{
    /**
     * Return resolved options for assertions.
     *
     * @return array<string, mixed>
     */
    public function configuration(): array
    {
        return $this->options;
    }

    /**
     * Declare an application-owned option.
     *
     * @param OptionsResolver $resolver the options resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('label', 'compact');
        $resolver->setAllowedTypes('label', 'string');
        $resolver->setNormalizer('label', static function (Options $options, string $value): string {
            unset($options); // The normalizer callback receives context even when only the value is needed.

            return strtolower($value);
        });
        $resolver->setAllowedValues('label', ['compact', 'expanded', 'EXPANDED']);
    }
}
