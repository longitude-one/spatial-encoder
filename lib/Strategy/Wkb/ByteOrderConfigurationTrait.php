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

namespace LongitudeOne\SpatialEncoder\Strategy\Wkb;

use LongitudeOne\Core\Enum\ByteOrderEnum;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Shared construction-time byte-order configuration for WKB and EWKB.
 *
 * @internal
 */
trait ByteOrderConfigurationTrait
{
    /**
     * Declare the enum-only byte-order option.
     *
     * @param OptionsResolver $resolver the local configuration resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('byte_order', ByteOrderEnum::LITTLE_ENDIAN);
        $resolver->setAllowedTypes('byte_order', ByteOrderEnum::class);
    }

    /** Return the resolved order without exposing an untyped option value. */
    private function byteOrder(): ByteOrderEnum
    {
        return ByteOrderEnum::BIG_ENDIAN === $this->options['byte_order'] ? ByteOrderEnum::BIG_ENDIAN : ByteOrderEnum::LITTLE_ENDIAN;
    }

    /** Select the native pack format while retaining its existing ordinate conversion. */
    private function doubleFormat(): string
    {
        return ByteOrderEnum::BIG_ENDIAN === $this->byteOrder() ? 'E*' : 'e*';
    }

    /** Write the selected WKB byte-order marker. */
    private function writeByteOrder(): string
    {
        return pack('C', $this->byteOrder()->value);
    }

    /**
     * Write the canonical quiet NaN for each EMPTY point ordinate.
     *
     * @param int $dimension the number of coordinate ordinates
     */
    private function writeEmptyPoint(int $dimension): string
    {
        $nan = ByteOrderEnum::BIG_ENDIAN === $this->byteOrder() ? '7ff8000000000000' : '000000000000f87f';

        return str_repeat(pack('H*', $nan), $dimension);
    }

    /**
     * Write an unsigned 32-bit integer in the selected order.
     *
     * @param int $value the numeric field value
     */
    private function writeUnsignedInteger(int $value): string
    {
        return pack(ByteOrderEnum::BIG_ENDIAN === $this->byteOrder() ? 'N' : 'V', $value);
    }
}
