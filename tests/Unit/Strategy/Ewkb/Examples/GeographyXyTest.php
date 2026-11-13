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

namespace LongitudeOne\SpatialEncoder\Tests\Unit\Strategy\Ewkb\Examples;

use LongitudeOne\SpatialEncoder\Strategy\Ewkb\EwkbTypeEncoder;
use LongitudeOne\SpatialEncoder\Strategy\EwkbBinaryStrategy;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\GeographyCollectionImmutable as GeographyCollection;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\LineStringImmutable as LineString;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\MultiLineStringImmutable as MultiLineString;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\MultiPointImmutable as MultiPoint;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\MultiPolygonImmutable as MultiPolygon;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\PointImmutable as Point;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\PolygonImmutable as Polygon;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\TriangleImmutable as Triangle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Explicit EWKB examples for two-dimensional Geography objects.
 *
 * @internal
 */
#[CoversClass(EwkbBinaryStrategy::class)]
#[CoversClass(EwkbTypeEncoder::class)]
class GeographyXyTest extends TestCase
{
    /** Write a geographic collection using the GEOMETRYCOLLECTION keyword. */
    public function testGeographyCollection(): void
    {
        $collection = new GeographyCollection(4326, [
            new Point(1, 2, 4326),
            new LineString([[0, 0], [2, 3]], 4326),
        ]);

        // Reference geometry in WKT: GEOMETRYCOLLECTION (POINT (1 2), LINESTRING (0 0, 2 3))
        self::assertSame(
            '0107000020e6100000020000000101000000000000000000f03f00000000000000400102000000020000000000000000000000000000000000000000000000000000400000000000000840',
            bin2hex(new EwkbBinaryStrategy()->encode($collection))
        );
    }

    /** Preserve the type and dimension of an empty GeographyCollection. */
    public function testGeographyCollectionEmpty(): void
    {
        $collection = new GeographyCollection(4326, []);

        // Reference geometry in WKT: GEOMETRYCOLLECTION EMPTY
        self::assertSame(
            '0107000020e610000000000000',
            bin2hex(new EwkbBinaryStrategy()->encode($collection))
        );
    }

    /** Write three points in their original order. */
    public function testLineString(): void
    {
        $line = new LineString([[0, 0], [2, 3], [4, 1]], 4326);

        // Reference geometry in WKT: LINESTRING (0 0, 2 3, 4 1)
        self::assertSame(
            '0102000020e61000000300000000000000000000000000000000000000000000000000004000000000000008400000000000001040000000000000f03f',
            bin2hex(new EwkbBinaryStrategy()->encode($line))
        );
    }

    /** Preserve the type and dimension of an empty LineString. */
    public function testLineStringEmpty(): void
    {
        $line = new LineString([], 4326);

        // Reference geometry in WKT: LINESTRING EMPTY
        self::assertSame(
            '0102000020e610000000000000',
            bin2hex(new EwkbBinaryStrategy()->encode($line))
        );
    }

    /** Write two lines in their original order. */
    public function testMultiLineString(): void
    {
        $lines = new MultiLineString([
            [[0, 0], [2, 3]],
            [[4, 1], [5, 2]],
        ], 4326);

        // Reference geometry in WKT: MULTILINESTRING ((0 0, 2 3), (4 1, 5 2))
        self::assertSame(
            '0105000020e61000000200000001020000000200000000000000000000000000000000000000000000000000004000000000000008400102000000020000000000000000001040000000000000f03f00000000000014400000000000000040',
            bin2hex(new EwkbBinaryStrategy()->encode($lines))
        );
    }

    /** Preserve the type and dimension of an empty MultiLineString. */
    public function testMultiLineStringEmpty(): void
    {
        $lines = new MultiLineString([], 4326);

        // Reference geometry in WKT: MULTILINESTRING EMPTY
        self::assertSame(
            '0105000020e610000000000000',
            bin2hex(new EwkbBinaryStrategy()->encode($lines))
        );
    }

    /** Write two distinct points with their own WKB headers. */
    public function testMultiPoint(): void
    {
        $points = new MultiPoint([[1, 2], [3, 4]], 4326);

        // Reference geometry in WKT: MULTIPOINT ((1 2), (3 4))
        self::assertSame(
            '0104000020e6100000020000000101000000000000000000f03f0000000000000040010100000000000000000008400000000000001040',
            bin2hex(new EwkbBinaryStrategy()->encode($points))
        );
    }

    /** Preserve the type and dimension of an empty MultiPoint. */
    public function testMultiPointEmpty(): void
    {
        $points = new MultiPoint([], 4326);

        // Reference geometry in WKT: MULTIPOINT EMPTY
        self::assertSame(
            '0104000020e610000000000000',
            bin2hex(new EwkbBinaryStrategy()->encode($points))
        );
    }

    /** Write two polygons with their own closed exterior rings. */
    public function testMultiPolygon(): void
    {
        $polygons = new MultiPolygon([
            [[[0, 0], [2, 0], [0, 2], [0, 0]]],
            [[[3, 3], [5, 3], [3, 5], [3, 3]]],
        ], 4326);

        // Reference geometry in WKT: MULTIPOLYGON (((0 0, 2 0, 0 2, 0 0)), ((3 3, 5 3, 3 5, 3 3)))
        self::assertSame(
            '0106000020e61000000200000001030000000100000004000000000000000000000000000000000000000000000000000040000000000000000000000000000000000000000000000040000000000000000000000000000000000103000000010000000400000000000000000008400000000000000840000000000000144000000000000008400000000000000840000000000000144000000000000008400000000000000840',
            bin2hex(new EwkbBinaryStrategy()->encode($polygons))
        );
    }

    /** Preserve the type and dimension of an empty MultiPolygon. */
    public function testMultiPolygonEmpty(): void
    {
        $polygons = new MultiPolygon([], 4326);

        // Reference geometry in WKT: MULTIPOLYGON EMPTY
        self::assertSame(
            '0106000020e610000000000000',
            bin2hex(new EwkbBinaryStrategy()->encode($polygons))
        );
    }

    /** Write a point with fractional and negative ordinates. */
    public function testPoint(): void
    {
        $point = new Point(2.5, -3, 4326);

        // Reference geometry in WKT: POINT (2.5 -3)
        self::assertSame(
            '0101000020e6100000000000000000044000000000000008c0',
            bin2hex(new EwkbBinaryStrategy()->encode($point))
        );
    }

    /** Preserve the type and dimension of an empty Point. */
    public function testPointEmpty(): void
    {
        $point = new Point(srid: 4326);

        // Reference geometry in WKT: POINT EMPTY
        self::assertSame(
            '0101000020e6100000000000000000f87f000000000000f87f',
            bin2hex(new EwkbBinaryStrategy()->encode($point))
        );
    }

    /** Write an exterior ring followed by an interior hole. */
    public function testPolygon(): void
    {
        $polygon = new Polygon([
            [[0, 0], [4, 0], [4, 4], [0, 4], [0, 0]],
            [[1, 1], [1, 2], [2, 2], [2, 1], [1, 1]],
        ], 4326);

        // Reference geometry in WKT: POLYGON ((0 0, 4 0, 4 4, 0 4, 0 0), (1 1, 1 2, 2 2, 2 1, 1 1))
        self::assertSame(
            '0103000020e61000000200000005000000000000000000000000000000000000000000000000001040000000000000000000000000000010400000000000001040000000000000000000000000000010400000000000000000000000000000000005000000000000000000f03f000000000000f03f000000000000f03f0000000000000040000000000000004000000000000000400000000000000040000000000000f03f000000000000f03f000000000000f03f',
            bin2hex(new EwkbBinaryStrategy()->encode($polygon))
        );
    }

    /** Preserve the type and dimension of an empty Polygon. */
    public function testPolygonEmpty(): void
    {
        $polygon = new Polygon([], 4326);

        // Reference geometry in WKT: POLYGON EMPTY
        self::assertSame(
            '0103000020e610000000000000',
            bin2hex(new EwkbBinaryStrategy()->encode($polygon))
        );
    }

    /** Write three vertices and the closing position. */
    public function testTriangle(): void
    {
        $triangle = new Triangle([[[0, 0], [2, 0], [0, 2], [0, 0]]], 4326);

        // Reference geometry in WKT: TRIANGLE ((0 0, 2 0, 0 2, 0 0))
        self::assertSame(
            '0111000020e6100000010000000400000000000000000000000000000000000000000000000000004000000000000000000000000000000000000000000000004000000000000000000000000000000000',
            bin2hex(new EwkbBinaryStrategy()->encode($triangle))
        );
    }

    /** Preserve the type and dimension of an empty Triangle. */
    public function testTriangleEmpty(): void
    {
        $triangle = new Triangle([], 4326);

        // Reference geometry in WKT: TRIANGLE EMPTY
        self::assertSame(
            '0111000020e610000000000000',
            bin2hex(new EwkbBinaryStrategy()->encode($triangle))
        );
    }
}
