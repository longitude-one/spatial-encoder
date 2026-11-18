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

use LongitudeOne\Core\Enum\ByteOrderEnum;
use LongitudeOne\SpatialEncoder\Strategy\Wkb\WkbTypeEncoder;
use LongitudeOne\SpatialEncoder\Strategy\WkbBinaryStrategy;
use LongitudeOne\SpatialTypes\Types\Dimension2\CircularString as MutableCircularString;
use LongitudeOne\SpatialTypes\Types\Dimension2\CompoundCurve as MutableCompoundCurve;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\CircularStringImmutable as GeographicCircularString;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\CompoundCurveImmutable as GeographicCompoundCurve;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\LineStringImmutable as GeographicLineString;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\CircularStringImmutable as CircularString;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\CompoundCurveImmutable as CompoundCurve;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\GeometryCollectionImmutable as GeometryCollection;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\LineStringImmutable as LineString;
use LongitudeOne\SpatialTypes\Types\Dimension2\LineString as MutableLineString;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\CircularStringImmutable as CircularStringM;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\CompoundCurveImmutable as CompoundCurveM;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\LineStringImmutable as LineStringM;
use LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\CircularStringImmutable as CircularStringZ;
use LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\CompoundCurveImmutable as CompoundCurveZ;
use LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\LineStringImmutable as LineStringZ;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\CircularStringImmutable as CircularStringZm;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\CompoundCurveImmutable as CompoundCurveZm;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\LineStringImmutable as LineStringZm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Literal WKB fixtures for component boundaries, dimensions and EMPTY.
 *
 * @internal
 */
#[CoversClass(WkbBinaryStrategy::class)]
#[CoversClass(WkbTypeEncoder::class)]
final class CompoundCurveTest extends TestCase
{
    /** Retain two complete circular components without interpolation. */
    public function testCircularOnly(): void
    {
        $curve = new CompoundCurve([
            new CircularString([[0, 0], [1, 1], [2, 0]], 4326),
            new CircularString([[2, 0], [3, -1], [4, 0]], 4326),
        ], 4326);

        // Reference geometry in WKT: COMPOUNDCURVE (CIRCULARSTRING (0 0, 1 1, 2 0), CIRCULARSTRING (2 0, 3 -1, 4 0))
        self::assertSame(
            '01090000000200000001080000000300000000000000000000000000000000000000000000000000f03f000000000000f03f00000000000000400000000000000000010800000003000000000000000000004000000000000000000000000000000840000000000000f0bf00000000000010400000000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '01090000000200000001080000000300000000000000000000000000000000000000000000000000f03f000000000000f03f00000000000000400000000000000000010800000003000000000000000000004000000000000000000000000000000840000000000000f0bf00000000000010400000000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '000000000900000002000000000800000003000000000000000000000000000000003ff00000000000003ff000000000000040000000000000000000000000000000000000000800000003400000000000000000000000000000004008000000000000bff000000000000040100000000000000000000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }

    /** Preserve the repeated closing endpoint without changing either component. */
    public function testClosed(): void
    {
        $curve = new CompoundCurve([
            new CircularString([[0, 0], [1, 1], [2, 0]], 4326),
            new LineString([[2, 0], [0, 0]], 4326),
        ], 4326);

        // Reference geometry in WKT: COMPOUNDCURVE (CIRCULARSTRING (0 0, 1 1, 2 0), (2 0, 0 0))
        self::assertSame(
            '01090000000200000001080000000300000000000000000000000000000000000000000000000000f03f000000000000f03f000000000000004000000000000000000102000000020000000000000000000040000000000000000000000000000000000000000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '01090000000200000001080000000300000000000000000000000000000000000000000000000000f03f000000000000f03f000000000000004000000000000000000102000000020000000000000000000040000000000000000000000000000000000000000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '000000000900000002000000000800000003000000000000000000000000000000003ff00000000000003ff0000000000000400000000000000000000000000000000000000002000000024000000000000000000000000000000000000000000000000000000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }

    /** The approved EMPTY contract writes a uint32 zero component count. */
    public function testEmptyWritesZeroComponentCount(): void
    {
        $curve = new CompoundCurve([], 4326);

        // Reference geometry in WKT: COMPOUNDCURVE EMPTY
        self::assertSame(
            '010900000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '010900000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '000000000900000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }

    /** Retain XYM metadata and the explicit zero component count. */
    public function testEmptyXymWritesZeroComponentCount(): void
    {
        $curve = new CompoundCurveM([], 4326);

        // Reference geometry in WKT: COMPOUNDCURVE M EMPTY
        self::assertSame(
            '01d907000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '01d907000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '00000007d900000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }

    /** Retain XYZM metadata and the explicit zero component count. */
    public function testEmptyXyzmWritesZeroComponentCount(): void
    {
        $curve = new CompoundCurveZm([], 4326);

        // Reference geometry in WKT: COMPOUNDCURVE ZM EMPTY
        self::assertSame(
            '01c10b000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '01c10b000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '0000000bc100000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }

    /** Retain XYZ metadata and the explicit zero component count. */
    public function testEmptyXyzWritesZeroComponentCount(): void
    {
        $curve = new CompoundCurveZ([], 4326);

        // Reference geometry in WKT: COMPOUNDCURVE Z EMPTY
        self::assertSame(
            '01f103000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '01f103000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '00000003f100000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }

    /** An EMPTY without SRID still contains a uint32 zero component count. */
    public function testEmptyZeroSridWritesZeroComponentCount(): void
    {
        $curve = new CompoundCurve([], 0);

        // Reference geometry in WKT: COMPOUNDCURVE EMPTY
        self::assertSame(
            '010900000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '010900000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '000000000900000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }

    /** A geographic SRID preserves the supplied X and Y order. */
    public function testGeographyPreservesAxisOrder(): void
    {
        $curve = new GeographicCompoundCurve([
            new GeographicLineString([[2, 40], [4, 40]], 4326),
            new GeographicCircularString([[4, 40], [5, 41], [6, 40]], 4326),
        ], 4326);

        // Reference geometry in WKT: COMPOUNDCURVE ((2 40, 4 40), CIRCULARSTRING (4 40, 5 41, 6 40))
        self::assertSame(
            '0109000000020000000102000000020000000000000000000040000000000000444000000000000010400000000000004440010800000003000000000000000000104000000000000044400000000000001440000000000080444000000000000018400000000000004440',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '0109000000020000000102000000020000000000000000000040000000000000444000000000000010400000000000004440010800000003000000000000000000104000000000000044400000000000001440000000000080444000000000000018400000000000004440',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '0000000009000000020000000002000000024000000000000000404400000000000040100000000000004044000000000000000000000800000003401000000000000040440000000000004014000000000000404480000000000040180000000000004044000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }

    /** Retain two distinct linear component headers and their shared endpoint. */
    public function testLinearOnly(): void
    {
        $curve = new CompoundCurve([
            new LineString([[0, 0], [2, 0]], 4326),
            new LineString([[2, 0], [3, 1]], 4326),
        ], 4326);

        // Reference geometry in WKT: COMPOUNDCURVE ((0 0, 2 0), (2 0, 3 1))
        self::assertSame(
            '0109000000020000000102000000020000000000000000000000000000000000000000000000000000400000000000000000010200000002000000000000000000004000000000000000000000000000000840000000000000f03f',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '0109000000020000000102000000020000000000000000000000000000000000000000000000000000400000000000000000010200000002000000000000000000004000000000000000000000000000000840000000000000f03f',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '00000000090000000200000000020000000200000000000000000000000000000000400000000000000000000000000000000000000002000000024000000000000000000000000000000040080000000000003ff0000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }

    /** Keep component order, boundaries and the distinct open endpoints. */
    public function testMixedOpen(): void
    {
        $curve = new CompoundCurve([
            new LineString([[0, 0], [2, 0]], 4326),
            new CircularString([[2, 0], [3, 1], [4, 0]], 4326),
            new LineString([[4, 0], [5, -1]], 4326),
        ], 4326);

        // Reference geometry in WKT: COMPOUNDCURVE ((0 0, 2 0), CIRCULARSTRING (2 0, 3 1, 4 0), (4 0, 5 -1))
        self::assertSame(
            '0109000000030000000102000000020000000000000000000000000000000000000000000000000000400000000000000000010800000003000000000000000000004000000000000000000000000000000840000000000000f03f00000000000010400000000000000000010200000002000000000000000000104000000000000000000000000000001440000000000000f0bf',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '0109000000030000000102000000020000000000000000000000000000000000000000000000000000400000000000000000010800000003000000000000000000004000000000000000000000000000000840000000000000f03f00000000000010400000000000000000010200000002000000000000000000104000000000000000000000000000001440000000000000f0bf',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '00000000090000000300000000020000000200000000000000000000000000000000400000000000000000000000000000000000000008000000034000000000000000000000000000000040080000000000003ff000000000000040100000000000000000000000000000000000000200000002401000000000000000000000000000004014000000000000bff0000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }

    /** Encoding leaves mutable components and their parent unchanged. */
    public function testMutableInputIsUnchanged(): void
    {
        $curve = new MutableCompoundCurve([
            new MutableLineString([[0, 0], [2, 0]], 4326),
            new MutableCircularString([[2, 0], [3, 1], [4, 0]], 4326),
        ], 4326);
        $coordinates = $curve->toArray();
        $components = $curve->getCurves();

        // Reference geometry in WKT: COMPOUNDCURVE ((0 0, 2 0), CIRCULARSTRING (2 0, 3 1, 4 0))
        self::assertSame(
            '0109000000020000000102000000020000000000000000000000000000000000000000000000000000400000000000000000010800000003000000000000000000004000000000000000000000000000000840000000000000f03f00000000000010400000000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '0109000000020000000102000000020000000000000000000000000000000000000000000000000000400000000000000000010800000003000000000000000000004000000000000000000000000000000840000000000000f03f00000000000010400000000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '00000000090000000200000000020000000200000000000000000000000000000000400000000000000000000000000000000000000008000000034000000000000000000000000000000040080000000000003ff000000000000040100000000000000000000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
        self::assertSame($coordinates, $curve->toArray());
        self::assertSame($components, $curve->getCurves());
        self::assertSame(4326, $curve->getSrid());
    }

    /** Nested compound curves retain complete headers and inherit the root SRID. */
    public function testNestedCollection(): void
    {
        $collection = new GeometryCollection(4326, [
            new GeometryCollection(4326, [
                new CompoundCurve([
                    new LineString([[0, 0], [2, 0]], 4326),
                    new CircularString([[2, 0], [3, 1], [4, 0]], 4326),
                ], 4326),
                new CompoundCurve([], 4326),
            ]),
        ]);

        // Reference geometry in WKT: GEOMETRYCOLLECTION (GEOMETRYCOLLECTION (COMPOUNDCURVE ((0 0, 2 0), CIRCULARSTRING (2 0, 3 1, 4 0)), COMPOUNDCURVE EMPTY))
        self::assertSame(
            '0107000000010000000107000000020000000109000000020000000102000000020000000000000000000000000000000000000000000000000000400000000000000000010800000003000000000000000000004000000000000000000000000000000840000000000000f03f00000000000010400000000000000000010900000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($collection))
        );
        self::assertSame(
            '00000000070000000100000000070000000200000000090000000200000000020000000200000000000000000000000000000000400000000000000000000000000000000000000008000000034000000000000000000000000000000040080000000000003ff000000000000040100000000000000000000000000000000000000900000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($collection))
        );
    }

    /** Retain M measures without interpreting them as Z ordinates. */
    public function testXym(): void
    {
        $curve = new CompoundCurveM([
            new LineStringM([[0, 0, 6], [2, 0, 7]], 4326),
            new CircularStringM([[2, 0, 7], [3, 1, 8], [4, 0, 9]], 4326),
        ], 4326);

        // Reference geometry in WKT: COMPOUNDCURVE M ((0 0 6, 2 0 7), CIRCULARSTRING M (2 0 7, 3 1 8, 4 0 9))
        self::assertSame(
            '01d90700000200000001d207000002000000000000000000000000000000000000000000000000001840000000000000004000000000000000000000000000001c4001d807000003000000000000000000004000000000000000000000000000001c400000000000000840000000000000f03f0000000000002040000000000000104000000000000000000000000000002240',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '01d90700000200000001d207000002000000000000000000000000000000000000000000000000001840000000000000004000000000000000000000000000001c4001d807000003000000000000000000004000000000000000000000000000001c400000000000000840000000000000f03f0000000000002040000000000000104000000000000000000000000000002240',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '00000007d90000000200000007d20000000200000000000000000000000000000000401800000000000040000000000000000000000000000000401c00000000000000000007d80000000340000000000000000000000000000000401c00000000000040080000000000003ff00000000000004020000000000000401000000000000000000000000000004022000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }

    /** Retain XYZ dimensions on the outer curve and every component. */
    public function testXyz(): void
    {
        $curve = new CompoundCurveZ([
            new LineStringZ([[0, 0, 3], [2, 0, 4]], 4326),
            new CircularStringZ([[2, 0, 4], [3, 1, 5], [4, 0, 6]], 4326),
        ], 4326);

        // Reference geometry in WKT: COMPOUNDCURVE Z ((0 0 3, 2 0 4), CIRCULARSTRING Z (2 0 4, 3 1 5, 4 0 6))
        self::assertSame(
            '01f10300000200000001ea0300000200000000000000000000000000000000000000000000000000084000000000000000400000000000000000000000000000104001f0030000030000000000000000000040000000000000000000000000000010400000000000000840000000000000f03f0000000000001440000000000000104000000000000000000000000000001840',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '01f10300000200000001ea0300000200000000000000000000000000000000000000000000000000084000000000000000400000000000000000000000000000104001f0030000030000000000000000000040000000000000000000000000000010400000000000000840000000000000f03f0000000000001440000000000000104000000000000000000000000000001840',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '00000003f10000000200000003ea0000000200000000000000000000000000000000400800000000000040000000000000000000000000000000401000000000000000000003f00000000340000000000000000000000000000000401000000000000040080000000000003ff00000000000004014000000000000401000000000000000000000000000004018000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }

    /** Retain XYZM ordinate order and both dimension indicators. */
    public function testXyzm(): void
    {
        $curve = new CompoundCurveZm([
            new LineStringZm([[0, 0, 3, 6], [2, 0, 4, 7]], 4326),
            new CircularStringZm([[2, 0, 4, 7], [3, 1, 5, 8], [4, 0, 6, 9]], 4326),
        ], 4326);

        // Reference geometry in WKT: COMPOUNDCURVE ZM ((0 0 3 6, 2 0 4 7), CIRCULARSTRING ZM (2 0 4 7, 3 1 5 8, 4 0 6 9))
        self::assertSame(
            '01c10b00000200000001ba0b00000200000000000000000000000000000000000000000000000000084000000000000018400000000000000040000000000000000000000000000010400000000000001c4001c00b0000030000000000000000000040000000000000000000000000000010400000000000001c400000000000000840000000000000f03f000000000000144000000000000020400000000000001040000000000000000000000000000018400000000000002240',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '01c10b00000200000001ba0b00000200000000000000000000000000000000000000000000000000084000000000000018400000000000000040000000000000000000000000000010400000000000001c4001c00b0000030000000000000000000040000000000000000000000000000010400000000000001c400000000000000840000000000000f03f000000000000144000000000000020400000000000001040000000000000000000000000000018400000000000002240',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '0000000bc1000000020000000bba000000020000000000000000000000000000000040080000000000004018000000000000400000000000000000000000000000004010000000000000401c0000000000000000000bc000000003400000000000000000000000000000004010000000000000401c00000000000040080000000000003ff0000000000000401400000000000040200000000000004010000000000000000000000000000040180000000000004022000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }

    /** A zero SRID introduces no SRID flag or field. */
    public function testZeroSrid(): void
    {
        $curve = new CompoundCurve([
            new LineString([[0, 0], [2, 0]], 0),
            new CircularString([[2, 0], [3, 1], [4, 0]], 0),
        ], 0);

        // Reference geometry in WKT: COMPOUNDCURVE ((0 0, 2 0), CIRCULARSTRING (2 0, 3 1, 4 0))
        self::assertSame(
            '0109000000020000000102000000020000000000000000000000000000000000000000000000000000400000000000000000010800000003000000000000000000004000000000000000000000000000000840000000000000f03f00000000000010400000000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '0109000000020000000102000000020000000000000000000000000000000000000000000000000000400000000000000000010800000003000000000000000000004000000000000000000000000000000840000000000000f03f00000000000010400000000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '00000000090000000200000000020000000200000000000000000000000000000000400000000000000000000000000000000000000008000000034000000000000000000000000000000040080000000000003ff000000000000040100000000000000000000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }
}
