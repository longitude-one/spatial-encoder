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
 * This exception is thrown when the spatial interface is not supported.
 *
 * It should not happen, but it could happen if spatial-interface library is updated.
 *
 * @internal
 */
class UnsupportedSpatialInterfaceException extends \Exception implements ExceptionInterface
{
}
