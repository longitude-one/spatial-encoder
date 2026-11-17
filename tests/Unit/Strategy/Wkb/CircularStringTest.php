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
use LongitudeOne\SpatialTypes\Types\Dimension2\Geography\CircularStringImmutable as GeographicCircularString;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\CircularStringImmutable as CircularString;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\GeometryCollectionImmutable as GeometryCollection;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\CircularStringImmutable as CircularStringM;
use LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\CircularStringImmutable as CircularStringZ;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\CircularStringImmutable as CircularStringZm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Literal WKB fixtures for circular-string control points, dimensions and EMPTY.
 *
 * @internal
 */
#[CoversClass(WkbBinaryStrategy::class)]
#[CoversClass(WkbTypeEncoder::class)]
final class CircularStringTest extends TestCase
{
    /** Preserve the repeated closing coordinate without linearizing the curve. */
    public function testClosed(): void
    {
        $curve = new CircularString([[0, 0], [1, 1], [2, 0], [1, -1], [0, 0]], 4326);

        self::assertSame(
            '01080000000500000000000000000000000000000000000000000000000000f03f000000000000f03f00000000000000400000000000000000000000000000f03f000000000000f0bf00000000000000000000000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '01080000000500000000000000000000000000000000000000000000000000f03f000000000000f03f00000000000000400000000000000000000000000000f03f000000000000f0bf00000000000000000000000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '000000000800000005000000000000000000000000000000003ff00000000000003ff0000000000000400000000000000000000000000000003ff0000000000000bff000000000000000000000000000000000000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }

    /** Retain nested curve headers and zero counts; only the EWKB root carries SRID. */
    public function testCollection(): void
    {
        $collection = new GeometryCollection(4326, [
            new CircularString([[0, 0], [1, 1], [2, 0]], 4326),
            new CircularString([], 4326),
        ]);

        self::assertSame(
            '01070000000200000001080000000300000000000000000000000000000000000000000000000000f03f000000000000f03f00000000000000400000000000000000010800000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($collection))
        );
        self::assertSame(
            '000000000700000002000000000800000003000000000000000000000000000000003ff00000000000003ff000000000000040000000000000000000000000000000000000000800000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($collection))
        );
    }

    /** Encode valid collinear control points without changing their coordinates. */
    public function testCollinear(): void
    {
        $curve = new CircularString([[0, 0], [1, 0], [2, 0]], 4326);

        self::assertSame(
            '01080000000300000000000000000000000000000000000000000000000000f03f000000000000000000000000000000400000000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '01080000000300000000000000000000000000000000000000000000000000f03f000000000000000000000000000000400000000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '000000000800000003000000000000000000000000000000003ff0000000000000000000000000000040000000000000000000000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }

    /** The approved EMPTY contract writes an explicit uint32 zero after the header. */
    public function testEmptyWritesZeroPointCount(): void
    {
        $curve = new CircularString([], 4326);

        self::assertSame(
            '010800000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '010800000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '000000000800000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }

    /** Retain XYM metadata and the approved explicit zero point count. */
    public function testEmptyXymWritesZeroPointCount(): void
    {
        $curve = new CircularStringM([], 4326);

        self::assertSame(
            '01d807000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '01d807000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '00000007d800000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }

    /** Retain XYZM metadata and the approved explicit zero point count. */
    public function testEmptyXyzmWritesZeroPointCount(): void
    {
        $curve = new CircularStringZm([], 4326);

        self::assertSame(
            '01c00b000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '01c00b000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '0000000bc000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }

    /** Retain XYZ metadata and the approved explicit zero point count. */
    public function testEmptyXyzWritesZeroPointCount(): void
    {
        $curve = new CircularStringZ([], 4326);

        self::assertSame(
            '01f003000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '01f003000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '00000003f000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }

    /** An EMPTY curve without SRID still contains the explicit zero point count. */
    public function testEmptyZeroSridWritesZeroPointCount(): void
    {
        $curve = new CircularString([], 0);

        self::assertSame(
            '010800000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '010800000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '000000000800000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }

    /** Preserve fractional and negative values in every ordinate. */
    public function testFractionalOrdinates(): void
    {
        $curve = new CircularStringZm([[1.5, -2.25, 3.5, -4.75], [2.5, -1.25, 4.5, -3.75], [3.5, -2.25, 5.5, -2.75]], 4326);

        self::assertSame(
            '01c00b000003000000000000000000f83f00000000000002c00000000000000c4000000000000013c00000000000000440000000000000f4bf00000000000012400000000000000ec00000000000000c4000000000000002c0000000000000164000000000000006c0',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '01c00b000003000000000000000000f83f00000000000002c00000000000000c4000000000000013c00000000000000440000000000000f4bf00000000000012400000000000000ec00000000000000c4000000000000002c0000000000000164000000000000006c0',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '0000000bc0000000033ff8000000000000c002000000000000400c000000000000c0130000000000004004000000000000bff40000000000004012000000000000c00e000000000000400c000000000000c0020000000000004016000000000000c006000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }

    /** The geographic SRID does not swap X and Y. */
    public function testGeographyPreservesAxisOrder(): void
    {
        $curve = new GeographicCircularString([[2, 40], [3, 41], [4, 40]], 4326);

        self::assertSame(
            '010800000003000000000000000000004000000000000044400000000000000840000000000080444000000000000010400000000000004440',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '010800000003000000000000000000004000000000000044400000000000000840000000000080444000000000000010400000000000004440',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '000000000800000003400000000000000040440000000000004008000000000000404480000000000040100000000000004044000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }

    /** Preserve shared arc endpoints once and retain the complete point order. */
    public function testMultipleArcs(): void
    {
        $curve = new CircularString([[0, 0], [1, 1], [2, 0], [3, -1], [4, 0]], 4326);

        self::assertSame(
            '01080000000500000000000000000000000000000000000000000000000000f03f000000000000f03f000000000000004000000000000000000000000000000840000000000000f0bf00000000000010400000000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '01080000000500000000000000000000000000000000000000000000000000f03f000000000000f03f000000000000004000000000000000000000000000000840000000000000f0bf00000000000010400000000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '000000000800000005000000000000000000000000000000003ff00000000000003ff0000000000000400000000000000000000000000000004008000000000000bff000000000000040100000000000000000000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }

    /** Mutable input keeps its coordinates and SRID after encoding. */
    public function testMutableInputIsUnchanged(): void
    {
        $curve = new MutableCircularString([[0, 0], [1, 1], [2, 0]], 4326);

        self::assertSame(
            '01080000000300000000000000000000000000000000000000000000000000f03f000000000000f03f00000000000000400000000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '01080000000300000000000000000000000000000000000000000000000000f03f000000000000f03f00000000000000400000000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '000000000800000003000000000000000000000000000000003ff00000000000003ff000000000000040000000000000000000000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
        self::assertSame([[0, 0], [1, 1], [2, 0]], $curve->toArray());
        self::assertSame(4326, $curve->getSrid());
    }

    /** Retain nested curve headers and zero counts; only the EWKB root carries SRID. */
    public function testNestedCollection(): void
    {
        $collection = new GeometryCollection(4326, [
            new GeometryCollection(4326, [
                new CircularString([[0, 0], [1, 1], [2, 0]], 4326),
                new CircularString([], 4326),
            ]),
        ]);

        self::assertSame(
            '01070000000100000001070000000200000001080000000300000000000000000000000000000000000000000000000000f03f000000000000f03f00000000000000400000000000000000010800000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($collection))
        );
        self::assertSame(
            '000000000700000001000000000700000002000000000800000003000000000000000000000000000000003ff00000000000003ff000000000000040000000000000000000000000000000000000000800000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($collection))
        );
    }

    /** Preserve all three control points and the format-specific SRID contract. */
    public function testSingleArc(): void
    {
        $curve = new CircularString([[0, 0], [1, 1], [2, 0]], 4326);

        self::assertSame(
            '01080000000300000000000000000000000000000000000000000000000000f03f000000000000f03f00000000000000400000000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '01080000000300000000000000000000000000000000000000000000000000f03f000000000000f03f00000000000000400000000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '000000000800000003000000000000000000000000000000003ff00000000000003ff000000000000040000000000000000000000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }

    /** Preserve M as a measure rather than a Z ordinate. */
    public function testXym(): void
    {
        $curve = new CircularStringM([[0, 0, 6], [1, 1, 7], [2, 0, 8]], 4326);

        self::assertSame(
            '01d807000003000000000000000000000000000000000000000000000000001840000000000000f03f000000000000f03f0000000000001c40000000000000004000000000000000000000000000002040',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '01d807000003000000000000000000000000000000000000000000000000001840000000000000f03f000000000000f03f0000000000001c40000000000000004000000000000000000000000000002040',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '00000007d8000000030000000000000000000000000000000040180000000000003ff00000000000003ff0000000000000401c000000000000400000000000000000000000000000004020000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }

    /** Preserve XYZ ordinates with the correct dimension identifier. */
    public function testXyz(): void
    {
        $curve = new CircularStringZ([[0, 0, 3], [1, 1, 4], [2, 0, 5]], 4326);

        self::assertSame(
            '01f003000003000000000000000000000000000000000000000000000000000840000000000000f03f000000000000f03f0000000000001040000000000000004000000000000000000000000000001440',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '01f003000003000000000000000000000000000000000000000000000000000840000000000000f03f000000000000f03f0000000000001040000000000000004000000000000000000000000000001440',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '00000003f0000000030000000000000000000000000000000040080000000000003ff00000000000003ff00000000000004010000000000000400000000000000000000000000000004014000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }

    /** Preserve XYZM ordinate order and both dimension indicators. */
    public function testXyzm(): void
    {
        $curve = new CircularStringZm([[0, 0, 3, 6], [1, 1, 4, 7], [2, 0, 5, 8]], 4326);

        self::assertSame(
            '01c00b0000030000000000000000000000000000000000000000000000000008400000000000001840000000000000f03f000000000000f03f00000000000010400000000000001c400000000000000040000000000000000000000000000014400000000000002040',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '01c00b0000030000000000000000000000000000000000000000000000000008400000000000001840000000000000f03f000000000000f03f00000000000010400000000000001c400000000000000040000000000000000000000000000014400000000000002040',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '0000000bc00000000300000000000000000000000000000000400800000000000040180000000000003ff00000000000003ff00000000000004010000000000000401c0000000000004000000000000000000000000000000040140000000000004020000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }

    /** A zero SRID does not introduce an SRID field or flag. */
    public function testZeroSrid(): void
    {
        $curve = new CircularString([[0, 0], [1, 1], [2, 0]], 0);

        self::assertSame(
            '01080000000300000000000000000000000000000000000000000000000000f03f000000000000f03f00000000000000400000000000000000',
            bin2hex(new WkbBinaryStrategy()->encode($curve))
        );
        self::assertSame(
            '01080000000300000000000000000000000000000000000000000000000000f03f000000000000f03f00000000000000400000000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($curve))
        );
        self::assertSame(
            '000000000800000003000000000000000000000000000000003ff00000000000003ff000000000000040000000000000000000000000000000',
            bin2hex(new WkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($curve))
        );
    }
}
