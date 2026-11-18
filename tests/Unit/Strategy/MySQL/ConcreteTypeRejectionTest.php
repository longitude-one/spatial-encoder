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

use LongitudeOne\SpatialEncoder\Encoder;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialTypeException;
use LongitudeOne\SpatialEncoder\Strategy\MySQL\MySQLGeometryEncoder;
use LongitudeOne\SpatialEncoder\Strategy\MySQL\MySQLTypeEncoder;
use LongitudeOne\SpatialEncoder\Strategy\MySQLBinaryStrategy;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\CircularStringImmutable;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\CompoundCurveImmutable;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\GeometryCollectionImmutable;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\LineStringImmutable;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\TriangleImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Reject concrete curves and surfaces without converting or flattening them.
 *
 * @internal
 */
#[CoversClass(Encoder::class)]
#[CoversClass(MySQLBinaryStrategy::class)]
#[CoversClass(MySQLGeometryEncoder::class)]
#[CoversClass(MySQLTypeEncoder::class)]
class ConcreteTypeRejectionTest extends TestCase
{
    /** A circular control-point sequence must not become a LineString. */
    public function testCircularString(): void
    {
        $spatial = new CircularStringImmutable([[0, 0], [1, 1], [2, 0]], 4326);
        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support spatial type CircularString');
        new Encoder(new MySQLBinaryStrategy())->encode($spatial);
    }

    /** Unsupported types fail through the type exception regardless of dimensions. */
    public function testCircularStringDimension3m(): void
    {
        $spatial = new \LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\CircularStringImmutable([[0, 0, 4], [1, 1, 5], [2, 0, 6]], 4326);
        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support spatial type CircularString');
        new Encoder(new MySQLBinaryStrategy())->encode($spatial);
    }

    /** Unsupported types fail through the type exception regardless of dimensions. */
    public function testCircularStringDimension3z(): void
    {
        $spatial = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\CircularStringImmutable([[0, 0, 1], [1, 1, 2], [2, 0, 3]], 4326);
        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support spatial type CircularString');
        new Encoder(new MySQLBinaryStrategy())->encode($spatial);
    }

    /** Unsupported types fail through the type exception regardless of dimensions. */
    public function testCircularStringDimension4zm(): void
    {
        $spatial = new \LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\CircularStringImmutable([[0, 0, 1, 4], [1, 1, 2, 5], [2, 0, 3, 6]], 4326);
        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support spatial type CircularString');
        new Encoder(new MySQLBinaryStrategy())->encode($spatial);
    }

    /** Empty unsupported values still fail because of their type. */
    public function testEmptyCircularString(): void
    {
        $spatial = new CircularStringImmutable([], 4326);
        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support spatial type CircularString');
        new Encoder(new MySQLBinaryStrategy())->encode($spatial);
    }

    /** Linear-only compound curves retain their own type and must not be flattened. */
    public function testLinearCompoundCurve(): void
    {
        $spatial = new CompoundCurveImmutable([new LineStringImmutable([[0, 0], [2, 0]], 4326)], 4326);
        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support spatial type CompoundCurve');
        new Encoder(new MySQLBinaryStrategy())->encode($spatial);
    }

    /** Collection membership cannot bypass the type boundary. */
    public function testNestedCircularString(): void
    {
        $spatial = new GeometryCollectionImmutable(4326, [
            new GeometryCollectionImmutable(4326, [new CircularStringImmutable([[0, 0], [1, 1], [2, 0]], 4326)]),
        ]);
        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support spatial type CircularString');
        new Encoder(new MySQLBinaryStrategy())->encode($spatial);
    }

    /** Compound components must not be flattened when nested in a collection. */
    public function testNestedCompoundCurve(): void
    {
        $spatial = new GeometryCollectionImmutable(4326, [
            new CompoundCurveImmutable([
                new LineStringImmutable([[0, 0], [2, 0]], 4326),
                new CircularStringImmutable([[2, 0], [3, 1], [4, 0]], 4326),
            ], 4326),
        ]);
        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support spatial type CompoundCurve');
        new Encoder(new MySQLBinaryStrategy())->encode($spatial);
    }

    /** Triangle cannot silently become a Polygon. */
    public function testTriangle(): void
    {
        $spatial = new TriangleImmutable([[[0, 0], [2, 0], [0, 2], [0, 0]]], 4326);
        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support spatial type Triangle');
        new Encoder(new MySQLBinaryStrategy())->encode($spatial);
    }
}
