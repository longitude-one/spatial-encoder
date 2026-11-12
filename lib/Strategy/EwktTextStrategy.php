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

use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;

/**
 * Extends WKT with an SRID prefix when the spatial reference is nonzero.
 */
class EwktTextStrategy extends WktTextStrategy
{
    /**
     * Write WKT with a single SRID prefix for the outermost geometry.
     *
     * @see https://postgis.net/docs/ST_AsEWKT.html
     *
     * @param SpatialInterface $spatial the spatial object to encode
     */
    public function encode(SpatialInterface $spatial): string
    {
        $wkt = parent::encode($spatial);
        $srid = $spatial->getSrid();

        return 0 === $srid ? $wkt : \sprintf('SRID=%d;%s', $srid, $wkt);
    }
}
