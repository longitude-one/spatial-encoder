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

namespace LongitudeOne\SpatialEncoder\Tests\Unit\Strategy;

use LongitudeOne\SpatialEncoder\Strategy\EwktTextStrategy;
use LongitudeOne\SpatialEncoder\Strategy\Wkt\WktCurveEncoder;
use LongitudeOne\SpatialEncoder\Strategy\Wkt\WktTypeEncoder;
use LongitudeOne\SpatialEncoder\Strategy\WktTextStrategy;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\CircularStringImmutable as CircularString;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\CompoundCurveImmutable as CompoundCurve;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\GeometryCollectionImmutable as GeometryCollection;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\LineStringImmutable as LineString;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\CircularStringImmutable as CircularStringM;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\CompoundCurveImmutable as CompoundCurveM;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\LineStringImmutable as LineStringM;
use LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\CircularStringImmutable as CircularStringZ;
use LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\CompoundCurveImmutable as CompoundCurveZ;
use LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\LineStringImmutable as LineStringZ;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\CircularStringImmutable as CircularStringZm;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\CompoundCurveImmutable as CompoundCurveZm;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\LineStringImmutable as LineStringZm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Preserve compound curve component syntax and coordinate dimensions.
 *
 * @internal
 */
#[CoversClass(WktTextStrategy::class)]
#[CoversClass(EwktTextStrategy::class)]
#[CoversClass(WktTypeEncoder::class)]
#[CoversClass(WktCurveEncoder::class)]
class CompoundCurveTextTest extends TestCase
{
    /** Preserve the circularonly component sequence in both formats. */
    public function testCircularOnly(): void
    {
        $curve = new CompoundCurve([
            new CircularString([[0, 0], [1, 1], [2, 0]], 4326),
            new CircularString([[2, 0], [3, -1], [4, 0]], 4326),
        ], 4326);

        self::assertSame('COMPOUNDCURVE (CIRCULARSTRING (0 0, 1 1, 2 0), CIRCULARSTRING (2 0, 3 -1, 4 0))', new WktTextStrategy()->encode($curve));
        self::assertSame('SRID=4326;COMPOUNDCURVE (CIRCULARSTRING (0 0, 1 1, 2 0), CIRCULARSTRING (2 0, 3 -1, 4 0))', new EwktTextStrategy()->encode($curve));
    }

    /** Preserve the closed component sequence in both formats. */
    public function testClosed(): void
    {
        $curve = new CompoundCurve([
            new CircularString([[0, 0], [1, 1], [2, 0]], 4326),
            new LineString([[2, 0], [0, 0]], 4326),
        ], 4326);

        self::assertSame('COMPOUNDCURVE (CIRCULARSTRING (0 0, 1 1, 2 0), (2 0, 0 0))', new WktTextStrategy()->encode($curve));
        self::assertSame('SRID=4326;COMPOUNDCURVE (CIRCULARSTRING (0 0, 1 1, 2 0), (2 0, 0 0))', new EwktTextStrategy()->encode($curve));
    }

    /** Collection recursion prefixes only the outermost geometry. */
    public function testCollection(): void
    {
        $collection = new GeometryCollection(4326, [
            new CompoundCurve([new LineString([[0, 0], [2, 0]], 4326)], 4326),
            new CompoundCurve([], 4326),
        ]);

        self::assertSame(
            'GEOMETRYCOLLECTION (COMPOUNDCURVE ((0 0, 2 0)), COMPOUNDCURVE EMPTY)',
            new WktTextStrategy()->encode($collection)
        );
        self::assertSame(
            'SRID=4326;GEOMETRYCOLLECTION (COMPOUNDCURVE ((0 0, 2 0)), COMPOUNDCURVE EMPTY)',
            new EwktTextStrategy()->encode($collection)
        );
    }

    /** Preserve EMPTY and its dimension marker. */
    public function testEmpty(): void
    {
        $curve = new CompoundCurve([], 4326);

        self::assertSame('COMPOUNDCURVE EMPTY', new WktTextStrategy()->encode($curve));
        self::assertSame('SRID=4326;COMPOUNDCURVE EMPTY', new EwktTextStrategy()->encode($curve));
    }

    /** Preserve EMPTY and its dimension marker. */
    public function testEmptyM(): void
    {
        $curve = new CompoundCurveM([], 4326);

        self::assertSame('COMPOUNDCURVE M EMPTY', new WktTextStrategy()->encode($curve));
        self::assertSame('SRID=4326;COMPOUNDCURVE M EMPTY', new EwktTextStrategy()->encode($curve));
    }

    /** Preserve EMPTY and its dimension marker. */
    public function testEmptyZ(): void
    {
        $curve = new CompoundCurveZ([], 4326);

        self::assertSame('COMPOUNDCURVE Z EMPTY', new WktTextStrategy()->encode($curve));
        self::assertSame('SRID=4326;COMPOUNDCURVE Z EMPTY', new EwktTextStrategy()->encode($curve));
    }

    /** Preserve EMPTY and its dimension marker. */
    public function testEmptyZm(): void
    {
        $curve = new CompoundCurveZm([], 4326);

        self::assertSame('COMPOUNDCURVE ZM EMPTY', new WktTextStrategy()->encode($curve));
        self::assertSame('SRID=4326;COMPOUNDCURVE ZM EMPTY', new EwktTextStrategy()->encode($curve));
    }

    /** Preserve the linearonly component sequence in both formats. */
    public function testLinearOnly(): void
    {
        $curve = new CompoundCurve([
            new LineString([[0, 0], [2, 0]], 4326),
            new LineString([[2, 0], [3, 1]], 4326),
        ], 4326);

        self::assertSame('COMPOUNDCURVE ((0 0, 2 0), (2 0, 3 1))', new WktTextStrategy()->encode($curve));
        self::assertSame('SRID=4326;COMPOUNDCURVE ((0 0, 2 0), (2 0, 3 1))', new EwktTextStrategy()->encode($curve));
    }

    /** Preserve the m component sequence in both formats. */
    public function testM(): void
    {
        $curve = new CompoundCurveM([
            new LineStringM([[0, 0, 6], [2, 0, 7]], 4326),
            new CircularStringM([[2, 0, 7], [3, 1, 8], [4, 0, 9]], 4326),
        ], 4326);

        self::assertSame('COMPOUNDCURVE M ((0 0 6, 2 0 7), CIRCULARSTRING M (2 0 7, 3 1 8, 4 0 9))', new WktTextStrategy()->encode($curve));
        self::assertSame('SRID=4326;COMPOUNDCURVE M ((0 0 6, 2 0 7), CIRCULARSTRING M (2 0 7, 3 1 8, 4 0 9))', new EwktTextStrategy()->encode($curve));
    }

    /** Preserve the mixedopen component sequence in both formats. */
    public function testMixedOpen(): void
    {
        $curve = new CompoundCurve([
            new LineString([[0, 0], [2, 0]], 4326),
            new CircularString([[2, 0], [3, 1], [4, 0]], 4326),
            new LineString([[4, 0], [5, -1]], 4326),
        ], 4326);

        self::assertSame('COMPOUNDCURVE ((0 0, 2 0), CIRCULARSTRING (2 0, 3 1, 4 0), (4 0, 5 -1))', new WktTextStrategy()->encode($curve));
        self::assertSame('SRID=4326;COMPOUNDCURVE ((0 0, 2 0), CIRCULARSTRING (2 0, 3 1, 4 0), (4 0, 5 -1))', new EwktTextStrategy()->encode($curve));
    }

    /** Preserve the z component sequence in both formats. */
    public function testZ(): void
    {
        $curve = new CompoundCurveZ([
            new LineStringZ([[0, 0, 3], [2, 0, 4]], 4326),
            new CircularStringZ([[2, 0, 4], [3, 1, 5], [4, 0, 6]], 4326),
        ], 4326);

        self::assertSame('COMPOUNDCURVE Z ((0 0 3, 2 0 4), CIRCULARSTRING Z (2 0 4, 3 1 5, 4 0 6))', new WktTextStrategy()->encode($curve));
        self::assertSame('SRID=4326;COMPOUNDCURVE Z ((0 0 3, 2 0 4), CIRCULARSTRING Z (2 0 4, 3 1 5, 4 0 6))', new EwktTextStrategy()->encode($curve));
    }

    /** SRID zero leaves the EWKT prefix absent. */
    public function testZeroSrid(): void
    {
        $curve = new CompoundCurve([new LineString([[0, 0], [2, 0]], 0)], 0);

        self::assertSame('COMPOUNDCURVE ((0 0, 2 0))', new EwktTextStrategy()->encode($curve));
        self::assertSame('COMPOUNDCURVE EMPTY', new EwktTextStrategy()->encode(new CompoundCurve([], 0)));
    }

    /** Preserve the zm component sequence in both formats. */
    public function testZm(): void
    {
        $curve = new CompoundCurveZm([
            new LineStringZm([[0, 0, 3, 6], [2, 0, 4, 7]], 4326),
            new CircularStringZm([[2, 0, 4, 7], [3, 1, 5, 8], [4, 0, 6, 9]], 4326),
        ], 4326);

        self::assertSame('COMPOUNDCURVE ZM ((0 0 3 6, 2 0 4 7), CIRCULARSTRING ZM (2 0 4 7, 3 1 5 8, 4 0 6 9))', new WktTextStrategy()->encode($curve));
        self::assertSame('SRID=4326;COMPOUNDCURVE ZM ((0 0 3 6, 2 0 4 7), CIRCULARSTRING ZM (2 0 4 7, 3 1 5 8, 4 0 6 9))', new EwktTextStrategy()->encode($curve));
    }
}
