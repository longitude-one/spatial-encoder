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

use LongitudeOne\Core\Enum\GeometryTypeEnum;
use LongitudeOne\SpatialEncoder\Encoder;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialTypeException;
use LongitudeOne\SpatialEncoder\Strategy\GeoJson\GeometryEncoder;
use LongitudeOne\SpatialEncoder\Strategy\GeoJsonStrategy;
use LongitudeOne\SpatialTypes\Interfaces\CollectionInterface;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Explicit RFC 7946 capability decisions for every concrete spatial type.
 *
 * @internal
 */
#[CoversClass(Encoder::class)]
#[CoversClass(GeoJsonStrategy::class)]
#[CoversClass(GeometryEncoder::class)]
class TypeCapabilityTest extends TestCase
{
    /** A new concrete enum case requires an explicit support or rejection decision. */
    public function testEveryInstantiableTypeHasAnExplicitCapabilityDecision(): void
    {
        $supported = [
            GeometryTypeEnum::POINT,
            GeometryTypeEnum::LINESTRING,
            GeometryTypeEnum::POLYGON,
            GeometryTypeEnum::GEOMETRYCOLLECTION,
            GeometryTypeEnum::MULTIPOINT,
            GeometryTypeEnum::MULTILINESTRING,
            GeometryTypeEnum::MULTIPOLYGON,
        ];
        $unsupported = [
            GeometryTypeEnum::BREPSOLID,
            GeometryTypeEnum::CIRCLE,
            GeometryTypeEnum::CIRCULARSTRING,
            GeometryTypeEnum::CLOTHOID,
            GeometryTypeEnum::COMPOUNDCURVE,
            GeometryTypeEnum::COMPOUNDSURFACE,
            GeometryTypeEnum::CURVEPOLYGON,
            GeometryTypeEnum::ELLIPTICALCURVE,
            GeometryTypeEnum::GEODESICSTRING,
            GeometryTypeEnum::MULTICURVE,
            GeometryTypeEnum::MULTISURFACE,
            GeometryTypeEnum::NURBSCURVE,
            GeometryTypeEnum::POLYHEDRALSURFACE,
            GeometryTypeEnum::SPIRALCURVE,
            GeometryTypeEnum::TIN,
            GeometryTypeEnum::TRIANGLE,
        ];

        self::assertEqualsCanonicalizing(
            array_column(array_filter(
                new \ReflectionEnum(GeometryTypeEnum::class)->getCases(),
                static function (\ReflectionEnumUnitCase $case): bool {
                    $type = $case->getValue();

                    return $type instanceof GeometryTypeEnum && $type->isInstantiable();
                },
            ), 'name'),
            array_column(array_merge($supported, $unsupported), 'name'),
        );
    }

    /** A supported container must not inspect an unsupported member's payload. */
    public function testNestedTypeIsRejectedBeforeReadingPayload(): void
    {
        $member = self::createMock(SpatialInterface::class);
        $member->method('getType')->willReturn(GeometryTypeEnum::COMPOUNDCURVE);
        $member->expects($this->never())->method('hasZ');
        $member->expects($this->never())->method('hasM');
        $member->expects($this->never())->method('toArray');

        $collection = self::createMock(CollectionInterface::class);
        $collection->method('getSrid')->willReturn(4326);
        $collection->method('getType')->willReturn(GeometryTypeEnum::GEOMETRYCOLLECTION);
        $collection->method('hasZ')->willReturn(false);
        $collection->method('hasM')->willReturn(false);
        $collection->method('getElements')->willReturn([$member]);
        $collection->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('This GeoJSON strategy does not support COMPOUNDCURVE.');
        new Encoder(new GeoJsonStrategy())->encode($collection);
    }

    /** Reject BRepSolid before reading coordinate payloads. */
    public function testRejectsBrepSolidBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::BREPSOLID);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->method('hasM')->willReturn(false);
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('This GeoJSON strategy does not support BREPSOLID.');
        new Encoder(new GeoJsonStrategy())->encode($spatial);
    }

    /** Reject Circle before reading coordinate payloads. */
    public function testRejectsCircleBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::CIRCLE);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->method('hasM')->willReturn(false);
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('This GeoJSON strategy does not support CIRCLE.');
        new Encoder(new GeoJsonStrategy())->encode($spatial);
    }

    /** Reject CircularString before reading coordinate payloads. */
    public function testRejectsCircularStringBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::CIRCULARSTRING);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->method('hasM')->willReturn(false);
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('This GeoJSON strategy does not support CIRCULARSTRING.');
        new Encoder(new GeoJsonStrategy())->encode($spatial);
    }

    /** Reject Clothoid before reading coordinate payloads. */
    public function testRejectsClothoidBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::CLOTHOID);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->method('hasM')->willReturn(false);
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('This GeoJSON strategy does not support CLOTHOID.');
        new Encoder(new GeoJsonStrategy())->encode($spatial);
    }

    /** Reject CompoundCurve before reading coordinate payloads. */
    public function testRejectsCompoundCurveBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::COMPOUNDCURVE);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->method('hasM')->willReturn(false);
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('This GeoJSON strategy does not support COMPOUNDCURVE.');
        new Encoder(new GeoJsonStrategy())->encode($spatial);
    }

    /** Reject CompoundSurface before reading coordinate payloads. */
    public function testRejectsCompoundSurfaceBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::COMPOUNDSURFACE);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->method('hasM')->willReturn(false);
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('This GeoJSON strategy does not support COMPOUNDSURFACE.');
        new Encoder(new GeoJsonStrategy())->encode($spatial);
    }

    /** Reject CurvePolygon before reading coordinate payloads. */
    public function testRejectsCurvePolygonBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::CURVEPOLYGON);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->method('hasM')->willReturn(false);
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('This GeoJSON strategy does not support CURVEPOLYGON.');
        new Encoder(new GeoJsonStrategy())->encode($spatial);
    }

    /** Reject EllipticalCurve before reading coordinate payloads. */
    public function testRejectsEllipticalCurveBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::ELLIPTICALCURVE);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->method('hasM')->willReturn(false);
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('This GeoJSON strategy does not support ELLIPTICALCURVE.');
        new Encoder(new GeoJsonStrategy())->encode($spatial);
    }

    /** Reject GeodesicString before reading coordinate payloads. */
    public function testRejectsGeodesicStringBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::GEODESICSTRING);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->method('hasM')->willReturn(false);
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('This GeoJSON strategy does not support GEODESICSTRING.');
        new Encoder(new GeoJsonStrategy())->encode($spatial);
    }

    /** Reject MultiCurve before reading coordinate payloads. */
    public function testRejectsMultiCurveBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::MULTICURVE);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->method('hasM')->willReturn(false);
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('This GeoJSON strategy does not support MULTICURVE.');
        new Encoder(new GeoJsonStrategy())->encode($spatial);
    }

    /** Reject MultiSurface before reading coordinate payloads. */
    public function testRejectsMultiSurfaceBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::MULTISURFACE);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->method('hasM')->willReturn(false);
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('This GeoJSON strategy does not support MULTISURFACE.');
        new Encoder(new GeoJsonStrategy())->encode($spatial);
    }

    /** Reject NURBSCurve before reading coordinate payloads. */
    public function testRejectsNurbsCurveBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::NURBSCURVE);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->method('hasM')->willReturn(false);
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('This GeoJSON strategy does not support NURBSCURVE.');
        new Encoder(new GeoJsonStrategy())->encode($spatial);
    }

    /** Reject PolyhedralSurface before reading coordinate payloads. */
    public function testRejectsPolyhedralSurfaceBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::POLYHEDRALSURFACE);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->method('hasM')->willReturn(false);
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('This GeoJSON strategy does not support POLYHEDRALSURFACE.');
        new Encoder(new GeoJsonStrategy())->encode($spatial);
    }

    /** Reject SpiralCurve before reading coordinate payloads. */
    public function testRejectsSpiralCurveBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::SPIRALCURVE);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->method('hasM')->willReturn(false);
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('This GeoJSON strategy does not support SPIRALCURVE.');
        new Encoder(new GeoJsonStrategy())->encode($spatial);
    }

    /** Reject TIN before reading coordinate payloads. */
    public function testRejectsTinBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::TIN);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->method('hasM')->willReturn(false);
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('This GeoJSON strategy does not support TIN.');
        new Encoder(new GeoJsonStrategy())->encode($spatial);
    }

    /** Reject Triangle before reading coordinate payloads. */
    public function testRejectsTriangleBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::TRIANGLE);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->method('hasM')->willReturn(false);
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('This GeoJSON strategy does not support TRIANGLE.');
        new Encoder(new GeoJsonStrategy())->encode($spatial);
    }
}
