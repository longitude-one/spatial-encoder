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

use LongitudeOne\Core\Enum\SpatialModelEnum;
use LongitudeOne\SpatialEncoder\Encoder;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedDimensionException;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialTypeException;
use LongitudeOne\SpatialEncoder\Strategy\EwkbBinaryStrategy;
use LongitudeOne\SpatialEncoder\Strategy\EwktTextStrategy;
use LongitudeOne\SpatialEncoder\Strategy\GeoJsonStrategy;
use LongitudeOne\SpatialEncoder\Strategy\MySQLBinaryStrategy;
use LongitudeOne\SpatialEncoder\Strategy\WkbBinaryStrategy;
use LongitudeOne\SpatialEncoder\Strategy\WktTextStrategy;
use LongitudeOne\SpatialTypes\Types\Dimension2\LineString;
use LongitudeOne\SpatialTypes\Types\Dimension2\Point;
use LongitudeOne\SpatialTypes\Types\Dimension2\SpatialCollection;
use PHPUnit\Framework\TestCase;

/**
 * Concrete mutable objects use the same read contracts as immutable objects.
 *
 * @internal
 */
class MutableEncodingTest extends TestCase
{
    /** An existing strategy must observe coordinate edits on its next call. */
    public function testCoordinatesAreReadAfterMutation(): void
    {
        $point = new Point(1, 2, 4326);
        $encoder = new Encoder(new WkbBinaryStrategy());
        $encoded = $encoder->encode($point);
        static::assertIsString($encoded);
        static::assertSame('0101000000000000000000f03f0000000000000040', bin2hex($encoded));

        $point->setX(3);
        $encoded = $encoder->encode($point);
        static::assertIsString($encoded);
        static::assertSame('010100000000000000000008400000000000000040', bin2hex($encoded));
        static::assertSame([3, 2], $point->toArray());
    }

    /** Mutable XYZM EWKT retains the complete declared dimension and SRID. */
    public function testEwktXyzm(): void
    {
        $point = new \LongitudeOne\SpatialTypes\Types\Dimension4zm\Point(1, 2, 3, 4, 4326);
        static::assertSame('SRID=4326;POINT ZM (1 2 3 4)', (new EwktTextStrategy())->encode($point));

        $point->setM(5);
        static::assertSame('SRID=4326;POINT ZM (1 2 3 5)', (new EwktTextStrategy())->encode($point));
    }

    /** The instance-level Geography family preserves longitude-first coordinates. */
    public function testGeographyAcrossFormats(): void
    {
        $point = new Point(1, 2, 4326, SpatialModelEnum::GEOGRAPHY);
        static::assertSame('POINT (1 2)', (new WktTextStrategy())->encode($point));
        static::assertSame('SRID=4326;POINT (1 2)', (new EwktTextStrategy())->encode($point));
        static::assertSame('0101000000000000000000f03f0000000000000040', bin2hex((new WkbBinaryStrategy())->encode($point)));
        static::assertSame('0101000020e6100000000000000000f03f0000000000000040', bin2hex((new EwkbBinaryStrategy())->encode($point)));
        static::assertSame('e61000000101000000000000000000f03f0000000000000040', bin2hex((new MySQLBinaryStrategy())->encode($point)));
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[1,2]}', (new GeoJsonStrategy())->encode($point));
    }

    /** All RFC aggregate types retain their concrete mutable members. */
    public function testGeoJsonAggregates(): void
    {
        $collection = new SpatialCollection([
            new \LongitudeOne\SpatialTypes\Types\Dimension2\MultiPoint([[1, 2], [3, 4]], 4326),
            new \LongitudeOne\SpatialTypes\Types\Dimension2\MultiLineString([[[1, 2], [3, 4]]], 4326),
            new \LongitudeOne\SpatialTypes\Types\Dimension2\Polygon([[[0, 0], [2, 0], [0, 2], [0, 0]]], 4326),
            new \LongitudeOne\SpatialTypes\Types\Dimension2\MultiPolygon([[[[0, 0], [2, 0], [0, 2], [0, 0]]]], 4326),
        ], 4326);
        static::assertJsonStringEqualsJsonString(
            '{"type":"GeometryCollection","geometries":[{"type":"MultiPoint","coordinates":[[1,2],[3,4]]},{"type":"MultiLineString","coordinates":[[[1,2],[3,4]]]},{"type":"Polygon","coordinates":[[[0,0],[2,0],[0,2],[0,0]]]},{"type":"MultiPolygon","coordinates":[[[[0,0],[2,0],[0,2],[0,0]]]]}]}',
            (new GeoJsonStrategy())->encode($collection)
        );
    }

    /** Mutable measured objects are rejected even when empty. */
    public function testGeoJsonMeasuredEmpty(): void
    {
        $this->expectException(UnsupportedDimensionException::class);
        (new GeoJsonStrategy())->encode(new \LongitudeOne\SpatialTypes\Types\Dimension3m\Point());
    }

    /** Mutable GeoJSON recursively retains nesting, members and declared Z. */
    public function testGeoJsonNestedXyzCollection(): void
    {
        $collection = new \LongitudeOne\SpatialTypes\Types\Dimension3z\SpatialCollection([
            new \LongitudeOne\SpatialTypes\Types\Dimension3z\Point(1, 2, 3, 4326),
            new \LongitudeOne\SpatialTypes\Types\Dimension3z\SpatialCollection([
                new \LongitudeOne\SpatialTypes\Types\Dimension3z\LineString([[4, 5, 6], [7, 8, 9]], 4326),
                new \LongitudeOne\SpatialTypes\Types\Dimension3z\Point(srid: 4326),
            ], 4326),
        ], 4326);
        static::assertJsonStringEqualsJsonString(
            '{"type":"GeometryCollection","geometries":[{"type":"Point","coordinates":[1,2,3]},{"type":"GeometryCollection","geometries":[{"type":"LineString","coordinates":[[4,5,6],[7,8,9]]},{"type":"Point","coordinates":[]}]}]}',
            (new GeoJsonStrategy())->encode($collection)
        );
    }

    /** Mutable collection construction uses members before the declared reference. */
    public function testMysqlCollection(): void
    {
        $collection = new SpatialCollection([
            new Point(1, 2, 4326),
            new LineString([[0, 0], [2, 3]], 4326),
        ], 4326);
        static::assertSame(
            'e61000000107000000020000000101000000000000000000f03f00000000000000400102000000020000000000000000000000000000000000000000000000000000400000000000000840',
            bin2hex((new MySQLBinaryStrategy())->encode($collection))
        );
    }

    /** Mutable empty collections retain their SRID and remain accepted by MySQL. */
    public function testMysqlEmptyCollection(): void
    {
        $collection = new SpatialCollection([], 4326);
        static::assertSame('e6100000010700000000000000', bin2hex((new MySQLBinaryStrategy())->encode($collection)));
    }

    /** The MySQL EMPTY exception policy also applies to mutable objects. */
    public function testMysqlEmptyPoint(): void
    {
        $this->expectException(UnsupportedSpatialTypeException::class);
        (new MySQLBinaryStrategy())->encode(new Point());
    }
}
