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

namespace LongitudeOne\SpatialEncoder\Tests\Unit\Strategy\MySQL;

use LongitudeOne\SpatialEncoder\Exception\UnsupportedDimensionException;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialTypeException;
use LongitudeOne\SpatialEncoder\Strategy\MySQL\MySQLTypeEncoder;
use LongitudeOne\SpatialEncoder\Strategy\MySQLBinaryStrategy;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\LineStringImmutable as LineString;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\MultiLineStringImmutable as MultiLineString;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\MultiPointImmutable as MultiPoint;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\MultiPolygonImmutable as MultiPolygon;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\PointImmutable as Point;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\PolygonImmutable as Polygon;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Geography\GeographyCollectionImmutable as GeographyCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Explicit rejection tests for incompatible MySQL inputs.
 *
 * @internal
 */
#[CoversClass(MySQLBinaryStrategy::class)]
#[CoversClass(MySQLTypeEncoder::class)]
class GeographyInvalidInputTest extends TestCase
{
    /** Reject incompatible input instead of emitting a lossy or invalid value. */
    public function testEmptyLineString(): void
    {
        $spatial = new LineString([], 4326);
        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support empty LineString; only GeometryCollection may be empty.');
        new MySQLBinaryStrategy()->encode($spatial);
    }

    /** Reject incompatible input instead of emitting a lossy or invalid value. */
    public function testEmptyMultiLineString(): void
    {
        $spatial = new MultiLineString([], 4326);
        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support empty MultiLineString; only GeometryCollection may be empty.');
        new MySQLBinaryStrategy()->encode($spatial);
    }

    /** Reject incompatible input instead of emitting a lossy or invalid value. */
    public function testEmptyMultiPoint(): void
    {
        $spatial = new MultiPoint([], 4326);
        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support empty MultiPoint; only GeometryCollection may be empty.');
        new MySQLBinaryStrategy()->encode($spatial);
    }

    /** Reject incompatible input instead of emitting a lossy or invalid value. */
    public function testEmptyMultiPolygon(): void
    {
        $spatial = new MultiPolygon([], 4326);
        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support empty MultiPolygon; only GeometryCollection may be empty.');
        new MySQLBinaryStrategy()->encode($spatial);
    }

    /** Reject incompatible input instead of emitting a lossy or invalid value. */
    public function testEmptyPoint(): void
    {
        $spatial = new Point(srid: 4326);
        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support empty Point; only GeometryCollection may be empty.');
        new MySQLBinaryStrategy()->encode($spatial);
    }

    /** Reject incompatible input instead of emitting a lossy or invalid value. */
    public function testEmptyPolygon(): void
    {
        $spatial = new Polygon([], 4326);
        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support empty Polygon; only GeometryCollection may be empty.');
        new MySQLBinaryStrategy()->encode($spatial);
    }

    /** Reject incompatible input instead of emitting a lossy or invalid value. */
    public function testMultiLineStringWithEmptyMember(): void
    {
        $spatial = new MultiLineString([new LineString([], 4326)], 4326);
        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support empty LineString; only GeometryCollection may be empty.');
        new MySQLBinaryStrategy()->encode($spatial);
    }

    /** Reject incompatible input instead of emitting a lossy or invalid value. */
    public function testMultiPolygonWithEmptyMember(): void
    {
        $spatial = new MultiPolygon([new Polygon([], 4326)], 4326);
        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support empty Polygon; only GeometryCollection may be empty.');
        new MySQLBinaryStrategy()->encode($spatial);
    }

    /** Reject incompatible input instead of emitting a lossy or invalid value. */
    public function testNestedEmptyPoint(): void
    {
        $spatial = new \LongitudeOne\SpatialTypes\Types\Dimension2\Geography\GeographyCollectionImmutable(4326, [
            new \LongitudeOne\SpatialTypes\Types\Dimension2\Geography\GeographyCollectionImmutable(4326, [new Point(srid: 4326)]),
        ]);
        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support empty Point; only GeometryCollection may be empty.');
        new MySQLBinaryStrategy()->encode($spatial);
    }

    /** Reject incompatible input instead of emitting a lossy or invalid value. */
    public function testXymEmptyCollection(): void
    {
        $spatial = new GeographyCollection(4326, []);
        $this->expectException(UnsupportedDimensionException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter only supports XY coordinates; Z and M are not supported.');
        new MySQLBinaryStrategy()->encode($spatial);
    }

    /** Reject incompatible input instead of emitting a lossy or invalid value. */
    public function testXymEmptyPoint(): void
    {
        $spatial = new \LongitudeOne\SpatialTypes\Types\Dimension3m\Geography\PointImmutable(srid: 4326);
        $this->expectException(UnsupportedDimensionException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter only supports XY coordinates; Z and M are not supported.');
        new MySQLBinaryStrategy()->encode($spatial);
    }

    /** Reject incompatible input instead of emitting a lossy or invalid value. */
    public function testXymPoint(): void
    {
        $spatial = new \LongitudeOne\SpatialTypes\Types\Dimension3m\Geography\PointImmutable(1, 2, 4, 4326);
        $this->expectException(UnsupportedDimensionException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter only supports XY coordinates; Z and M are not supported.');
        new MySQLBinaryStrategy()->encode($spatial);
    }

    /** Reject incompatible input instead of emitting a lossy or invalid value. */
    public function testXyzEmptyCollection(): void
    {
        $spatial = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\GeographyCollectionImmutable(4326, []);
        $this->expectException(UnsupportedDimensionException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter only supports XY coordinates; Z and M are not supported.');
        new MySQLBinaryStrategy()->encode($spatial);
    }

    /** Reject incompatible input instead of emitting a lossy or invalid value. */
    public function testXyzEmptyPoint(): void
    {
        $spatial = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\PointImmutable(srid: 4326);
        $this->expectException(UnsupportedDimensionException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter only supports XY coordinates; Z and M are not supported.');
        new MySQLBinaryStrategy()->encode($spatial);
    }

    /** Reject incompatible input instead of emitting a lossy or invalid value. */
    public function testXyzmEmptyCollection(): void
    {
        $spatial = new \LongitudeOne\SpatialTypes\Types\Dimension4zm\Geography\GeographyCollectionImmutable(4326, []);
        $this->expectException(UnsupportedDimensionException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter only supports XY coordinates; Z and M are not supported.');
        new MySQLBinaryStrategy()->encode($spatial);
    }

    /** Reject incompatible input instead of emitting a lossy or invalid value. */
    public function testXyzmEmptyPoint(): void
    {
        $spatial = new \LongitudeOne\SpatialTypes\Types\Dimension4zm\Geography\PointImmutable(srid: 4326);
        $this->expectException(UnsupportedDimensionException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter only supports XY coordinates; Z and M are not supported.');
        new MySQLBinaryStrategy()->encode($spatial);
    }

    /** Reject incompatible input instead of emitting a lossy or invalid value. */
    public function testXyzmPoint(): void
    {
        $spatial = new \LongitudeOne\SpatialTypes\Types\Dimension4zm\Geography\PointImmutable(1, 2, 3, 4, 4326);
        $this->expectException(UnsupportedDimensionException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter only supports XY coordinates; Z and M are not supported.');
        new MySQLBinaryStrategy()->encode($spatial);
    }

    /** Reject incompatible input instead of emitting a lossy or invalid value. */
    public function testXyzPoint(): void
    {
        $spatial = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\PointImmutable(1, 2, 3, 4326);
        $this->expectException(UnsupportedDimensionException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter only supports XY coordinates; Z and M are not supported.');
        new MySQLBinaryStrategy()->encode($spatial);
    }
}
