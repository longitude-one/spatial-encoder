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
 * An error raised while encoding JSON, retaining the original exception.
 */
class JsonEncodingException extends \RuntimeException implements ExceptionInterface
{
}
