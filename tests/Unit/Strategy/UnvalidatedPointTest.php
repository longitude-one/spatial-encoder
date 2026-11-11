<?php
/**
 * This file is part of the spatial-encoder project.
 *
 * PHP 8.4 | 8.5
 *
 * Copyright Alexandre Tranchant <alexandre.tranchant@gmail.com> 2026
 * Copyright Longitude One 2026
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

declare(strict_types=1);

namespace LongitudeOne\SpatialEncoder\Tests\Unit\Strategy;

use LongitudeOne\SpatialEncoder\Strategy\EwkbBinaryStrategy;
use LongitudeOne\SpatialEncoder\Strategy\EwktTextStrategy;
use LongitudeOne\SpatialEncoder\Strategy\GeoJsonStrategy;
use LongitudeOne\SpatialEncoder\Strategy\MySQLBinaryStrategy;
use LongitudeOne\SpatialEncoder\Strategy\WkbBinaryStrategy;
use LongitudeOne\SpatialEncoder\Strategy\WktTextStrategy;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\PointImmutable;
use LongitudeOne\SpatialTypes\Types\Dimension2\Point;
use PHPUnit\Framework\TestCase;

/**
 * Observed outcomes for unvalidated coordinates, not a geometry validity guarantee.
 *
 * @internal
 */
class UnvalidatedPointTest extends TestCase
{
    /** EWKB shares the native conversion and omits an SRID flag for zero. */
    public function testEwkbNullOrdinate(): void
    {
        static::assertSame('0101000000000000000000f03f0000000000000000', bin2hex((new EwkbBinaryStrategy())->encode(new Point(1, null))));
        static::assertSame('0101000000000000000000f03f0000000000000000', bin2hex((new EwkbBinaryStrategy())->encode(new PointImmutable(1, null))));
    }

    /** JSON accepts null even though an RFC 7946 position requires numbers. */
    public function testGeoJsonNullOrdinate(): void
    {
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[1,null]}', (new GeoJsonStrategy())->encode(new Point(1, null)));
        static::assertJsonStringEqualsJsonString('{"type":"Point","coordinates":[1,null]}', (new GeoJsonStrategy())->encode(new PointImmutable(1, null)));
    }

    /** SRID formatting does not turn an incomplete point into a valid point. */
    public function testImmutableEwktNullOrdinate(): void
    {
        $this->expectException(\TypeError::class);
        (new EwktTextStrategy())->encode(new PointImmutable(1, null));
    }

    /** The same error propagates from an immutable partial point. */
    public function testImmutableWktNullOrdinate(): void
    {
        $this->expectException(\TypeError::class);
        (new WktTextStrategy())->encode(new PointImmutable(1, null));
    }

    /** EWKT does not intercept the inherited numeric formatter error. */
    public function testMutableEwktNullOrdinate(): void
    {
        $this->expectException(\TypeError::class);
        (new EwktTextStrategy())->encode(new Point(1, null));
    }

    /** WKT exposes the numeric formatter's failure on a mutable partial point. */
    public function testMutableWktNullOrdinate(): void
    {
        $this->expectException(\TypeError::class);
        (new WktTextStrategy())->encode(new Point(1, null));
    }

    /** MySQL adds a zero SRID prefix to the coerced coordinate payload. */
    public function testMysqlNullOrdinate(): void
    {
        static::assertSame('000000000101000000000000000000f03f0000000000000000', bin2hex((new MySQLBinaryStrategy())->encode(new Point(1, null))));
        static::assertSame('000000000101000000000000000000f03f0000000000000000', bin2hex((new MySQLBinaryStrategy())->encode(new PointImmutable(1, null))));
    }

    /** PHP's native pack conversion writes zero for null in the ISO payload. */
    public function testWkbNullOrdinate(): void
    {
        $mutable = new Point(1, null);
        $immutable = new PointImmutable(1, null);
        static::assertSame('0101000000000000000000f03f0000000000000000', bin2hex((new WkbBinaryStrategy())->encode($mutable)));
        static::assertSame('0101000000000000000000f03f0000000000000000', bin2hex((new WkbBinaryStrategy())->encode($immutable)));
        static::assertSame([1, null], $mutable->toArray());
        static::assertSame([1, null], $immutable->toArray());
    }
}
