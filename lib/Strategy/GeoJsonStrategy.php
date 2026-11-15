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

use LongitudeOne\SpatialEncoder\Exception\JsonEncodingException;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedDimensionException;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedGeometryStructureException;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialInterfaceException;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialTypeException;
use LongitudeOne\SpatialEncoder\Strategy\GeoJson\GeometryEncoder;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Encodes supported geometries using RFC 7946 without transforming coordinates.
 */
class GeoJsonStrategy extends AbstractConfigurableStrategy implements StrategyInterface
{
    /**
     * Declare only the options supported by this format.
     *
     * @param OptionsResolver $resolver the local configuration resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('pretty_print', false);
        $resolver->setAllowedTypes('pretty_print', 'bool');
    }

    /**
     * Encode a geometry, preserving its coordinates and omitting reference metadata.
     *
     * @param SpatialInterface $spatial the geometry to encode
     *
     * @throws UnsupportedDimensionException         when the geometry declares M
     * @throws UnsupportedSpatialTypeException       when the type is not supported
     * @throws UnsupportedSpatialInterfaceException  when the geometry interface is missing
     * @throws UnsupportedGeometryStructureException when the geometry has an incompatible structure
     * @throws JsonEncodingException                 when JSON encoding fails
     */
    public function encode(SpatialInterface $spatial): string
    {
        if ($spatial->hasM()) {
            throw new UnsupportedDimensionException('GeoJSON does not support measured coordinates.');
        }

        $geometry = new GeometryEncoder()->encode($spatial);

        try {
            return json_encode($geometry, \JSON_THROW_ON_ERROR | ($this->options['pretty_print'] ? \JSON_PRETTY_PRINT : 0));
        } catch (\JsonException $exception) {
            throw new JsonEncodingException('Unable to encode the spatial instance as GeoJSON.', previous: $exception);
        }
    }
}
