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

namespace LongitudeOne\SpatialEncoder\Tests\Unit;

use LongitudeOne\SpatialEncoder\Encoder;
use LongitudeOne\SpatialEncoder\Strategy\EwkbBinaryStrategy;
use LongitudeOne\SpatialEncoder\Strategy\MySQLBinaryStrategy;
use LongitudeOne\SpatialEncoder\Strategy\WkbBinaryStrategy;
use LongitudeOne\SpatialEncoder\Strategy\WktTextStrategy;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\PointImmutable as Point;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \LongitudeOne\SpatialEncoder\Encoder
 */
class EncoderTest extends TestCase
{
    /**
     * Test the binary encoder with a mysql strategy.
     */
    public function testBinaryEncoderWithMysqlStrategy(): void
    {
        $strategy = new MySQLBinaryStrategy();
        $encoder = new Encoder($strategy);
        $point = new Point(1, 2, 4326);
        self::assertSame(
            $strategy->encode($point),
            $encoder->encode($point)
        );
    }

    /**
     * Test the binary encoder with the Well-Known Binary strategy.
     */
    public function testBinaryEncoderWithWkbStrategy(): void
    {
        $strategy = new WkbBinaryStrategy();
        $encoder = new Encoder($strategy);
        $point = new Point(1, 2, 4326);
        self::assertSame(
            $strategy->encode($point),
            $encoder->encode($point)
        );
    }

    /**
     * Test the binary encoder with the Well-Known Binary strategy.
     */
    public function testEwkbEncoderWithEwkbStrategy(): void
    {
        $badStrategy = new MySQLBinaryStrategy();
        $strategy = new EwkbBinaryStrategy();
        $encoder = new Encoder($badStrategy);
        self::assertSame($badStrategy, $encoder->getStrategy());
        $encoder->setStrategy($strategy);
        $point = new Point(1, 2, 4326);
        self::assertSame(
            $strategy->encode($point),
            $encoder->encode($point)
        );
        self::assertNotSame(
            $badStrategy->encode($point),
            $encoder->encode($point)
        );
    }

    /** Test text strategies can be supplied and exchanged with binary strategies. */
    public function testTextEncoder(): void
    {
        $point = new Point(1, 2, 4326);
        $textStrategy = new WktTextStrategy();
        $binaryStrategy = new WkbBinaryStrategy();
        $encoder = new Encoder($textStrategy);
        self::assertSame($textStrategy, $encoder->getStrategy());
        self::assertSame('POINT (1 2)', $encoder->encode($point));
        self::assertSame($encoder, $encoder->setStrategy($binaryStrategy));
        self::assertSame($binaryStrategy->encode($point), $encoder->encode($point));
        $encoder->setStrategy($textStrategy);
        self::assertSame('POINT (1 2)', $encoder->encode($point));
    }
}
