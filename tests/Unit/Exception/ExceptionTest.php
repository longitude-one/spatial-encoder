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

namespace LongitudeOne\SpatialEncoder\Tests\Unit\Exception;

use LongitudeOne\SpatialEncoder\Exception\ExceptionInterface;
use LongitudeOne\SpatialEncoder\Exception\UnavailableResourceException;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedDimensionException;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialInterfaceException;
use LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialTypeException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \LongitudeOne\SpatialEncoder\Exception\UnavailableResourceException
 * @covers \LongitudeOne\SpatialEncoder\Exception\UnsupportedDimensionException
 * @covers \LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialInterfaceException
 * @covers \LongitudeOne\SpatialEncoder\Exception\UnsupportedSpatialTypeException
 */
class ExceptionTest extends TestCase
{
    /**
     * @return \Generator<string, array{0: object}, null, void>
     */
    public static function exceptionProvider(): \Generator
    {
        yield 'UnavailableResourceException' => [new UnavailableResourceException()];

        yield 'UnsupportedSpatialTypeException' => [new UnsupportedSpatialTypeException()];

        yield 'UnsupportedDimensionException' => [new UnsupportedDimensionException()];

        yield 'UnsupportedSpatialInterfaceException' => [new UnsupportedSpatialInterfaceException()];
    }

    /**
     * Let's check that all internal exceptions are instances of ExceptionInterface.
     *
     * @param object $exception exception to test
     */
    #[DataProvider('exceptionProvider')]
    public function testUnavailableResourceException(object $exception): void
    {
        self::assertInstanceOf(\Exception::class, $exception);
        self::assertInstanceOf(ExceptionInterface::class, $exception);
    }
}
