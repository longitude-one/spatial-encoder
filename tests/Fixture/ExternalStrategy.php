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

use LongitudeOne\SpatialEncoder\Strategy\ConfigurableStrategyInterface;
use LongitudeOne\SpatialEncoder\Strategy\StrategyInterface;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** Application-owned array encoding with no base-class inheritance. */
final class ExternalStrategy implements ConfigurableStrategyInterface, StrategyInterface
{
    /** @var array<string, mixed> Resolved options. */
    private readonly array $options;

    /** @param array<string, mixed> $options application format options */
    public function __construct(array $options = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
    }

    /** @param OptionsResolver $resolver local configuration resolver */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('include_srid', false);
        $resolver->setAllowedTypes('include_srid', 'bool');
    }

    /**
     * Encode an application-specific array representation.
     *
     * @param SpatialInterface $spatial source geometry
     *
     * @return array<string, mixed>
     */
    public function encode(SpatialInterface $spatial): array
    {
        $result = ['coordinates' => $spatial->toArray()];
        if ($this->options['include_srid']) {
            $result['srid'] = $spatial->getSrid();
        }

        return $result;
    }
}
