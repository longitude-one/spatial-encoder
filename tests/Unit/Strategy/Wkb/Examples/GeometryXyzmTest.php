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

namespace LongitudeOne\SpatialEncoder\Tests\Unit\Strategy\Wkb\Examples;

use LongitudeOne\SpatialEncoder\Strategy\Wkb\WkbTypeEncoder;
use LongitudeOne\SpatialEncoder\Strategy\WkbBinaryStrategy;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\LineString;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\MultiLineString;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\MultiPoint;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\MultiPolygon;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Point;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Polygon;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\PolyhedralSurface;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\SpatialCollection;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Triangle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Explicit ISO WKB examples for Geometry objects with elevation and measure (XYZM).
 *
 * @internal
 */
#[CoversClass(WkbBinaryStrategy::class)]
#[CoversClass(WkbTypeEncoder::class)]
class GeometryXyzmTest extends TestCase
{
    /** Write a collection containing a point and a line. */
    public function testGeometryCollection(): void
    {
        $collection = new SpatialCollection([
            new Point(1, 2, 5, 10, 4326),
            new LineString([[0, 0, 6, 20], [2, 3, 7, 30]], 4326),
        ], 4326);

        // Reference geometry in WKT: GEOMETRYCOLLECTION ZM (POINT ZM (1 2 5 10), LINESTRING ZM (0 0 6 20, 2 3 7 30))
        self::assertSame(
            '01bf0b00000200000001b90b0000000000000000f03f00000000000000400000000000001440000000000000244001ba0b0000020000000000000000000000000000000000000000000000000018400000000000003440000000000000004000000000000008400000000000001c400000000000003e40',
            bin2hex(new WkbBinaryStrategy()->encode($collection))
        );
    }

    /** Preserve the type and dimension of an empty GeometryCollection. */
    public function testGeometryCollectionEmpty(): void
    {
        $collection = new SpatialCollection([], 4326);

        // Reference geometry in WKT: GEOMETRYCOLLECTION ZM EMPTY
        self::assertSame(
            '01bf0b000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($collection))
        );
    }

    /** Write three points in their original order. */
    public function testLineString(): void
    {
        $line = new LineString([[0, 0, 5, 10], [2, 3, 6, 20], [4, 1, 7, 30]], 4326);

        // Reference geometry in WKT: LINESTRING ZM (0 0 5 10, 2 3 6 20, 4 1 7 30)
        self::assertSame(
            '01ba0b000003000000000000000000000000000000000000000000000000001440000000000000244000000000000000400000000000000840000000000000184000000000000034400000000000001040000000000000f03f0000000000001c400000000000003e40',
            bin2hex(new WkbBinaryStrategy()->encode($line))
        );
    }

    /** Preserve the type and dimension of an empty LineString. */
    public function testLineStringEmpty(): void
    {
        $line = new LineString([], 4326);

        // Reference geometry in WKT: LINESTRING ZM EMPTY
        self::assertSame(
            '01ba0b000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($line))
        );
    }

    /** Write two lines in their original order. */
    public function testMultiLineString(): void
    {
        $lines = new MultiLineString([
            [[0, 0, 5, 10], [2, 3, 6, 20]],
            [[4, 1, 7, 30], [5, 2, 8, 40]],
        ], 4326);

        // Reference geometry in WKT: MULTILINESTRING ZM ((0 0 5 10, 2 3 6 20), (4 1 7 30, 5 2 8 40))
        self::assertSame(
            '01bd0b00000200000001ba0b0000020000000000000000000000000000000000000000000000000014400000000000002440000000000000004000000000000008400000000000001840000000000000344001ba0b0000020000000000000000001040000000000000f03f0000000000001c400000000000003e400000000000001440000000000000004000000000000020400000000000004440',
            bin2hex(new WkbBinaryStrategy()->encode($lines))
        );
    }

    /** Preserve the type and dimension of an empty MultiLineString. */
    public function testMultiLineStringEmpty(): void
    {
        $lines = new MultiLineString([], 4326);

        // Reference geometry in WKT: MULTILINESTRING ZM EMPTY
        self::assertSame(
            '01bd0b000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($lines))
        );
    }

    /** Write two distinct points with their own WKB headers. */
    public function testMultiPoint(): void
    {
        $points = new MultiPoint([[1, 2, 5, 10], [3, 4, 6, 20]], 4326);

        // Reference geometry in WKT: MULTIPOINT ZM ((1 2 5 10), (3 4 6 20))
        self::assertSame(
            '01bc0b00000200000001b90b0000000000000000f03f00000000000000400000000000001440000000000000244001b90b00000000000000000840000000000000104000000000000018400000000000003440',
            bin2hex(new WkbBinaryStrategy()->encode($points))
        );
    }

    /** Preserve the type and dimension of an empty MultiPoint. */
    public function testMultiPointEmpty(): void
    {
        $points = new MultiPoint([], 4326);

        // Reference geometry in WKT: MULTIPOINT ZM EMPTY
        self::assertSame(
            '01bc0b000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($points))
        );
    }

    /** Write two polygons with their own closed exterior rings. */
    public function testMultiPolygon(): void
    {
        $polygons = new MultiPolygon([
            [[[0, 0, 5, 10], [2, 0, 5, 10], [0, 2, 5, 10], [0, 0, 5, 10]]],
            [[[3, 3, 6, 20], [5, 3, 6, 20], [3, 5, 6, 20], [3, 3, 6, 20]]],
        ], 4326);

        // Reference geometry in WKT: MULTIPOLYGON ZM (((0 0 5 10, 2 0 5 10, 0 2 5 10, 0 0 5 10)), ((3 3 6 20, 5 3 6 20, 3 5 6 20, 3 3 6 20)))
        self::assertSame(
            '01be0b00000200000001bb0b00000100000004000000000000000000000000000000000000000000000000001440000000000000244000000000000000400000000000000000000000000000144000000000000024400000000000000000000000000000004000000000000014400000000000002440000000000000000000000000000000000000000000001440000000000000244001bb0b000001000000040000000000000000000840000000000000084000000000000018400000000000003440000000000000144000000000000008400000000000001840000000000000344000000000000008400000000000001440000000000000184000000000000034400000000000000840000000000000084000000000000018400000000000003440',
            bin2hex(new WkbBinaryStrategy()->encode($polygons))
        );
    }

    /** Preserve the type and dimension of an empty MultiPolygon. */
    public function testMultiPolygonEmpty(): void
    {
        $polygons = new MultiPolygon([], 4326);

        // Reference geometry in WKT: MULTIPOLYGON ZM EMPTY
        self::assertSame(
            '01be0b000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($polygons))
        );
    }

    /** Write a point with fractional and negative ordinates. */
    public function testPoint(): void
    {
        $point = new Point(2.5, -3, 5, 10, 4326);

        // Reference geometry in WKT: POINT ZM (2.5 -3 5 10)
        self::assertSame(
            '01b90b0000000000000000044000000000000008c000000000000014400000000000002440',
            bin2hex(new WkbBinaryStrategy()->encode($point))
        );
    }

    /** Preserve the type and dimension of an empty Point. */
    public function testPointEmpty(): void
    {
        $point = new Point(srid: 4326);

        // Reference geometry in WKT: POINT ZM EMPTY
        self::assertSame(
            '01b90b0000000000000000f87f000000000000f87f000000000000f87f000000000000f87f',
            bin2hex(new WkbBinaryStrategy()->encode($point))
        );
    }

    /** Write an exterior ring followed by an interior hole. */
    public function testPolygon(): void
    {
        $polygon = new Polygon([
            [[0, 0, 5, 10], [4, 0, 5, 10], [4, 4, 5, 10], [0, 4, 5, 10], [0, 0, 5, 10]],
            [[1, 1, 5, 10], [1, 2, 5, 10], [2, 2, 5, 10], [2, 1, 5, 10], [1, 1, 5, 10]],
        ], 4326);

        // Reference geometry in WKT: POLYGON ZM ((0 0 5 10, 4 0 5 10, 4 4 5 10, 0 4 5 10, 0 0 5 10), (1 1 5 10, 1 2 5 10, 2 2 5 10, 2 1 5 10, 1 1 5 10))
        self::assertSame(
            '01bb0b000002000000050000000000000000000000000000000000000000000000000014400000000000002440000000000000104000000000000000000000000000001440000000000000244000000000000010400000000000001040000000000000144000000000000024400000000000000000000000000000104000000000000014400000000000002440000000000000000000000000000000000000000000001440000000000000244005000000000000000000f03f000000000000f03f00000000000014400000000000002440000000000000f03f00000000000000400000000000001440000000000000244000000000000000400000000000000040000000000000144000000000000024400000000000000040000000000000f03f00000000000014400000000000002440000000000000f03f000000000000f03f00000000000014400000000000002440',
            bin2hex(new WkbBinaryStrategy()->encode($polygon))
        );
    }

    /** Preserve the type and dimension of an empty Polygon. */
    public function testPolygonEmpty(): void
    {
        $polygon = new Polygon([], 4326);

        // Reference geometry in WKT: POLYGON ZM EMPTY
        self::assertSame(
            '01bb0b000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($polygon))
        );
    }

    /** Write two adjacent polygon patches sharing an edge. */
    public function testPolyhedralSurface(): void
    {
        $surface = new PolyhedralSurface([
            [[[0, 0, 5, 10], [1, 0, 5, 10], [1, 1, 5, 10], [0, 1, 5, 10], [0, 0, 5, 10]]],
            [[[1, 0, 5, 10], [2, 0, 5, 10], [2, 1, 5, 10], [1, 1, 5, 10], [1, 0, 5, 10]]],
        ], 4326);

        // Reference geometry in WKT: POLYHEDRALSURFACE ZM (((0 0 5 10, 1 0 5 10, 1 1 5 10, 0 1 5 10, 0 0 5 10)), ((1 0 5 10, 2 0 5 10, 2 1 5 10, 1 1 5 10, 1 0 5 10)))
        self::assertSame(
            '01c70b00000200000001bb0b000001000000050000000000000000000000000000000000000000000000000014400000000000002440000000000000f03f000000000000000000000000000014400000000000002440000000000000f03f000000000000f03f000000000000144000000000000024400000000000000000000000000000f03f00000000000014400000000000002440000000000000000000000000000000000000000000001440000000000000244001bb0b00000100000005000000000000000000f03f00000000000000000000000000001440000000000000244000000000000000400000000000000000000000000000144000000000000024400000000000000040000000000000f03f00000000000014400000000000002440000000000000f03f000000000000f03f00000000000014400000000000002440000000000000f03f000000000000000000000000000014400000000000002440',
            bin2hex(new WkbBinaryStrategy()->encode($surface))
        );
    }

    /** Preserve the type and dimension of an empty PolyhedralSurface. */
    public function testPolyhedralSurfaceEmpty(): void
    {
        $surface = new PolyhedralSurface([], 4326);

        // Reference geometry in WKT: POLYHEDRALSURFACE ZM EMPTY
        self::assertSame(
            '01c70b000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($surface))
        );
    }

    /** Write three vertices and the closing position. */
    public function testTriangle(): void
    {
        $triangle = new Triangle([[[0, 0, 5, 10], [2, 0, 5, 10], [0, 2, 5, 10], [0, 0, 5, 10]]], 4326);

        // Reference geometry in WKT: TRIANGLE ZM ((0 0 5 10, 2 0 5 10, 0 2 5 10, 0 0 5 10))
        self::assertSame(
            '01c90b000001000000040000000000000000000000000000000000000000000000000014400000000000002440000000000000004000000000000000000000000000001440000000000000244000000000000000000000000000000040000000000000144000000000000024400000000000000000000000000000000000000000000014400000000000002440',
            bin2hex(new WkbBinaryStrategy()->encode($triangle))
        );
    }

    /** Preserve the type and dimension of an empty Triangle. */
    public function testTriangleEmpty(): void
    {
        $triangle = new Triangle([], 4326);

        // Reference geometry in WKT: TRIANGLE ZM EMPTY
        self::assertSame(
            '01c90b000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($triangle))
        );
    }
}
