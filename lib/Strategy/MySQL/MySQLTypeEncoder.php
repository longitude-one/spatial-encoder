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

namespace LongitudeOne\SpatialEncoder\Strategy\MySQL;

use LongitudeOne\Core\Enum\GeometryTypeEnum;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedDimensionException;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialTypeException;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;

/**
 * Encodes MySQL geometry type identifiers.
 *
 * Use pack('V') for 32-bit integers: MySQL's internal format requires little-endian
 * bytes. pack('L') uses the machine's native byte order and is not portable.
 */
class MySQLTypeEncoder
{
    /**
     * Write the binary MySQL type identifier for a spatial value.
     *
     * @param SpatialInterface $spatial the spatial interface
     *
     * @return string a binary string representing the type in the internal MySQL storage format
     *
     * @throws UnsupportedDimensionException   when coordinates include Z or M
     * @throws UnsupportedSpatialTypeException when the spatial type or empty value is not supported
     */
    public function writeType(SpatialInterface $spatial): string
    {
        // This match is the explicit allow-list: reject unknown types before inspecting their payload.
        $type = match ($spatial->getType()) {
            GeometryTypeEnum::POINT,
            GeometryTypeEnum::LINESTRING,
            GeometryTypeEnum::POLYGON,
            GeometryTypeEnum::MULTIPOINT,
            GeometryTypeEnum::MULTILINESTRING,
            GeometryTypeEnum::MULTIPOLYGON,
            GeometryTypeEnum::GEOMETRYCOLLECTION => $spatial->getType()->wkbCode(),
            default => null,
        } ?? throw new UnsupportedSpatialTypeException(\sprintf('MySQL adapter does not support spatial type %s', $spatial->getType()->value));

        $this->validateInput($spatial);

        return pack('V', $type);
    }

    /**
     * Validate each geometry before writing its header, including nested members.
     *
     * @param SpatialInterface $spatial the geometry to validate
     *
     * @throws UnsupportedDimensionException   when coordinates include Z or M
     * @throws UnsupportedSpatialTypeException when an empty value is not a collection
     */
    private function validateInput(SpatialInterface $spatial): void
    {
        if ($spatial->hasZ() || $spatial->hasM()) {
            throw new UnsupportedDimensionException('MySQL adapter only supports XY coordinates; Z and M are not supported.');
        }

        if (GeometryTypeEnum::GEOMETRYCOLLECTION !== $spatial->getType() && [] === $spatial->toArray()) {
            throw new UnsupportedSpatialTypeException(\sprintf('MySQL adapter does not support empty %s; only GeometryCollection may be empty.', $spatial->getType()->value));
        }
    }
}
