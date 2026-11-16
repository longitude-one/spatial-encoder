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

use LongitudeOne\SpatialEncoder\Strategy\EwktTextStrategy;
use LongitudeOne\SpatialEncoder\Strategy\Wkt\WktTypeEncoder;
use LongitudeOne\SpatialEncoder\Strategy\WktTextStrategy;
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
 * Preserve circular-string control points in both textual strategies.
 *
 * @internal
 */
#[CoversClass(WktTextStrategy::class)]
#[CoversClass(EwktTextStrategy::class)]
#[CoversClass(WktTypeEncoder::class)]
class CircularStringTextTest extends TestCase
{
    /** Preserve the closed representation and its reference metadata. */
    public function testClosed(): void
    {
        $curve = new CircularString([[0, 0], [1, 1], [2, 0], [1, -1], [0, 0]], 4326);

        self::assertSame('CIRCULARSTRING (0 0, 1 1, 2 0, 1 -1, 0 0)', new WktTextStrategy()->encode($curve));
        self::assertSame('SRID=4326;CIRCULARSTRING (0 0, 1 1, 2 0, 1 -1, 0 0)', new EwktTextStrategy()->encode($curve));
    }

    /** Collection recursion retains curved member types and prefixes only the root. */
    public function testCollection(): void
    {
        $collection = new GeometryCollection(4326, [
            new CircularString([[0, 0], [1, 1], [2, 0]], 4326),
            new CircularString([], 4326),
        ]);

        self::assertSame(
            'GEOMETRYCOLLECTION (CIRCULARSTRING (0 0, 1 1, 2 0), CIRCULARSTRING EMPTY)',
            new WktTextStrategy()->encode($collection)
        );
        self::assertSame(
            'SRID=4326;GEOMETRYCOLLECTION (CIRCULARSTRING (0 0, 1 1, 2 0), CIRCULARSTRING EMPTY)',
            new EwktTextStrategy()->encode($collection)
        );
    }

    /** Preserve the collinear representation and its reference metadata. */
    public function testCollinear(): void
    {
        $curve = new CircularString([[0, 0], [1, 0], [2, 0]], 4326);

        self::assertSame('CIRCULARSTRING (0 0, 1 0, 2 0)', new WktTextStrategy()->encode($curve));
        self::assertSame('SRID=4326;CIRCULARSTRING (0 0, 1 0, 2 0)', new EwktTextStrategy()->encode($curve));
    }

    /** Preserve the empty representation and its reference metadata. */
    public function testEmpty(): void
    {
        $curve = new CircularString([], 4326);

        self::assertSame('CIRCULARSTRING EMPTY', new WktTextStrategy()->encode($curve));
        self::assertSame('SRID=4326;CIRCULARSTRING EMPTY', new EwktTextStrategy()->encode($curve));
    }

    /** Preserve the emptym representation and its reference metadata. */
    public function testEmptyM(): void
    {
        $curve = new CircularStringM([], 4326);

        self::assertSame('CIRCULARSTRING M EMPTY', new WktTextStrategy()->encode($curve));
        self::assertSame('SRID=4326;CIRCULARSTRING M EMPTY', new EwktTextStrategy()->encode($curve));
    }

    /** Preserve the emptyz representation and its reference metadata. */
    public function testEmptyZ(): void
    {
        $curve = new CircularStringZ([], 4326);

        self::assertSame('CIRCULARSTRING Z EMPTY', new WktTextStrategy()->encode($curve));
        self::assertSame('SRID=4326;CIRCULARSTRING Z EMPTY', new EwktTextStrategy()->encode($curve));
    }

    /** Preserve the emptyzm representation and its reference metadata. */
    public function testEmptyZm(): void
    {
        $curve = new CircularStringZm([], 4326);

        self::assertSame('CIRCULARSTRING ZM EMPTY', new WktTextStrategy()->encode($curve));
        self::assertSame('SRID=4326;CIRCULARSTRING ZM EMPTY', new EwktTextStrategy()->encode($curve));
    }

    /** Preserve the geography representation and its reference metadata. */
    public function testGeography(): void
    {
        $curve = new GeographicCircularString([[2, 40], [3, 41], [4, 40]], 4326);

        self::assertSame('CIRCULARSTRING (2 40, 3 41, 4 40)', new WktTextStrategy()->encode($curve));
        self::assertSame('SRID=4326;CIRCULARSTRING (2 40, 3 41, 4 40)', new EwktTextStrategy()->encode($curve));
    }

    /** Preserve the m representation and its reference metadata. */
    public function testM(): void
    {
        $curve = new CircularStringM([[0, 0, 6], [1, 1, 7], [2, 0, 8]], 4326);

        self::assertSame('CIRCULARSTRING M (0 0 6, 1 1 7, 2 0 8)', new WktTextStrategy()->encode($curve));
        self::assertSame('SRID=4326;CIRCULARSTRING M (0 0 6, 1 1 7, 2 0 8)', new EwktTextStrategy()->encode($curve));
    }

    /** Preserve the multiplearcs representation and its reference metadata. */
    public function testMultipleArcs(): void
    {
        $curve = new CircularString([[0, 0], [1, 1], [2, 0], [3, -1], [4, 0]], 4326);

        self::assertSame('CIRCULARSTRING (0 0, 1 1, 2 0, 3 -1, 4 0)', new WktTextStrategy()->encode($curve));
        self::assertSame('SRID=4326;CIRCULARSTRING (0 0, 1 1, 2 0, 3 -1, 4 0)', new EwktTextStrategy()->encode($curve));
    }

    /** Preserve the mutable representation and its reference metadata. */
    public function testMutable(): void
    {
        $curve = new MutableCircularString([[0, 0], [1, 1], [2, 0]], 4326);

        self::assertSame('CIRCULARSTRING (0 0, 1 1, 2 0)', new WktTextStrategy()->encode($curve));
        self::assertSame('SRID=4326;CIRCULARSTRING (0 0, 1 1, 2 0)', new EwktTextStrategy()->encode($curve));
    }

    /** Preserve the singlearc representation and its reference metadata. */
    public function testSingleArc(): void
    {
        $curve = new CircularString([[0, 0], [1, 1], [2, 0]], 4326);

        self::assertSame('CIRCULARSTRING (0 0, 1 1, 2 0)', new WktTextStrategy()->encode($curve));
        self::assertSame('SRID=4326;CIRCULARSTRING (0 0, 1 1, 2 0)', new EwktTextStrategy()->encode($curve));
    }

    /** Preserve the z representation and its reference metadata. */
    public function testZ(): void
    {
        $curve = new CircularStringZ([[0, 0, 3], [1, 1, 4], [2, 0, 5]], 4326);

        self::assertSame('CIRCULARSTRING Z (0 0 3, 1 1 4, 2 0 5)', new WktTextStrategy()->encode($curve));
        self::assertSame('SRID=4326;CIRCULARSTRING Z (0 0 3, 1 1 4, 2 0 5)', new EwktTextStrategy()->encode($curve));
    }

    /** SRID zero does not introduce an EWKT prefix. */
    public function testZeroSrid(): void
    {
        $curve = new CircularString([[0, 0], [1, 1], [2, 0]], 0);

        self::assertSame('CIRCULARSTRING (0 0, 1 1, 2 0)', new EwktTextStrategy()->encode($curve));
        self::assertSame('CIRCULARSTRING EMPTY', new EwktTextStrategy()->encode(new CircularString([], 0)));
    }

    /** Preserve the zm representation and its reference metadata. */
    public function testZm(): void
    {
        $curve = new CircularStringZm([[0, 0, 3, 6], [1, 1, 4, 7], [2, 0, 5, 8]], 4326);

        self::assertSame('CIRCULARSTRING ZM (0 0 3 6, 1 1 4 7, 2 0 5 8)', new WktTextStrategy()->encode($curve));
        self::assertSame('SRID=4326;CIRCULARSTRING ZM (0 0 3 6, 1 1 4 7, 2 0 5 8)', new EwktTextStrategy()->encode($curve));
    }
}
