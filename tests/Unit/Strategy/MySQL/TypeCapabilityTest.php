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

use LongitudeOne\Core\Enum\GeometryTypeEnum;
use LongitudeOne\SpatialEncoder\Encoder;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialTypeException;
use LongitudeOne\SpatialEncoder\Strategy\MySQL\MySQLTypeEncoder;
use LongitudeOne\SpatialEncoder\Strategy\MySQLBinaryStrategy;
use LongitudeOne\SpatialTypes\Interfaces\CollectionInterface;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Explicit MySQL capability decisions for every concrete spatial type.
 *
 * @internal
 */
#[CoversClass(Encoder::class)]
#[CoversClass(MySQLBinaryStrategy::class)]
#[CoversClass(MySQLTypeEncoder::class)]
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
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support spatial type CompoundCurve');
        new Encoder(new MySQLBinaryStrategy())->encode($collection);
    }

    /** Reject BRepSolid before reading dimensions or coordinate payloads. */
    public function testRejectsBrepSolidBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getSrid')->willReturn(4326);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::BREPSOLID);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->expects($this->never())->method('hasM');
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support spatial type BRepSolid');
        new Encoder(new MySQLBinaryStrategy())->encode($spatial);
    }

    /** Reject Circle before reading dimensions or coordinate payloads. */
    public function testRejectsCircleBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getSrid')->willReturn(4326);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::CIRCLE);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->expects($this->never())->method('hasM');
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support spatial type Circle');
        new Encoder(new MySQLBinaryStrategy())->encode($spatial);
    }

    /** Reject CircularString before reading dimensions or coordinate payloads. */
    public function testRejectsCircularStringBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getSrid')->willReturn(4326);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::CIRCULARSTRING);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->expects($this->never())->method('hasM');
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support spatial type CircularString');
        new Encoder(new MySQLBinaryStrategy())->encode($spatial);
    }

    /** Reject Clothoid before reading dimensions or coordinate payloads. */
    public function testRejectsClothoidBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getSrid')->willReturn(4326);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::CLOTHOID);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->expects($this->never())->method('hasM');
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support spatial type Clothoid');
        new Encoder(new MySQLBinaryStrategy())->encode($spatial);
    }

    /** Reject CompoundCurve before reading dimensions or coordinate payloads. */
    public function testRejectsCompoundCurveBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getSrid')->willReturn(4326);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::COMPOUNDCURVE);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->expects($this->never())->method('hasM');
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support spatial type CompoundCurve');
        new Encoder(new MySQLBinaryStrategy())->encode($spatial);
    }

    /** Reject CompoundSurface before reading dimensions or coordinate payloads. */
    public function testRejectsCompoundSurfaceBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getSrid')->willReturn(4326);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::COMPOUNDSURFACE);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->expects($this->never())->method('hasM');
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support spatial type CompoundSurface');
        new Encoder(new MySQLBinaryStrategy())->encode($spatial);
    }

    /** Reject CurvePolygon before reading dimensions or coordinate payloads. */
    public function testRejectsCurvePolygonBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getSrid')->willReturn(4326);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::CURVEPOLYGON);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->expects($this->never())->method('hasM');
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support spatial type CurvePolygon');
        new Encoder(new MySQLBinaryStrategy())->encode($spatial);
    }

    /** Reject EllipticalCurve before reading dimensions or coordinate payloads. */
    public function testRejectsEllipticalCurveBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getSrid')->willReturn(4326);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::ELLIPTICALCURVE);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->expects($this->never())->method('hasM');
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support spatial type EllipticalCurve');
        new Encoder(new MySQLBinaryStrategy())->encode($spatial);
    }

    /** Reject GeodesicString before reading dimensions or coordinate payloads. */
    public function testRejectsGeodesicStringBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getSrid')->willReturn(4326);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::GEODESICSTRING);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->expects($this->never())->method('hasM');
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support spatial type GeodesicString');
        new Encoder(new MySQLBinaryStrategy())->encode($spatial);
    }

    /** Reject MultiCurve before reading dimensions or coordinate payloads. */
    public function testRejectsMultiCurveBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getSrid')->willReturn(4326);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::MULTICURVE);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->expects($this->never())->method('hasM');
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support spatial type MultiCurve');
        new Encoder(new MySQLBinaryStrategy())->encode($spatial);
    }

    /** Reject MultiSurface before reading dimensions or coordinate payloads. */
    public function testRejectsMultiSurfaceBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getSrid')->willReturn(4326);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::MULTISURFACE);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->expects($this->never())->method('hasM');
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support spatial type MultiSurface');
        new Encoder(new MySQLBinaryStrategy())->encode($spatial);
    }

    /** Reject NURBSCurve before reading dimensions or coordinate payloads. */
    public function testRejectsNurbsCurveBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getSrid')->willReturn(4326);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::NURBSCURVE);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->expects($this->never())->method('hasM');
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support spatial type NURBSCurve');
        new Encoder(new MySQLBinaryStrategy())->encode($spatial);
    }

    /** Reject PolyhedralSurface before reading dimensions or coordinate payloads. */
    public function testRejectsPolyhedralSurfaceBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getSrid')->willReturn(4326);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::POLYHEDRALSURFACE);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->expects($this->never())->method('hasM');
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support spatial type PolyhedralSurface');
        new Encoder(new MySQLBinaryStrategy())->encode($spatial);
    }

    /** Reject SpiralCurve before reading dimensions or coordinate payloads. */
    public function testRejectsSpiralCurveBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getSrid')->willReturn(4326);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::SPIRALCURVE);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->expects($this->never())->method('hasM');
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support spatial type SpiralCurve');
        new Encoder(new MySQLBinaryStrategy())->encode($spatial);
    }

    /** Reject TIN before reading dimensions or coordinate payloads. */
    public function testRejectsTinBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getSrid')->willReturn(4326);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::TIN);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->expects($this->never())->method('hasM');
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support spatial type TIN');
        new Encoder(new MySQLBinaryStrategy())->encode($spatial);
    }

    /** Reject Triangle before reading dimensions or coordinate payloads. */
    public function testRejectsTriangleBeforeReadingPayload(): void
    {
        $spatial = self::createMock(SpatialInterface::class);
        $spatial->method('getSrid')->willReturn(4326);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::TRIANGLE);
        $spatial->expects($this->never())->method('hasZ');
        $spatial->expects($this->never())->method('hasM');
        $spatial->expects($this->never())->method('toArray');

        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('MySQL adapter does not support spatial type Triangle');
        new Encoder(new MySQLBinaryStrategy())->encode($spatial);
    }
}
