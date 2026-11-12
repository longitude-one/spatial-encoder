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

namespace LongitudeOne\SpatialEncoder\Tests\Unit\Strategy\Wkt\Examples;

use LongitudeOne\SpatialEncoder\Strategy\Wkt\WktTypeEncoder;
use LongitudeOne\SpatialEncoder\Strategy\WktTextStrategy;
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
 * Explicit WKT examples for Geography objects with a measure ordinate (XYM).
 *
 * @internal
 */
#[CoversClass(WktTextStrategy::class)]
#[CoversClass(WktTypeEncoder::class)]
class GeographyXymTest extends TestCase
{
    /** Write a geographic collection using the GEOMETRYCOLLECTION keyword. */
    public function testGeographyCollection(): void
    {
        $collection = new GeographyCollection(4326, [
            new Point(1, 2, 10, 4326),
            new LineString([[0, 0, 20], [2, 3, 30]], 4326),
        ]);

        self::assertSame(
            'GEOMETRYCOLLECTION M (POINT M (1 2 10), LINESTRING M (0 0 20, 2 3 30))',
            new WktTextStrategy()->encode($collection)
        );
    }

    /** Preserve the type and dimension of an empty GeographyCollection. */
    public function testGeographyCollectionEmpty(): void
    {
        $collection = new GeographyCollection(4326, []);

        self::assertSame('GEOMETRYCOLLECTION M EMPTY', new WktTextStrategy()->encode($collection));
    }

    /** Write three points in their original order. */
    public function testLineString(): void
    {
        $line = new LineString([[0, 0, 10], [2, 3, 20], [4, 1, 30]], 4326);

        self::assertSame('LINESTRING M (0 0 10, 2 3 20, 4 1 30)', new WktTextStrategy()->encode($line));
    }

    /** Preserve the type and dimension of an empty LineString. */
    public function testLineStringEmpty(): void
    {
        $line = new LineString([], 4326);

        self::assertSame('LINESTRING M EMPTY', new WktTextStrategy()->encode($line));
    }

    /** Write two lines in their original order. */
    public function testMultiLineString(): void
    {
        $lines = new MultiLineString([
            [[0, 0, 10], [2, 3, 20]],
            [[4, 1, 30], [5, 2, 40]],
        ], 4326);

        self::assertSame('MULTILINESTRING M ((0 0 10, 2 3 20), (4 1 30, 5 2 40))', new WktTextStrategy()->encode($lines));
    }

    /** Preserve the type and dimension of an empty MultiLineString. */
    public function testMultiLineStringEmpty(): void
    {
        $lines = new MultiLineString([], 4326);

        self::assertSame('MULTILINESTRING M EMPTY', new WktTextStrategy()->encode($lines));
    }

    /** Write two distinct points with separate parentheses. */
    public function testMultiPoint(): void
    {
        $points = new MultiPoint([[1, 2, 10], [3, 4, 20]], 4326);

        self::assertSame('MULTIPOINT M ((1 2 10), (3 4 20))', new WktTextStrategy()->encode($points));
    }

    /** Preserve the type and dimension of an empty MultiPoint. */
    public function testMultiPointEmpty(): void
    {
        $points = new MultiPoint([], 4326);

        self::assertSame('MULTIPOINT M EMPTY', new WktTextStrategy()->encode($points));
    }

    /** Write two polygons with their own closed exterior rings. */
    public function testMultiPolygon(): void
    {
        $polygons = new MultiPolygon([
            [[[0, 0, 10], [2, 0, 10], [0, 2, 10], [0, 0, 10]]],
            [[[3, 3, 20], [5, 3, 20], [3, 5, 20], [3, 3, 20]]],
        ], 4326);

        self::assertSame(
            'MULTIPOLYGON M (((0 0 10, 2 0 10, 0 2 10, 0 0 10)), ((3 3 20, 5 3 20, 3 5 20, 3 3 20)))',
            new WktTextStrategy()->encode($polygons)
        );
    }

    /** Preserve the type and dimension of an empty MultiPolygon. */
    public function testMultiPolygonEmpty(): void
    {
        $polygons = new MultiPolygon([], 4326);

        self::assertSame('MULTIPOLYGON M EMPTY', new WktTextStrategy()->encode($polygons));
    }

    /** Write a point with fractional and negative ordinates. */
    public function testPoint(): void
    {
        $point = new Point(2.5, -3, 10, 4326);

        self::assertSame('POINT M (2.5 -3 10)', new WktTextStrategy()->encode($point));
    }

    /** Preserve the type and dimension of an empty Point. */
    public function testPointEmpty(): void
    {
        $point = new Point(srid: 4326);

        self::assertSame('POINT M EMPTY', new WktTextStrategy()->encode($point));
    }

    /** Write an exterior ring followed by an interior hole. */
    public function testPolygon(): void
    {
        $polygon = new Polygon([
            [[0, 0, 10], [4, 0, 10], [4, 4, 10], [0, 4, 10], [0, 0, 10]],
            [[1, 1, 10], [1, 2, 10], [2, 2, 10], [2, 1, 10], [1, 1, 10]],
        ], 4326);

        self::assertSame(
            'POLYGON M ((0 0 10, 4 0 10, 4 4 10, 0 4 10, 0 0 10), (1 1 10, 1 2 10, 2 2 10, 2 1 10, 1 1 10))',
            new WktTextStrategy()->encode($polygon)
        );
    }

    /** Preserve the type and dimension of an empty Polygon. */
    public function testPolygonEmpty(): void
    {
        $polygon = new Polygon([], 4326);

        self::assertSame('POLYGON M EMPTY', new WktTextStrategy()->encode($polygon));
    }

    /** Write three vertices and the closing position. */
    public function testTriangle(): void
    {
        $triangle = new Triangle([[[0, 0, 10], [2, 0, 10], [0, 2, 10], [0, 0, 10]]], 4326);

        self::assertSame('TRIANGLE M ((0 0 10, 2 0 10, 0 2 10, 0 0 10))', new WktTextStrategy()->encode($triangle));
    }

    /** Preserve the type and dimension of an empty Triangle. */
    public function testTriangleEmpty(): void
    {
        $triangle = new Triangle([], 4326);

        self::assertSame('TRIANGLE M EMPTY', new WktTextStrategy()->encode($triangle));
    }
}
