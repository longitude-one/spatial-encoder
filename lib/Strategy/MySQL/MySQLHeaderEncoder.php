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

/**
 * Encodes the fixed header parts of MySQL's internal geometry format.
 *
 * Use pack('V') for 32-bit integers: MySQL's internal format requires little-endian
 * bytes. pack('L') uses the machine's native byte order and is not portable.
 */
class MySQLHeaderEncoder
{
    /**
     * Write the little-endian byte-order marker.
     *
     * @return string a binary string representing the first byte in the internal MySQL storage format
     */
    public function writeByteOrder(): string
    {
        return pack('C', 1);
    }

    /**
     * Write an SRID, using zero when it is absent.
     *
     * @param ?int $srid the spatial interface to write
     *
     * @return string a binary string representing the SRID in the internal MySQL storage format
     */
    public function writeSrid(?int $srid): string
    {
        return pack('V', $srid ?? 0);
    }
}
