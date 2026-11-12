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

namespace LongitudeOne\SpatialEncoder\Tests\Unit\Strategy\Wkb;

use LongitudeOne\Core\Enum\GeometryTypeEnum;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialInterfaceException;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialTypeException;
use LongitudeOne\SpatialEncoder\Strategy\Wkb\WkbTypeEncoder;
use LongitudeOne\SpatialEncoder\Strategy\WkbBinaryStrategy;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\GeometryCollectionImmutable as GeometryCollection;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\LineStringImmutable as LineString;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\PointImmutable as Point;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\TriangleImmutable as Triangle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Regression tests for nested ISO WKB and unsupported spatial contracts.
 *
 * @internal
 */
#[CoversClass(WkbBinaryStrategy::class)]
#[CoversClass(WkbTypeEncoder::class)]
class WkbRegressionTest extends TestCase
{
    /** Preserve nested headers, empty members and all ordinates, omitting every SRID. */
    public function testNestedCollectionWithEmptyMembers(): void
    {
        $collection = new GeometryCollection(4326, [
            new Point(srid: 4326),
            new GeometryCollection(4326, [
                new Point(1, 2, 3, 4, 4326),
                new LineString([], 4326),
            ]),
            new Triangle([], 4326),
        ]);

        // Reference geometry in WKT: GEOMETRYCOLLECTION ZM (POINT ZM EMPTY, GEOMETRYCOLLECTION ZM (POINT ZM (1 2 3 4), LINESTRING ZM EMPTY), TRIANGLE ZM EMPTY)
        self::assertSame(
            '01bf0b00000300000001b90b0000000000000000f87f000000000000f87f000000000000f87f000000000000f87f01bf0b00000200000001b90b0000000000000000f03f00000000000000400000000000000840000000000000104001ba0b00000000000001c90b000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($collection))
        );
    }

    /** Reject an unsupported interface rather than emit a partial geometry. */
    public function testUnsupportedInterface(): void
    {
        $spatial = self::createStub(SpatialInterface::class);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::POINT);
        $this->expectException(UnsupportedSpatialInterfaceException::class);
        new WkbBinaryStrategy()->encode($spatial);
    }

    /** Reject enum cases without concrete supported spatial types. */
    public function testUnsupportedType(): void
    {
        $spatial = self::createStub(SpatialInterface::class);
        $spatial->method('getType')->willReturn(GeometryTypeEnum::CIRCULARSTRING);
        $this->expectException(UnsupportedSpatialTypeException::class);
        $this->expectExceptionMessageIsOrContains('WKB adapter does not support spatial type CircularString');
        new WkbBinaryStrategy()->encode($spatial);
    }
}
