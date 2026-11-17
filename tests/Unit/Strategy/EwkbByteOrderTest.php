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

use LongitudeOne\Core\Enum\ByteOrderEnum;
use LongitudeOne\SpatialEncoder\Strategy\EwkbBinaryStrategy;
use LongitudeOne\SpatialEncoder\Strategy\Wkb\ByteOrderConfigurationTrait;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\PointImmutable as Point;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\PointImmutable as PointM;
use LongitudeOne\SpatialTypes\Types\Dimension3z\Geometry\PointImmutable as PointZ;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\GeometryCollectionImmutable as GeometryCollection;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\LineStringImmutable as LineString;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Geometry\PointImmutable as PointZm;
use LongitudeOne\SpatialTypes\Types\Dimension4zm\Point as MutablePointZm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/** Enum-only configuration preserves the existing little-endian representation. */
#[CoversClass(EwkbBinaryStrategy::class)]
#[CoversTrait(ByteOrderConfigurationTrait::class)]
final class EwkbByteOrderTest extends TestCase
{
    /** Big-endian construction succeeds and writes the complete numeric payload. */
    public function testBigEndian(): void
    {
        $geometry = new Point(1, 2, 4326);

        self::assertSame(
            '0020000001000010e63ff00000000000004000000000000000',
            bin2hex(new EwkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($geometry))
        );
    }

    /** Defaults and explicit little-endian preserve literal reference bytes. */
    public function testEmpty(): void
    {
        $geometry = new LineString([], 4326);
        $expected = '01020000e0e610000000000000';

        self::assertSame(
            '00e0000002000010e600000000',
            bin2hex(new EwkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($geometry))
        );

        self::assertSame($expected, bin2hex(new EwkbBinaryStrategy()->encode($geometry)));
        self::assertSame($expected, bin2hex(new EwkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($geometry)));
    }

    /** Preserve fractional values, signs, ordinate order and mutable input state. */
    public function testFractionalMutableCoordinates(): void
    {
        $geometry = new MutablePointZm(1.5, -2.25, 3.5, -4.75, 4326);

        self::assertSame(
            '00e0000001000010e63ff8000000000000c002000000000000400c000000000000c013000000000000',
            bin2hex(new EwkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($geometry))
        );
        self::assertSame(
            '01010000e0e6100000000000000000f83f00000000000002c00000000000000c4000000000000013c0',
            bin2hex(new EwkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($geometry))
        );
        self::assertSame([1.5, -2.25, 3.5, -4.75], $geometry->toArray());
        self::assertSame(4326, $geometry->getSrid());
    }

    /** Defaults and explicit little-endian preserve literal reference bytes. */
    public function testNested(): void
    {
        $geometry = new GeometryCollection(4326, [
            new GeometryCollection(4326, [new PointZm(1, 2, 3, 4, 4326)]),
            new LineString([], 4326),
        ]);
        $expected = '01070000e0e61000000200000001070000c00100000001010000c0000000000000f03f00000000000000400000000000000840000000000000104001020000c000000000';

        self::assertSame(
            '00e0000007000010e60000000200c00000070000000100c00000013ff000000000000040000000000000004008000000000000401000000000000000c000000200000000',
            bin2hex(new EwkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($geometry))
        );

        self::assertSame($expected, bin2hex(new EwkbBinaryStrategy()->encode($geometry)));
        self::assertSame($expected, bin2hex(new EwkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($geometry)));
    }

    /** Raw values cannot substitute for the core enum. */
    public function testRejectsArray(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new EwkbBinaryStrategy(['byte_order' => []]);
    }

    /** Raw values cannot substitute for the core enum. */
    public function testRejectsBoolean(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new EwkbBinaryStrategy(['byte_order' => true]);
    }

    /** Raw values cannot substitute for the core enum. */
    public function testRejectsInteger(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new EwkbBinaryStrategy(['byte_order' => 1]);
    }

    /** Raw values cannot substitute for the core enum. */
    public function testRejectsNull(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new EwkbBinaryStrategy(['byte_order' => null]);
    }

    /** Raw values cannot substitute for the core enum. */
    public function testRejectsObject(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new EwkbBinaryStrategy(['byte_order' => new \stdClass()]);
    }

    /** Raw values cannot substitute for the core enum. */
    public function testRejectsString(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new EwkbBinaryStrategy(['byte_order' => 'LITTLE_ENDIAN']);
    }

    /** Raw values cannot substitute for the core enum. */
    public function testRejectsZero(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new EwkbBinaryStrategy(['byte_order' => 0]);
    }

    /** Defaults and explicit little-endian preserve literal reference bytes. */
    public function testXy(): void
    {
        $geometry = new Point(1, 2, 4326);
        $expected = '0101000020e6100000000000000000f03f0000000000000040';

        self::assertSame(
            '0020000001000010e63ff00000000000004000000000000000',
            bin2hex(new EwkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($geometry))
        );

        self::assertSame($expected, bin2hex(new EwkbBinaryStrategy()->encode($geometry)));
        self::assertSame($expected, bin2hex(new EwkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($geometry)));
    }

    /** Defaults and explicit little-endian preserve literal reference bytes. */
    public function testXym(): void
    {
        $geometry = new PointM(1, 2, 4, 4326);
        $expected = '0101000060e6100000000000000000f03f00000000000000400000000000001040';

        self::assertSame(
            '0060000001000010e63ff000000000000040000000000000004010000000000000',
            bin2hex(new EwkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($geometry))
        );

        self::assertSame($expected, bin2hex(new EwkbBinaryStrategy()->encode($geometry)));
        self::assertSame($expected, bin2hex(new EwkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($geometry)));
    }

    /** Defaults and explicit little-endian preserve literal reference bytes. */
    public function testXyz(): void
    {
        $geometry = new PointZ(1, 2, 3, 4326);
        $expected = '01010000a0e6100000000000000000f03f00000000000000400000000000000840';

        self::assertSame(
            '00a0000001000010e63ff000000000000040000000000000004008000000000000',
            bin2hex(new EwkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($geometry))
        );

        self::assertSame($expected, bin2hex(new EwkbBinaryStrategy()->encode($geometry)));
        self::assertSame($expected, bin2hex(new EwkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($geometry)));
    }

    /** Defaults and explicit little-endian preserve literal reference bytes. */
    public function testXyzm(): void
    {
        $geometry = new PointZm(1, 2, 3, 4, 4326);
        $expected = '01010000e0e6100000000000000000f03f000000000000004000000000000008400000000000001040';

        self::assertSame(
            '00e0000001000010e63ff0000000000000400000000000000040080000000000004010000000000000',
            bin2hex(new EwkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($geometry))
        );

        self::assertSame($expected, bin2hex(new EwkbBinaryStrategy()->encode($geometry)));
        self::assertSame($expected, bin2hex(new EwkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($geometry)));
    }

    /** Defaults and explicit little-endian preserve literal reference bytes. */
    public function testZeroSrid(): void
    {
        $geometry = new Point(1, 2, 0);
        $expected = '0101000000000000000000f03f0000000000000040';

        self::assertSame(
            '00000000013ff00000000000004000000000000000',
            bin2hex(new EwkbBinaryStrategy(['byte_order' => ByteOrderEnum::BIG_ENDIAN])->encode($geometry))
        );

        self::assertSame($expected, bin2hex(new EwkbBinaryStrategy()->encode($geometry)));
        self::assertSame($expected, bin2hex(new EwkbBinaryStrategy(['byte_order' => ByteOrderEnum::LITTLE_ENDIAN])->encode($geometry)));
    }
}
