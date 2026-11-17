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

use LogicException as UnsupportedByteOrderException;
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
     * Resolve byte order before encoding is possible.
     *
     * @param array<string, mixed> $options strategy-specific configuration
     *
     * @throws UnsupportedByteOrderException when big-endian output is requested before it is implemented
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        if (ByteOrderEnum::BIG_ENDIAN === $this->options['byte_order']) {
            throw new UnsupportedByteOrderException('Big-endian encoding is not implemented yet.');
        }
    }

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
}
