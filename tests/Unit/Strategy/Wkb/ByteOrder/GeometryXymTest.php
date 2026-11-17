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

namespace LongitudeOne\SpatialEncoder\Tests\Unit\Strategy\Wkb\ByteOrder;

use LongitudeOne\Core\Enum\ByteOrderEnum;
use LongitudeOne\SpatialEncoder\Strategy\Wkb\WkbTypeEncoder;
use LongitudeOne\SpatialEncoder\Strategy\WkbBinaryStrategy;
use LongitudeOne\SpatialTypes\Types\Dimension3m\LineString;
use LongitudeOne\SpatialTypes\Types\Dimension3m\MultiLineString;
use LongitudeOne\SpatialTypes\Types\Dimension3m\MultiPoint;
use LongitudeOne\SpatialTypes\Types\Dimension3m\MultiPolygon;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Point;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Polygon;
use LongitudeOne\SpatialTypes\Types\Dimension3m\SpatialCollection;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Triangle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Literal fixtures for both ISO WKB byte orders for Geometry objects with a measure ordinate (XYM).
 *
 * @internal
 */
#[CoversClass(WkbBinaryStrategy::class)]
#[CoversClass(WkbTypeEncoder::class)]
class GeometryXymTest extends TestCase
{
    /** Write a collection containing a point and a line. */
    public function testGeometryCollection(): void
    {
        $collection = new SpatialCollection([
            new Point(1, 2, 10, 4326),
            new LineString([[0, 0, 20], [2, 3, 30]], 4326),
        ], 4326);

        // Reference geometry in WKT: GEOMETRYCOLLECTION M (POINT M (1 2 10), LINESTRING M (0 0 20, 2 3 30))
        self::assertSame(
            '00000007d70000000200000007d13ff00000000000004000000000000000402400000000000000000007d20000000200000000000000000000000000000000403400000000000040000000000000004008000000000000403e000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($collection))
        );
        self::assertSame(
            '01d70700000200000001d1070000000000000000f03f0000000000000040000000000000244001d207000002000000000000000000000000000000000000000000000000003440000000000000004000000000000008400000000000003e40',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($collection))
        );
    }

    /** Preserve the type and dimension of an empty GeometryCollection. */
    public function testGeometryCollectionEmpty(): void
    {
        $collection = new SpatialCollection([], 4326);

        // Reference geometry in WKT: GEOMETRYCOLLECTION M EMPTY
        self::assertSame(
            '00000007d700000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($collection))
        );
        self::assertSame(
            '01d707000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($collection))
        );
    }

    /** Write three points in their original order. */
    public function testLineString(): void
    {
        $line = new LineString([[0, 0, 10], [2, 3, 20], [4, 1, 30]], 4326);

        // Reference geometry in WKT: LINESTRING M (0 0 10, 2 3 20, 4 1 30)
        self::assertSame(
            '00000007d20000000300000000000000000000000000000000402400000000000040000000000000004008000000000000403400000000000040100000000000003ff0000000000000403e000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($line))
        );
        self::assertSame(
            '01d2070000030000000000000000000000000000000000000000000000000024400000000000000040000000000000084000000000000034400000000000001040000000000000f03f0000000000003e40',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($line))
        );
    }

    /** Preserve the type and dimension of an empty LineString. */
    public function testLineStringEmpty(): void
    {
        $line = new LineString([], 4326);

        // Reference geometry in WKT: LINESTRING M EMPTY
        self::assertSame(
            '00000007d200000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($line))
        );
        self::assertSame(
            '01d207000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($line))
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
            '00000007d50000000200000007d20000000200000000000000000000000000000000402400000000000040000000000000004008000000000000403400000000000000000007d20000000240100000000000003ff0000000000000403e000000000000401400000000000040000000000000004044000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($lines))
        );
        self::assertSame(
            '01d50700000200000001d20700000200000000000000000000000000000000000000000000000000244000000000000000400000000000000840000000000000344001d2070000020000000000000000001040000000000000f03f0000000000003e40000000000000144000000000000000400000000000004440',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($lines))
        );
    }

    /** Preserve the type and dimension of an empty MultiLineString. */
    public function testMultiLineStringEmpty(): void
    {
        $lines = new MultiLineString([], 4326);

        // Reference geometry in WKT: MULTILINESTRING M EMPTY
        self::assertSame(
            '00000007d500000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($lines))
        );
        self::assertSame(
            '01d507000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($lines))
        );
    }

    /** Write two distinct points with their own WKB headers. */
    public function testMultiPoint(): void
    {
        $points = new MultiPoint([[1, 2, 10], [3, 4, 20]], 4326);

        // Reference geometry in WKT: MULTIPOINT M ((1 2 10), (3 4 20))
        self::assertSame(
            '00000007d40000000200000007d13ff00000000000004000000000000000402400000000000000000007d1400800000000000040100000000000004034000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($points))
        );
        self::assertSame(
            '01d40700000200000001d1070000000000000000f03f0000000000000040000000000000244001d1070000000000000000084000000000000010400000000000003440',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($points))
        );
    }

    /** Preserve the type and dimension of an empty MultiPoint. */
    public function testMultiPointEmpty(): void
    {
        $points = new MultiPoint([], 4326);

        // Reference geometry in WKT: MULTIPOINT M EMPTY
        self::assertSame(
            '00000007d400000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($points))
        );
        self::assertSame(
            '01d407000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($points))
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
            '00000007d60000000200000007d3000000010000000400000000000000000000000000000000402400000000000040000000000000000000000000000000402400000000000000000000000000004000000000000000402400000000000000000000000000000000000000000000402400000000000000000007d30000000100000004400800000000000040080000000000004034000000000000401400000000000040080000000000004034000000000000400800000000000040140000000000004034000000000000400800000000000040080000000000004034000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($polygons))
        );
        self::assertSame(
            '01d60700000200000001d3070000010000000400000000000000000000000000000000000000000000000000244000000000000000400000000000000000000000000000244000000000000000000000000000000040000000000000244000000000000000000000000000000000000000000000244001d30700000100000004000000000000000000084000000000000008400000000000003440000000000000144000000000000008400000000000003440000000000000084000000000000014400000000000003440000000000000084000000000000008400000000000003440',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($polygons))
        );
    }

    /** Preserve the type and dimension of an empty MultiPolygon. */
    public function testMultiPolygonEmpty(): void
    {
        $polygons = new MultiPolygon([], 4326);

        // Reference geometry in WKT: MULTIPOLYGON M EMPTY
        self::assertSame(
            '00000007d600000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($polygons))
        );
        self::assertSame(
            '01d607000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($polygons))
        );
    }

    /** Write a point with fractional and negative ordinates. */
    public function testPoint(): void
    {
        $point = new Point(2.5, -3, 10, 4326);

        // Reference geometry in WKT: POINT M (2.5 -3 10)
        self::assertSame(
            '00000007d14004000000000000c0080000000000004024000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($point))
        );
        self::assertSame(
            '01d1070000000000000000044000000000000008c00000000000002440',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($point))
        );
    }

    /** Preserve the type and dimension of an empty Point. */
    public function testPointEmpty(): void
    {
        $point = new Point(srid: 4326);

        // Reference geometry in WKT: POINT M EMPTY
        self::assertSame(
            '00000007d17ff80000000000007ff80000000000007ff8000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($point))
        );
        self::assertSame(
            '01d1070000000000000000f87f000000000000f87f000000000000f87f',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($point))
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
            '00000007d30000000200000005000000000000000000000000000000004024000000000000401000000000000000000000000000004024000000000000401000000000000040100000000000004024000000000000000000000000000040100000000000004024000000000000000000000000000000000000000000004024000000000000000000053ff00000000000003ff000000000000040240000000000003ff00000000000004000000000000000402400000000000040000000000000004000000000000000402400000000000040000000000000003ff000000000000040240000000000003ff00000000000003ff00000000000004024000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($polygon))
        );
        self::assertSame(
            '01d3070000020000000500000000000000000000000000000000000000000000000000244000000000000010400000000000000000000000000000244000000000000010400000000000001040000000000000244000000000000000000000000000001040000000000000244000000000000000000000000000000000000000000000244005000000000000000000f03f000000000000f03f0000000000002440000000000000f03f000000000000004000000000000024400000000000000040000000000000004000000000000024400000000000000040000000000000f03f0000000000002440000000000000f03f000000000000f03f0000000000002440',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($polygon))
        );
    }

    /** Preserve the type and dimension of an empty Polygon. */
    public function testPolygonEmpty(): void
    {
        $polygon = new Polygon([], 4326);

        // Reference geometry in WKT: POLYGON M EMPTY
        self::assertSame(
            '00000007d300000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($polygon))
        );
        self::assertSame(
            '01d307000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($polygon))
        );
    }

    /** Write three vertices and the closing position. */
    public function testTriangle(): void
    {
        $triangle = new Triangle([[[0, 0, 10], [2, 0, 10], [0, 2, 10], [0, 0, 10]]], 4326);

        // Reference geometry in WKT: TRIANGLE M ((0 0 10, 2 0 10, 0 2 10, 0 0 10))
        self::assertSame(
            '00000007e10000000100000004000000000000000000000000000000004024000000000000400000000000000000000000000000004024000000000000000000000000000040000000000000004024000000000000000000000000000000000000000000004024000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($triangle))
        );
        self::assertSame(
            '01e10700000100000004000000000000000000000000000000000000000000000000002440000000000000004000000000000000000000000000002440000000000000000000000000000000400000000000002440000000000000000000000000000000000000000000002440',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($triangle))
        );
    }

    /** Preserve the type and dimension of an empty Triangle. */
    public function testTriangleEmpty(): void
    {
        $triangle = new Triangle([], 4326);

        // Reference geometry in WKT: TRIANGLE M EMPTY
        self::assertSame(
            '00000007e100000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($triangle))
        );
        self::assertSame(
            '01e107000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($triangle))
        );
    }
}
