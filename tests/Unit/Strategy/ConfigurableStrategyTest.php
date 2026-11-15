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

use LongitudeOne\SpatialEncoder\Strategy\AbstractConfigurableStrategy;
use LongitudeOne\SpatialEncoder\Tests\Fixture\ConfigurableStrategy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

/** Configuration is resolved at construction and independent for every instance. */
final class ConfigurableStrategyTest extends TestCase
{
    /** Defaults are available without caller configuration. */
    public function testDefaults(): void
    {
        self::assertSame(['label' => 'compact'], new ConfigurableStrategy()->configuration());
    }

    /** Invalid types fail before execution. */
    public function testInvalidType(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new ConfigurableStrategy(['label' => 42]);
    }

    /** Unsupported values fail before execution. */
    public function testInvalidValue(): void
    {
        $this->expectException(InvalidOptionsException::class);
        new ConfigurableStrategy(['label' => 'unsupported']);
    }

    /** Allowed values are normalized without leaking to the next instance. */
    public function testNormalizationAndIsolation(): void
    {
        self::assertSame(['label' => 'expanded'], new ConfigurableStrategy(['label' => 'EXPANDED'])->configuration());
        self::assertSame(['label' => 'compact'], new ConfigurableStrategy()->configuration());
    }

    /** Only resolved readonly options are retained by the optional base class. */
    public function testResolverIsNotRetained(): void
    {
        $class = new \ReflectionClass(AbstractConfigurableStrategy::class);
        self::assertCount(1, $class->getProperties());
        self::assertTrue($class->getProperty('options')->isReadOnly());
        self::assertTrue($class->getProperty('options')->isProtected());
    }

    /** Unknown options fail before execution. */
    public function testUnknownOption(): void
    {
        $this->expectException(UndefinedOptionsException::class);
        new ConfigurableStrategy(['unknown' => true]);
    }
}
