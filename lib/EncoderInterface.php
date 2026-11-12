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

namespace LongitudeOne\SpatialEncoder;

use LongitudeOne\SpatialEncoder\Strategy\StrategyInterface;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;

interface EncoderInterface
{
    /**
     * Encoder constructor.
     *
     * @param StrategyInterface $strategy the strategy to use
     */
    public function __construct(StrategyInterface $strategy);

    /**
     * Encode a spatial interface to another representation.
     *
     * @param SpatialInterface $spatial the spatial interface to encode
     *
     * @return string a string representing the spatial interface in the implemented representation
     */
    public function encode(SpatialInterface $spatial): mixed;

    /**
     * Get the current strategy.
     *
     * @return StrategyInterface the strategy to use
     */
    public function getStrategy(): StrategyInterface;

    /**
     * Set the strategy to use.
     *
     * @param StrategyInterface $strategy the strategy to use
     *
     * @return self the current instance
     */
    public function setStrategy(StrategyInterface $strategy): self;
}
