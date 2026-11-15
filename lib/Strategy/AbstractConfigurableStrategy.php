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

namespace LongitudeOne\SpatialEncoder\Strategy;

use Symfony\Component\OptionsResolver\OptionsResolver;

/** Optional construction-time option resolution for strategy implementations. */
abstract class AbstractConfigurableStrategy implements ConfigurableStrategyInterface
{
    /** @var array<string, mixed> Resolved strategy options. */
    protected readonly array $options;

    /**
     * Resolve options once, retaining no resolver or caller-owned array.
     *
     * @param array<string, mixed> $options strategy-specific configuration
     */
    public function __construct(array $options = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
    }

    /**
     * Accept no options unless a strategy declares its own configuration.
     *
     * @param OptionsResolver $resolver the local configuration resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
