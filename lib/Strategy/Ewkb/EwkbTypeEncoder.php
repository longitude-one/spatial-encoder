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

namespace LongitudeOne\SpatialEncoder\Strategy\Ewkb;

use LongitudeOne\Core\Enum\ByteOrderEnum;
use LongitudeOne\Core\Enum\GeometryTypeEnum;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialTypeException;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;

/**
 * Encodes PostGIS EWKB type identifiers, dimension flags and optional SRID flag.
 */
class EwkbTypeEncoder
{
    /** Base geometry identifiers, before EWKB flags. */
    private const array TYPE_CODES = [
        GeometryTypeEnum::POINT->value => 1,
        GeometryTypeEnum::LINESTRING->value => 2,
        GeometryTypeEnum::POLYGON->value => 3,
        GeometryTypeEnum::MULTIPOINT->value => 4,
        GeometryTypeEnum::MULTILINESTRING->value => 5,
        GeometryTypeEnum::MULTIPOLYGON->value => 6,
        GeometryTypeEnum::GEOMETRYCOLLECTION->value => 7,
        GeometryTypeEnum::CIRCULARSTRING->value => 8,
        GeometryTypeEnum::COMPOUNDCURVE->value => 9,
        GeometryTypeEnum::POLYHEDRALSURFACE->value => 15,
        GeometryTypeEnum::TRIANGLE->value => 17,
    ];

    /**
     * Write a type identifier in the selected byte order, with EWKB Z/M/SRID flags.
     *
     * @param SpatialInterface $spatial     the spatial object to encode
     * @param bool             $includeSrid whether the header includes an SRID
     * @param ByteOrderEnum    $byteOrder   the selected binary byte order
     *
     * @throws UnsupportedSpatialTypeException when no implementation exists for the type
     */
    public function writeType(SpatialInterface $spatial, bool $includeSrid, ByteOrderEnum $byteOrder = ByteOrderEnum::LITTLE_ENDIAN): string
    {
        $type = self::TYPE_CODES[$spatial->getType()->value]
            ?? throw new UnsupportedSpatialTypeException(\sprintf('EWKB adapter does not support spatial type %s', $spatial->getType()->value));
        $dimension = ($spatial->hasZ() ? 0x80000000 : 0) | ($spatial->hasM() ? 0x40000000 : 0) | ($includeSrid ? 0x20000000 : 0);

        return pack(ByteOrderEnum::BIG_ENDIAN === $byteOrder ? 'N' : 'V', $type | $dimension);
    }
}
