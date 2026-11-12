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

namespace LongitudeOne\SpatialEncoder\Strategy\Wkt;

use LongitudeOne\Core\Enum\GeometryTypeEnum;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialTypeException;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;

/**
 * Encodes WKT type names and coordinate dimension markers.
 */
class WktTypeEncoder
{
    /**
     * Write the WKT type name and optional Z, M, or ZM marker.
     *
     * @param SpatialInterface $spatial the spatial object to encode
     *
     * @throws UnsupportedSpatialTypeException when the spatial type is not supported
     */
    public function writeType(SpatialInterface $spatial): string
    {
        $type = match ($spatial->getType()) {
            GeometryTypeEnum::POINT,
            GeometryTypeEnum::LINESTRING,
            GeometryTypeEnum::POLYGON,
            GeometryTypeEnum::MULTIPOINT,
            GeometryTypeEnum::MULTILINESTRING,
            GeometryTypeEnum::MULTIPOLYGON,
            GeometryTypeEnum::GEOMETRYCOLLECTION,
            GeometryTypeEnum::TRIANGLE,
            GeometryTypeEnum::POLYHEDRALSURFACE => mb_strtoupper($spatial->getType()->value),
            default => throw new UnsupportedSpatialTypeException(\sprintf('WKT adapter does not support spatial type %s', $spatial->getType()->value)),
        };
        $dimension = ($spatial->hasZ() ? 'Z' : '').($spatial->hasM() ? 'M' : '');

        return $type.('' === $dimension ? '' : ' '.$dimension);
    }
}
