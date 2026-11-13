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
use LongitudeOne\SpatialTypes\Types\Dimension3m\Geography\GeographyCollectionImmutable as GeographyCollection;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Geography\LineStringImmutable as LineString;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Geography\MultiLineStringImmutable as MultiLineString;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Geography\MultiPointImmutable as MultiPoint;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Geography\MultiPolygonImmutable as MultiPolygon;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Geography\PointImmutable as Point;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Geography\PolygonImmutable as Polygon;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Geography\TriangleImmutable as Triangle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Explicit EWKB examples for Geography objects with a measure ordinate (XYM).
 *
 * To create a new test and check the EWKB, you can use the following SQL query in PostGIS:
 * SELECT encode(ST_AsEWKB(ST_GeomFromText('POINT M (1 2 3)', 4326), 'NDR'), 'hex');
 *
 * @internal
 */
#[CoversClass(EwkbBinaryStrategy::class)]
#[CoversClass(EwkbTypeEncoder::class)]
class GeographyXymTest extends TestCase
{
    /** Write a geographic collection using the GEOMETRYCOLLECTION keyword. */
    public function testGeographyCollection(): void
    {
        $collection = new GeographyCollection(4326, [
            new Point(1, 2, 10, 4326),
            new LineString([[0, 0, 20], [2, 3, 30]], 4326),
        ]);

        // Reference geometry in WKT: GEOMETRYCOLLECTION M (POINT M (1 2 10), LINESTRING M (0 0 20, 2 3 30))
        self::assertSame(
            '0107000060e6100000020000000101000040000000000000f03f00000000000000400000000000002440010200004002000000000000000000000000000000000000000000000000003440000000000000004000000000000008400000000000003e40',
            bin2hex(new EwkbBinaryStrategy()->encode($collection))
        );
    }

    /** Preserve the type and dimension of an empty GeographyCollection. */
    public function testGeographyCollectionEmpty(): void
    {
        $collection = new GeographyCollection(4326, []);

        // Reference geometry in WKT: GEOMETRYCOLLECTION M EMPTY
        self::assertSame(
            '0107000060e610000000000000',
            bin2hex(new EwkbBinaryStrategy()->encode($collection))
        );
    }

    /** Write three points in their original order. */
    public function testLineString(): void
    {
        $line = new LineString([[0, 0, 10], [2, 3, 20], [4, 1, 30]], 4326);

        // Reference geometry in WKT: LINESTRING M (0 0 10, 2 3 20, 4 1 30)
        self::assertSame(
            '0102000060e6100000030000000000000000000000000000000000000000000000000024400000000000000040000000000000084000000000000034400000000000001040000000000000f03f0000000000003e40',
            bin2hex(new EwkbBinaryStrategy()->encode($line))
        );
    }

    /** Preserve the type and dimension of an empty LineString. */
    public function testLineStringEmpty(): void
    {
        $line = new LineString([], 4326);

        // Reference geometry in WKT: LINESTRING M EMPTY
        self::assertSame(
            '0102000060e610000000000000',
            bin2hex(new EwkbBinaryStrategy()->encode($line))
        );
    }

    /** Write two lines in their original order. */
    public function testMultiLineString(): void
    {
        $lines = new MultiLineString([
            [[0, 0, 10], [2, 3, 20]],
            [[4, 1, 30], [5, 2, 40]],
        ], 4326);

        // Reference geometry in WKT: MULTILINESTRING M ((0 0 10, 2 3 20), (4 1 30, 5 2 40))
        self::assertSame(
            '0105000060e6100000020000000102000040020000000000000000000000000000000000000000000000000024400000000000000040000000000000084000000000000034400102000040020000000000000000001040000000000000f03f0000000000003e40000000000000144000000000000000400000000000004440',
            bin2hex(new EwkbBinaryStrategy()->encode($lines))
        );
    }

    /** Preserve the type and dimension of an empty MultiLineString. */
    public function testMultiLineStringEmpty(): void
    {
        $lines = new MultiLineString([], 4326);

        // Reference geometry in WKT: MULTILINESTRING M EMPTY
        self::assertSame(
            '0105000060e610000000000000',
            bin2hex(new EwkbBinaryStrategy()->encode($lines))
        );
    }

    /** Write two distinct points with their own EWKB headers. */
    public function testMultiPoint(): void
    {
        $points = new MultiPoint([[1, 2, 10], [3, 4, 20]], 4326);

        // Reference geometry in WKT: MULTIPOINT M ((1 2 10), (3 4 20))
        self::assertSame(
            '0104000060e6100000020000000101000040000000000000f03f000000000000004000000000000024400101000040000000000000084000000000000010400000000000003440',
            bin2hex(new EwkbBinaryStrategy()->encode($points))
        );
    }

    /** Preserve the type and dimension of an empty MultiPoint. */
    public function testMultiPointEmpty(): void
    {
        $points = new MultiPoint([], 4326);

        // Reference geometry in WKT: MULTIPOINT M EMPTY
        self::assertSame(
            '0104000060e610000000000000',
            bin2hex(new EwkbBinaryStrategy()->encode($points))
        );
    }

    /** Write two polygons with their own closed exterior rings. */
    public function testMultiPolygon(): void
    {
        $polygons = new MultiPolygon([
            [[[0, 0, 10], [2, 0, 10], [0, 2, 10], [0, 0, 10]]],
            [[[3, 3, 20], [5, 3, 20], [3, 5, 20], [3, 3, 20]]],
        ], 4326);

        // Reference geometry in WKT: MULTIPOLYGON M (((0 0 10, 2 0 10, 0 2 10, 0 0 10)), ((3 3 20, 5 3 20, 3 5 20, 3 3 20)))
        self::assertSame(
            '0106000060e6100000020000000103000040010000000400000000000000000000000000000000000000000000000000244000000000000000400000000000000000000000000000244000000000000000000000000000000040000000000000244000000000000000000000000000000000000000000000244001030000400100000004000000000000000000084000000000000008400000000000003440000000000000144000000000000008400000000000003440000000000000084000000000000014400000000000003440000000000000084000000000000008400000000000003440',
            bin2hex(new EwkbBinaryStrategy()->encode($polygons))
        );
    }

    /** Preserve the type and dimension of an empty MultiPolygon. */
    public function testMultiPolygonEmpty(): void
    {
        $polygons = new MultiPolygon([], 4326);

        // Reference geometry in WKT: MULTIPOLYGON M EMPTY
        self::assertSame(
            '0106000060e610000000000000',
            bin2hex(new EwkbBinaryStrategy()->encode($polygons))
        );
    }

    /** Write a point with fractional and negative ordinates. */
    public function testPoint(): void
    {
        $point = new Point(2.5, -3, 10, 4326);

        // Reference geometry in WKT: POINT M (2.5 -3 10)
        self::assertSame(
            '0101000060e6100000000000000000044000000000000008c00000000000002440',
            bin2hex(new EwkbBinaryStrategy()->encode($point))
        );
    }

    /** Preserve the type and dimension of an empty Point. */
    public function testPointEmpty(): void
    {
        $point = new Point(srid: 4326);

        // Reference geometry in WKT: POINT M EMPTY
        self::assertSame(
            '0101000060e6100000000000000000f87f000000000000f87f000000000000f87f',
            bin2hex(new EwkbBinaryStrategy()->encode($point))
        );
    }

    /** Write an exterior ring followed by an interior hole. */
    public function testPolygon(): void
    {
        $polygon = new Polygon([
            [[0, 0, 10], [4, 0, 10], [4, 4, 10], [0, 4, 10], [0, 0, 10]],
            [[1, 1, 10], [1, 2, 10], [2, 2, 10], [2, 1, 10], [1, 1, 10]],
        ], 4326);

        // Reference geometry in WKT: POLYGON M ((0 0 10, 4 0 10, 4 4 10, 0 4 10, 0 0 10), (1 1 10, 1 2 10, 2 2 10, 2 1 10, 1 1 10))
        self::assertSame(
            '0103000060e6100000020000000500000000000000000000000000000000000000000000000000244000000000000010400000000000000000000000000000244000000000000010400000000000001040000000000000244000000000000000000000000000001040000000000000244000000000000000000000000000000000000000000000244005000000000000000000f03f000000000000f03f0000000000002440000000000000f03f000000000000004000000000000024400000000000000040000000000000004000000000000024400000000000000040000000000000f03f0000000000002440000000000000f03f000000000000f03f0000000000002440',
            bin2hex(new EwkbBinaryStrategy()->encode($polygon))
        );
    }

    /** Preserve the type and dimension of an empty Polygon. */
    public function testPolygonEmpty(): void
    {
        $polygon = new Polygon([], 4326);

        // Reference geometry in WKT: POLYGON M EMPTY
        self::assertSame(
            '0103000060e610000000000000',
            bin2hex(new EwkbBinaryStrategy()->encode($polygon))
        );
    }

    /** Write three vertices and the closing position. */
    public function testTriangle(): void
    {
        $triangle = new Triangle([[[0, 0, 10], [2, 0, 10], [0, 2, 10], [0, 0, 10]]], 4326);

        // Reference geometry in WKT: TRIANGLE M ((0 0 10, 2 0 10, 0 2 10, 0 0 10))
        self::assertSame(
            '0111000060e61000000100000004000000000000000000000000000000000000000000000000002440000000000000004000000000000000000000000000002440000000000000000000000000000000400000000000002440000000000000000000000000000000000000000000002440',
            bin2hex(new EwkbBinaryStrategy()->encode($triangle))
        );
    }

    /** Preserve the type and dimension of an empty Triangle. */
    public function testTriangleEmpty(): void
    {
        $triangle = new Triangle([], 4326);

        // Reference geometry in WKT: TRIANGLE M EMPTY
        self::assertSame(
            '0111000060e610000000000000',
            bin2hex(new EwkbBinaryStrategy()->encode($triangle))
        );
    }
}
