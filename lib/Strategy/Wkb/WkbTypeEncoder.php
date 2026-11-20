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

namespace LongitudeOne\SpatialEncoder\Strategy\Wkb;

use LongitudeOne\Core\Enum\ByteOrderEnum;
use LongitudeOne\Core\Enum\GeometryTypeEnum;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialTypeException;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;

/**
 * Encodes ISO SQL/MM geometry type identifiers and coordinate dimensions.
 */
class WkbTypeEncoder
{
    /**
     * Write a type identifier in the selected byte order, with ISO Z/M/ZM offsets.
     *
     * @param SpatialInterface $spatial   the spatial object to encode
     * @param ByteOrderEnum    $byteOrder the selected binary byte order
     *
     * @throws UnsupportedSpatialTypeException when no implementation exists for the type
     */
    public function writeType(SpatialInterface $spatial, ByteOrderEnum $byteOrder = ByteOrderEnum::LITTLE_ENDIAN): string
    {
        $type = match ($spatial->getType()) {
            GeometryTypeEnum::POINT,
            GeometryTypeEnum::LINESTRING,
            GeometryTypeEnum::POLYGON,
            GeometryTypeEnum::MULTIPOINT,
            GeometryTypeEnum::MULTILINESTRING,
            GeometryTypeEnum::MULTIPOLYGON,
            GeometryTypeEnum::GEOMETRYCOLLECTION,
            GeometryTypeEnum::CIRCULARSTRING,
            GeometryTypeEnum::COMPOUNDCURVE,
            GeometryTypeEnum::POLYHEDRALSURFACE,
            GeometryTypeEnum::TRIANGLE => $spatial->getType()->wkbCode(),
            default => null,
        } ?? throw new UnsupportedSpatialTypeException(\sprintf('WKB adapter does not support spatial type %s', $spatial->getType()->value));
        $dimension = $spatial->getDimension()->wkbCode();

        return pack(ByteOrderEnum::BIG_ENDIAN === $byteOrder ? 'N' : 'V', $type + $dimension);
    }
}
