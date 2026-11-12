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

namespace LongitudeOne\SpatialEncoder\Exception;

/**
 * A recognized spatial type cannot be represented by the selected encoding strategy.
 */
class UnsupportedSpatialTypeException extends \Exception implements ExceptionInterface
{
}
