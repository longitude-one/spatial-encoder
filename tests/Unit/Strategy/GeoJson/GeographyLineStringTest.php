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
use LongitudeOne\SpatialEncoder\Exception\UnsupportedGeometryStructureException;
use LongitudeOne\SpatialEncoder\Strategy\GeoJson\GeometryEncoder;
use LongitudeOne\SpatialEncoder\Strategy\GeoJsonStrategy;
use LongitudeOne\SpatialTypes\Reference\SpatialReference;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\LineStringImmutable as LineString;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Explicit GeoJSON LineString examples using the concrete model.
 *
 * @internal
 */
#[CoversClass(GeoJsonStrategy::class)]
#[CoversClass(GeometryEncoder::class)]
#[CoversClass(Encoder::class)]
class GeographyLineStringTest extends TestCase
{
    /** Preserve coordinates and their order without reference metadata. */
    public function testAdditionalPositions(): void
    {
        $line = new LineString([[3.5, 4.25], [1, 2], [-7, 8]]);
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($line);
        self::assertIsString($encoded);
        self::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[3.5,4.25],[1,2],[-7,8]]}', $encoded);
        self::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[3.5,4.25],[1,2],[-7,8]]}', new GeoJsonStrategy()->encode($line));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testAdditionalXyzPositions(): void
    {
        $line = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\LineStringImmutable([[3, 4, 9.5], [1, 2, -2], [-7, 8, 0]]);
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($line);
        self::assertIsString($encoded);
        self::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[3,4,9.5],[1,2,-2],[-7,8,0]]}', $encoded);
        self::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[3,4,9.5],[1,2,-2],[-7,8,0]]}', new GeoJsonStrategy()->encode($line));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testAntimeridian(): void
    {
        $line = new LineString([[170, 45], [-170, 45]], 4326);
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($line);
        self::assertIsString($encoded);
        self::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[170,45],[-170,45]]}', $encoded);
        self::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[170,45],[-170,45]]}', new GeoJsonStrategy()->encode($line));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testDeclaredEpsg(): void
    {
        $line = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\LineStringImmutable([[1, 2, 3], [4, 5, 6]], new SpatialReference(4326, 'EPSG'));
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($line);
        self::assertIsString($encoded);
        self::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[1,2,3],[4,5,6]]}', $encoded);
        self::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[1,2,3],[4,5,6]]}', new GeoJsonStrategy()->encode($line));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testEmptyXy(): void
    {
        $line = new LineString([]);
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($line);
        self::assertIsString($encoded);
        self::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[]}', $encoded);
        self::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[]}', new GeoJsonStrategy()->encode($line));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testEmptyXyz(): void
    {
        $line = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\LineStringImmutable([]);
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($line);
        self::assertIsString($encoded);
        self::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[]}', $encoded);
        self::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[]}', new GeoJsonStrategy()->encode($line));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testOtherSrid(): void
    {
        $line = new LineString([[1, 2], [3, 4]], 3857);
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($line);
        self::assertIsString($encoded);
        self::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[1,2],[3,4]]}', $encoded);
        self::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[1,2],[3,4]]}', new GeoJsonStrategy()->encode($line));
    }

    /** Reject an incompatible concrete input without modifying it. */
    public function testRejectEmptyXym(): void
    {
        $line = new \LongitudeOne\SpatialTypes\Types\Dimension3m\Geography\LineStringImmutable([]);
        $this->expectException(UnsupportedDimensionException::class);
        new Encoder(new GeoJsonStrategy())->encode($line);
    }

    /** Reject an incompatible concrete input without modifying it. */
    public function testRejectEmptyXyzm(): void
    {
        $line = new \LongitudeOne\SpatialTypes\Types\Dimension4zm\Geography\LineStringImmutable([]);
        $this->expectException(UnsupportedDimensionException::class);
        new Encoder(new GeoJsonStrategy())->encode($line);
    }

    /** Reject an incompatible concrete input without modifying it. */
    public function testRejectSingletonXy(): void
    {
        $line = new LineString([[1, 2]]);
        $this->expectException(UnsupportedGeometryStructureException::class);
        new Encoder(new GeoJsonStrategy())->encode($line);
    }

    /** Reject an incompatible concrete input without modifying it. */
    public function testRejectSingletonXyz(): void
    {
        $line = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\LineStringImmutable([[1, 2, 3]]);
        $this->expectException(UnsupportedGeometryStructureException::class);
        new Encoder(new GeoJsonStrategy())->encode($line);
    }

    /** Reject an incompatible concrete input without modifying it. */
    public function testRejectXym(): void
    {
        $line = new \LongitudeOne\SpatialTypes\Types\Dimension3m\Geography\LineStringImmutable([[1, 2, 3], [4, 5, 6]]);
        $this->expectException(UnsupportedDimensionException::class);
        new Encoder(new GeoJsonStrategy())->encode($line);
    }

    /** Reject an incompatible concrete input without modifying it. */
    public function testRejectXyzm(): void
    {
        $line = new \LongitudeOne\SpatialTypes\Types\Dimension4zm\Geography\LineStringImmutable([[1, 2, 3, 4], [5, 6, 7, 8]]);
        $this->expectException(UnsupportedDimensionException::class);
        new Encoder(new GeoJsonStrategy())->encode($line);
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testUnknownReference(): void
    {
        $line = new LineString([[1, 2], [3, 4]], new SpatialReference(999999, 'CUSTOM'));
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($line);
        self::assertIsString($encoded);
        self::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[1,2],[3,4]]}', $encoded);
        self::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[1,2],[3,4]]}', new GeoJsonStrategy()->encode($line));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testXy(): void
    {
        $line = new LineString([[1, 2], [3, 4]]);
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($line);
        self::assertIsString($encoded);
        self::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[1,2],[3,4]]}', $encoded);
        self::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[1,2],[3,4]]}', new GeoJsonStrategy()->encode($line));
    }

    /** Preserve coordinates and their order without reference metadata. */
    public function testXyz(): void
    {
        $line = new \LongitudeOne\SpatialTypes\Types\Dimension3z\Geography\LineStringImmutable([[1, 2, 3], [4, 5, 6]]);
        $encoder = new Encoder(new GeoJsonStrategy());

        $encoded = $encoder->encode($line);
        self::assertIsString($encoded);
        self::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[1,2,3],[4,5,6]]}', $encoded);
        self::assertJsonStringEqualsJsonString('{"type":"LineString","coordinates":[[1,2,3],[4,5,6]]}', new GeoJsonStrategy()->encode($line));
    }
}
