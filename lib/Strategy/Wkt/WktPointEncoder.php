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

use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialInterfaceException;
use LongitudeOne\SpatialTypes\Interfaces\PointInterface;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;
use TypeError as CoordinateTypeError;

/**
 * Formats WKT point ordinates for point and curve bodies.
 *
 * @internal
 */
final class WktPointEncoder
{
    /**
     * Write the ordinates of a nonempty point in its declared dimension.
     *
     * @param SpatialInterface $point the point to encode
     */
    public function writePoint(SpatialInterface $point): string
    {
        if (!$point instanceof PointInterface) {
            throw new UnsupportedSpatialInterfaceException($point::class);
        }

        return implode(' ', array_map($this->writeCoordinate(...), $point->toArray()));
    }

    /**
     * Write a coordinate without locale-dependent decimal separators.
     *
     * JSON uses PHP's serialize_precision setting, whose default (-1) preserves
     * floating-point values using their shortest round-trip representation.
     *
     * @param float|int|null $coordinate the ordinate to format
     *
     * @throws CoordinateTypeError when the ordinate is null
     * @throws \JsonException      when the coordinate is not finite
     */
    private function writeCoordinate(float|int|null $coordinate): string
    {
        if (null === $coordinate) {
            throw new CoordinateTypeError('WKT requires numeric ordinates; null given.');
        }

        return json_encode($coordinate, \JSON_THROW_ON_ERROR);
    }
}
