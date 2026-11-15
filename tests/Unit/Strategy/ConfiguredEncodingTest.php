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

use LongitudeOne\SpatialEncoder\Encoder;
use LongitudeOne\SpatialEncoder\Strategy\EwkbBinaryStrategy;
use LongitudeOne\SpatialEncoder\Strategy\EwktTextStrategy;
use LongitudeOne\SpatialEncoder\Strategy\GeoJsonStrategy;
use LongitudeOne\SpatialEncoder\Strategy\MySQLBinaryStrategy;
use LongitudeOne\SpatialEncoder\Strategy\WkbBinaryStrategy;
use LongitudeOne\SpatialEncoder\Strategy\WktTextStrategy;
use LongitudeOne\SpatialEncoder\Tests\Fixture\ExternalStrategy;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\PointImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

/** Format options change presentation without changing coordinates or reference. */
final class ConfiguredEncodingTest extends TestCase
{
    /** The default format stays byte-for-byte compatible. */
    public function testDefaultGeoJson(): void
    {
        self::assertSame('{"type":"Point","coordinates":[1,2]}', new GeoJsonStrategy()->encode(new PointImmutable(1, 2, 4326)));
    }

    /** EWKB does not accept JSON-specific options. */
    public function testEwkbRejectsUnrelatedOptions(): void
    {
        $this->expectException(UndefinedOptionsException::class);
        new EwkbBinaryStrategy(['pretty_print' => true]);
    }

    /** EWKT inherits WKT option validation. */
    public function testEwktRejectsUnrelatedOptions(): void
    {
        $this->expectException(UndefinedOptionsException::class);
        new EwktTextStrategy(['pretty_print' => true]);
    }

    /** A third-party strategy declares options without inheriting the optional base. */
    public function testExternalStrategy(): void
    {
        $point = new PointImmutable(1, 2, 4326);
        self::assertSame(['coordinates' => [1, 2]], new Encoder(new ExternalStrategy())->encode($point));
        self::assertSame(['coordinates' => [1, 2], 'srid' => 4326], new Encoder(new ExternalStrategy(['include_srid' => true]))->encode($point));
    }

    /** JSON formatting requires a boolean. */
    public function testInvalidJsonOption(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new GeoJsonStrategy(['pretty_print' => 'yes']);
    }

    /** MySQL validates options before allocating its encoding helpers. */
    public function testMysqlRejectsUnrelatedOptions(): void
    {
        $this->expectException(UndefinedOptionsException::class);
        new MySQLBinaryStrategy(['pretty_print' => true]);
    }

    /** Pretty printing changes whitespace only and never mutates the input. */
    public function testPrettyGeoJsonThroughEncoder(): void
    {
        $point = new PointImmutable(1, 2, 4326);
        $result = new Encoder(new GeoJsonStrategy(['pretty_print' => true]))->encode($point);
        self::assertIsString($result);
        self::assertStringContainsString("\n", $result);
        self::assertSame(['type' => 'Point', 'coordinates' => [1, 2]], json_decode($result, true, flags: \JSON_THROW_ON_ERROR));
        self::assertSame([1, 2], $point->toArray());
        self::assertSame(4326, $point->getSrid());
    }

    /** ISO WKB does not accept JSON-specific options. */
    public function testWkbRejectsUnrelatedOptions(): void
    {
        $this->expectException(UndefinedOptionsException::class);
        new WkbBinaryStrategy(['pretty_print' => true]);
    }

    /** WKT does not accept JSON-specific options. */
    public function testWktRejectsUnrelatedOptions(): void
    {
        $this->expectException(UndefinedOptionsException::class);
        new WktTextStrategy(['pretty_print' => true]);
    }
}
