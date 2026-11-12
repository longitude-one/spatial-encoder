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

namespace LongitudeOne\SpatialEncoder\Strategy;

use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;

/**
 * Encodes spatial objects to a binary or textual representation.
 */
interface StrategyInterface
{
    /**
     * Encode a spatial object to the strategy's output format.
     *
     * @param SpatialInterface $spatial the spatial object to encode
     */
    public function encode(SpatialInterface $spatial): mixed;
}
