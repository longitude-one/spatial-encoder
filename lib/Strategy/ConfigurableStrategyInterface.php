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

/** Declares the options accepted by a strategy independently of its execution contract. */
interface ConfigurableStrategyInterface
{
    /**
     * Declare defaults, types, values and normalizers for this strategy.
     *
     * @param OptionsResolver $resolver the local configuration resolver
     */
    public function configureOptions(OptionsResolver $resolver): void;
}
