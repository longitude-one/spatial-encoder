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

use LongitudeOne\SpatialTypes\Interfaces\PointInterface;

/**
 * Encodes X then Y, including longitude then latitude for geographic points.
 */
class MySQLPointEncoder
{
    /**
     * Write point coordinates.
     *
     * @param PointInterface $point the point to write
     *
     * @return string a binary string representing ordered coordinates in the internal MySQL storage format
     */
    public function writePoint(PointInterface $point): string
    {
        return pack('ee', $point->getX(), $point->getY());
    }
}
