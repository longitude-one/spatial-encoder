# Spatial Encoder

[![Release](https://img.shields.io/github/v/release/longitude-one/spatial-encoder?include_prereleases&sort=semver&label=release)](https://github.com/longitude-one/spatial-encoder/releases)
[![PHP](https://img.shields.io/packagist/php-v/longitude-one/spatial-encoder?label=php)](https://packagist.org/packages/longitude-one/spatial-encoder)
[![License](https://img.shields.io/packagist/l/longitude-one/spatial-encoder)](https://github.com/longitude-one/spatial-encoder/blob/main/LICENSE)
[![CI](https://github.com/longitude-one/spatial-encoder/actions/workflows/ci.yml/badge.svg)](https://github.com/longitude-one/spatial-encoder/actions/workflows/ci.yml)
[![Coverage](https://codecov.io/gh/longitude-one/spatial-encoder/branch/main/graph/badge.svg)](https://codecov.io/gh/longitude-one/spatial-encoder/tree/main)
[![Monthly downloads](https://img.shields.io/packagist/dm/longitude-one/spatial-encoder)](https://packagist.org/packages/longitude-one/spatial-encoder)
[![Documentation](https://img.shields.io/badge/docs-v1-blue)](https://www.longitude-one.fr/spatial-encoder/v1/index.html)

Encode LongitudeOne spatial objects as text or binary data.
Choose a strategy for the target format; both mutable and immutable objects are accepted.

## Installation

```bash
composer require longitude-one/spatial-encoder:^1.0.0-RC.0
```

## Usage

```php
use LongitudeOne\SpatialEncoder\Encoder;
use LongitudeOne\SpatialEncoder\Strategy\WktTextStrategy;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\PointImmutable;

$encoder = new Encoder(new WktTextStrategy());

echo $encoder->encode(new PointImmutable(1, 2, 4326));
// POINT (1 2)
```

## Documentation

Read the [official documentation](https://www.longitude-one.fr/spatial-encoder/v1/index.html).

See the [strategy guide](docs/strategies.md) for format comparisons, examples and limitations.

## Output formats

| Format | Strategy |
| --- | --- |
| WKT | `WktTextStrategy` |
| EWKT | `EwktTextStrategy` |
| ISO WKB | `WkbBinaryStrategy` |
| PostGIS EWKB | `EwkbBinaryStrategy` |
| MySQL internal binary | `MySQLBinaryStrategy` |
| GeoJSON | `GeoJsonStrategy` |

Encoding does not establish geometric validity. [Validate before encoding](docs/validation.md)
when your application requires valid geometries. Coordinates are preserved without
reprojection or axis swapping; supported types and dimensions depend on the strategy.

To add a format, implement `StrategyInterface` and pass it to `Encoder`.

## Contributing

See [Contributing](CONTRIBUTING.md) for development setup, checks and contribution guidelines.
