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

namespace LongitudeOne\SpatialEncoder\Tests\Functional;

use LongitudeOne\SpatialEncoder\Encoder;
use LongitudeOne\SpatialEncoder\Strategy\EwktTextStrategy;
use LongitudeOne\SpatialEncoder\Strategy\WktTextStrategy;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\CircularStringImmutable as GeographicCircularString;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\CompoundCurveImmutable as GeographicCompoundCurve;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\LineStringImmutable as GeographicLineString;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\CircularString;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\CompoundCurve;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\LineString;
use PHPUnit\Framework\TestCase;

/**
 * Export real compound curves through the public encoder entry point.
 *
 * @internal
 */
class CompoundCurveEncodingTest extends TestCase
{
    /** Geographic coordinates retain longitude-first order and the closing endpoint. */
    public function testClosedGeographicCurveKeepsItsCoordinates(): void
    {
        $curve = new GeographicCompoundCurve([
            new GeographicCircularString([[2, 40], [3, 41], [4, 40]], 4326),
            new GeographicLineString([[4, 40], [2, 40]], 4326),
        ], 4326);
        $encoder = new Encoder(new EwktTextStrategy());

        self::assertSame(
            'SRID=4326;COMPOUNDCURVE (CIRCULARSTRING (2 40, 3 41, 4 40), (4 40, 2 40))',
            $encoder->encode($curve)
        );
        $encoder->setStrategy(new WktTextStrategy());
        self::assertSame(
            'COMPOUNDCURVE (CIRCULARSTRING (2 40, 3 41, 4 40), (4 40, 2 40))',
            $encoder->encode($curve)
        );
        self::assertSame([
            [[2, 40], [3, 41], [4, 40]],
            [[4, 40], [2, 40]],
        ], $curve->toArray());
    }

    /** Switching formats preserves the source components and their XYZM ordinates. */
    public function testMutableCurveCanBeExportedInBothTextFormats(): void
    {
        $line = new LineString([[0, 0, 3, 6], [2, 0, 4, 7]], 4326);
        $arc = new CircularString([[2, 0, 4, 7], [3, 1, 5, 8], [4, 0, 6, 9]], 4326);
        $curve = new CompoundCurve([$line, $arc], 4326);
        $encoder = new Encoder(new WktTextStrategy());

        self::assertSame(
            'COMPOUNDCURVE ZM ((0 0 3 6, 2 0 4 7), CIRCULARSTRING ZM (2 0 4 7, 3 1 5 8, 4 0 6 9))',
            $encoder->encode($curve)
        );
        $encoder->setStrategy(new EwktTextStrategy());
        self::assertSame(
            'SRID=4326;COMPOUNDCURVE ZM ((0 0 3 6, 2 0 4 7), CIRCULARSTRING ZM (2 0 4 7, 3 1 5 8, 4 0 6 9))',
            $encoder->encode($curve)
        );
        self::assertSame($line, $curve->getCurve(0));
        self::assertSame($arc, $curve->getCurve(1));
        self::assertSame([
            [[0, 0, 3, 6], [2, 0, 4, 7]],
            [[2, 0, 4, 7], [3, 1, 5, 8], [4, 0, 6, 9]],
        ], $curve->toArray());
        self::assertSame(4326, $curve->getSrid());
    }
}
