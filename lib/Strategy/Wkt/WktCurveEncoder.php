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

use LongitudeOne\SpatialTypes\Interfaces\CircularStringInterface;
use LongitudeOne\SpatialTypes\Interfaces\CompoundCurveInterface;
use LongitudeOne\SpatialTypes\Interfaces\CurveInterface;
use LongitudeOne\SpatialTypes\Interfaces\LineStringInterface;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;

/**
 * Writes curve coordinates while preserving compound component grammar.
 *
 * @internal
 */
final class WktCurveEncoder
{
    /**
     * @param CurveInterface                     $curve         Curve to encode
     * @param callable(SpatialInterface): string $writeBody     Geometry body writer
     * @param callable(SpatialInterface): string $writeGeometry Complete geometry writer
     */
    public function writeCoordinates(CurveInterface $curve, callable $writeBody, callable $writeGeometry): string
    {
        if ($curve instanceof CompoundCurveInterface) {
            return implode(', ', array_map(
                static fn (CurveInterface $component): string => $component instanceof LineStringInterface ? $writeBody($component) : $writeGeometry($component),
                $curve->getCurves()
            ));
        }

        if ($curve instanceof LineStringInterface || $curve instanceof CircularStringInterface) {
            return implode(', ', array_map(new WktPointEncoder()->writePoint(...), $curve->getPoints()));
        }

        return new WktPointEncoder()->writePoint($curve);
    }
}
