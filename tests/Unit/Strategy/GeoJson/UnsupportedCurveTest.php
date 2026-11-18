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

namespace LongitudeOne\SpatialEncoder\Tests\Unit\Strategy\GeoJson;

use LongitudeOne\SpatialEncoder\Encoder;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedDimensionException;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialTypeException;
use LongitudeOne\SpatialEncoder\Strategy\GeoJson\GeometryEncoder;
use LongitudeOne\SpatialEncoder\Strategy\GeoJsonStrategy;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\CircularStringImmutable as CircularString;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\CompoundCurveImmutable as CompoundCurve;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\GeometryCollectionImmutable as GeometryCollection;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\LineStringImmutable as LineString;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\CircularStringImmutable as CircularStringM;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\CompoundCurveImmutable as CompoundCurveM;
use LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\CircularStringImmutable as CircularStringZ;
use LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\CompoundCurveImmutable as CompoundCurveZ;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\CircularStringImmutable as CircularStringZm;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\CompoundCurveImmutable as CompoundCurveZm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Real curves retain their semantics and are rejected without approximation.
 *
 * @internal
 */
#[CoversClass(Encoder::class)]
#[CoversClass(GeoJsonStrategy::class)]
#[CoversClass(GeometryEncoder::class)]
final class UnsupportedCurveTest extends TestCase
{
    /** Reject the real CircularString XY without losing ordinates. */
    public function testRejectsCircularString(): void
    {
        $curve = new CircularString([[0, 0], [1, 1], [2, 0]], 4326);

        $this->expectException(UnsupportedSpatialTypeException::class);
        new Encoder(new GeoJsonStrategy())->encode($curve);
    }

    /** Reject the real CircularString M without losing ordinates. */
    public function testRejectsCircularStringM(): void
    {
        $curve = new CircularStringM([[0, 0, 5], [1, 1, 6], [2, 0, 7]], 4326);

        $this->expectException(UnsupportedDimensionException::class);
        new Encoder(new GeoJsonStrategy())->encode($curve);
    }

    /** Reject the real CircularString Z without losing ordinates. */
    public function testRejectsCircularStringZ(): void
    {
        $curve = new CircularStringZ([[0, 0, 5], [1, 1, 6], [2, 0, 7]], 4326);

        $this->expectException(UnsupportedSpatialTypeException::class);
        new Encoder(new GeoJsonStrategy())->encode($curve);
    }

    /** Reject the real CircularString Zm without losing ordinates. */
    public function testRejectsCircularStringZm(): void
    {
        $curve = new CircularStringZm([[0, 0, 5, 8], [1, 1, 6, 9], [2, 0, 7, 10]], 4326);

        $this->expectException(UnsupportedDimensionException::class);
        new Encoder(new GeoJsonStrategy())->encode($curve);
    }

    /** Reject the real CompoundCurve XY without losing ordinates. */
    public function testRejectsCompoundCurve(): void
    {
        $curve = new CompoundCurve([new CircularString([[0, 0], [1, 1], [2, 0]], 4326)], 4326);

        $this->expectException(UnsupportedSpatialTypeException::class);
        new Encoder(new GeoJsonStrategy())->encode($curve);
    }

    /** Reject the real CompoundCurve M without losing ordinates. */
    public function testRejectsCompoundCurveM(): void
    {
        $curve = new CompoundCurveM([new CircularStringM([[0, 0, 5], [1, 1, 6], [2, 0, 7]], 4326)], 4326);

        $this->expectException(UnsupportedDimensionException::class);
        new Encoder(new GeoJsonStrategy())->encode($curve);
    }

    /** Reject the real CompoundCurve Z without losing ordinates. */
    public function testRejectsCompoundCurveZ(): void
    {
        $curve = new CompoundCurveZ([new CircularStringZ([[0, 0, 5], [1, 1, 6], [2, 0, 7]], 4326)], 4326);

        $this->expectException(UnsupportedSpatialTypeException::class);
        new Encoder(new GeoJsonStrategy())->encode($curve);
    }

    /** Reject the real CompoundCurve Zm without losing ordinates. */
    public function testRejectsCompoundCurveZm(): void
    {
        $curve = new CompoundCurveZm([new CircularStringZm([[0, 0, 5, 8], [1, 1, 6, 9], [2, 0, 7, 10]], 4326)], 4326);

        $this->expectException(UnsupportedDimensionException::class);
        new Encoder(new GeoJsonStrategy())->encode($curve);
    }

    /** Reject empty CircularString rather than exporting a supported substitute. */
    public function testRejectsEmptyCircularString(): void
    {
        $curve = new CircularString([], 4326);

        $this->expectException(UnsupportedSpatialTypeException::class);
        new Encoder(new GeoJsonStrategy())->encode($curve);
    }

    /** Reject empty CompoundCurve rather than exporting a supported substitute. */
    public function testRejectsEmptyCompoundCurve(): void
    {
        $curve = new CompoundCurve([], 4326);

        $this->expectException(UnsupportedSpatialTypeException::class);
        new Encoder(new GeoJsonStrategy())->encode($curve);
    }

    /** Even a compound made only of linear segments retains its unsupported type. */
    public function testRejectsLinearOnlyCompoundCurve(): void
    {
        $curve = new CompoundCurve([new LineString([[0, 0], [2, 0]], 4326)], 4326);

        $this->expectException(UnsupportedSpatialTypeException::class);
        new Encoder(new GeoJsonStrategy())->encode($curve);
    }

    /** Reject nested CircularString rather than exporting a supported substitute. */
    public function testRejectsNestedCircularString(): void
    {
        $curve = new GeometryCollection(4326, [new GeometryCollection(4326, [new CircularString([], 4326)])]);

        $this->expectException(UnsupportedSpatialTypeException::class);
        new Encoder(new GeoJsonStrategy())->encode($curve);
    }

    /** Reject nested CompoundCurve rather than exporting a supported substitute. */
    public function testRejectsNestedCompoundCurve(): void
    {
        $curve = new GeometryCollection(4326, [new GeometryCollection(4326, [new CompoundCurve([], 4326)])]);

        $this->expectException(UnsupportedSpatialTypeException::class);
        new Encoder(new GeoJsonStrategy())->encode($curve);
    }
}
