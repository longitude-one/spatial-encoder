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

use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialInterfaceException;
use LongitudeOne\SpatialEncoder\Strategy\Wkt\WktPointEncoder;
use LongitudeOne\SpatialEncoder\Strategy\Wkt\WktTypeEncoder;
use LongitudeOne\SpatialTypes\Interfaces\CircularStringInterface;
use LongitudeOne\SpatialTypes\Interfaces\CollectionInterface;
use LongitudeOne\SpatialTypes\Interfaces\LineStringInterface;
use LongitudeOne\SpatialTypes\Interfaces\MultiLineStringInterface;
use LongitudeOne\SpatialTypes\Interfaces\MultiPointInterface;
use LongitudeOne\SpatialTypes\Interfaces\MultiPolygonInterface;
use LongitudeOne\SpatialTypes\Interfaces\PolygonInterface;
use LongitudeOne\SpatialTypes\Interfaces\PolyhedralSurfaceInterface;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;

/**
 * Writes WKT in coordinate order X Y [Z] [M], without SRID metadata.
 */
class WktTextStrategy extends AbstractConfigurableStrategy implements StrategyInterface
{
    /**
     * Encode a spatial object to Well-Known Text, preserving its dimensions.
     *
     * @see https://libgeos.org/specifications/wkt/
     *
     * @param SpatialInterface $spatial the spatial object to encode
     *
     * @throws UnsupportedSpatialInterfaceException when the spatial interface is not supported
     * @throws \JsonException                       when a coordinate is not finite
     */
    public function encode(SpatialInterface $spatial): string
    {
        return $this->writeGeometry($spatial);
    }

    /**
     * Write a geometry body, including its surrounding parentheses.
     *
     * @param SpatialInterface $spatial the spatial object to encode
     */
    private function writeBody(SpatialInterface $spatial): string
    {
        if ([] === $spatial->toArray()) {
            return 'EMPTY';
        }

        return '('.$this->writeCoordinates($spatial).')';
    }

    /**
     * Write the contents of a nonempty geometry body.
     *
     * @param SpatialInterface $spatial the spatial object to encode
     */
    private function writeCoordinates(SpatialInterface $spatial): string
    {
        return match (true) {
            $spatial instanceof LineStringInterface,
            $spatial instanceof CircularStringInterface => implode(', ', array_map(new WktPointEncoder()->writePoint(...), $spatial->getPoints())),
            $spatial instanceof PolygonInterface => $this->writeMembers($spatial->getRings()),
            $spatial instanceof MultiPointInterface => $this->writeMembers($spatial->getPoints()),
            $spatial instanceof MultiLineStringInterface => $this->writeMembers($spatial->getLineStrings()),
            $spatial instanceof MultiPolygonInterface => $this->writeMembers($spatial->getPolygons()),
            $spatial instanceof PolyhedralSurfaceInterface => $this->writeMembers($spatial->getPatches()),
            $spatial instanceof CollectionInterface => implode(', ', array_map($this->writeGeometry(...), $spatial->getElements())),
            default => new WktPointEncoder()->writePoint($spatial),
        };
    }

    /**
     * Write a complete WKT geometry, keeping collection members free of prefixes.
     *
     * @param SpatialInterface $spatial the spatial object to encode
     */
    private function writeGeometry(SpatialInterface $spatial): string
    {
        return new WktTypeEncoder()->writeType($spatial).' '.$this->writeBody($spatial);
    }

    /**
     * Write the bodies of homogeneous geometry members.
     *
     * @param SpatialInterface[] $members the members to encode
     */
    private function writeMembers(array $members): string
    {
        return implode(', ', array_map($this->writeBody(...), $members));
    }
}
