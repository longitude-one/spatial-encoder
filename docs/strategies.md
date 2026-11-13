# Serialization strategies

Choose a strategy for the format expected by your consumer.
The encoder accepts mutable and immutable spatial objects without modifying them.

## Choose a format

| Strategy | Output | Dimensions | SRID included |
| --- | --- | --- | --- |
| `WktTextStrategy` | Readable WKT text | XY, XYZ, XYM, XYZM | No |
| `EwktTextStrategy` | WKT with a spatial reference | XY, XYZ, XYM, XYZM | When nonzero |
| `WkbBinaryStrategy` | ISO WKB bytes | XY, XYZ, XYM, XYZM | No |
| `EwkbBinaryStrategy` | PostGIS EWKB bytes | XY, XYZ, XYM, XYZM | When nonzero |
| `MySQLBinaryStrategy` | MySQL internal bytes | XY | Always, as a prefix |
| `GeoJsonStrategy` | GeoJSON geometry text | XY, XYZ | No |

All strategies support Point, LineString, Polygon, their multi-geometries,
and geometry collections. WKT, EWKT, WKB, and EWKB also support Triangle
and PolyhedralSurface; polyhedral surfaces are available in XYZ and XYZM.
Curved geometries and TIN are unsupported.

## Encode an object

```php
require 'vendor/autoload.php';

use LongitudeOne\SpatialEncoder\Encoder;
use LongitudeOne\SpatialEncoder\Strategy\WktTextStrategy;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\PointImmutable;

$point = new PointImmutable(1, 2, 4326);
$encoder = new Encoder(new WktTextStrategy());

echo $encoder->encode($point);
// POINT (1 2)
```

Replace the strategy to change the output format. Strategies can also be
called directly with `$strategy->encode($point)`.
Binary strategies return raw bytes; use `bin2hex()` only to display them.

## Before encoding

Validate your geometry first: encoding is not a general geometry validator.
See [Validate Before Encoding](validation.md).

Coordinates are preserved without reprojection, axis swapping, or unit conversion.
An SRID does not transform coordinates.

## Format limits

- **WKT / EWKT:** EWKT adds one outer `SRID=...;` prefix when the SRID is nonzero.
- **WKB / EWKB:** output is little-endian. WKB uses ISO dimension offsets;
  EWKB uses dimension flags and writes a nonzero SRID only on the outer geometry.
- **MySQL:** output includes a four-byte SRID prefix and is not plain WKB.
  Supply X/longitude before Y/latitude. Only XY is accepted; empty values are
  limited to geometry collections, including nested members.
- **GeoJSON:** supply WGS 84 longitude/latitude coordinates, with height in metres
  for XYZ. Reference metadata is omitted without checking the supplied SRID.
  Measured coordinates, Triangle, and PolyhedralSurface are rejected.

### GeoJSON structures

Empty geometries and collections are supported, but multi-geometries cannot
contain empty members. Non-empty lines require at least two positions.
Polygon exterior rings must have positive signed XY area and holes negative
signed XY area; zero-area rings are rejected. Rings are not repaired.
These checks do not establish topological validity.

Collections retain their order and nesting. Antimeridian crossings are preserved;
prepare any cutting or simplification required by your consumer before encoding.
The strategy returns geometry objects; compose Features in your application.

Unsupported dimensions, types, and incompatible structures raise the corresponding
`UnsupportedDimensionException`, `UnsupportedSpatialTypeException`, or
`UnsupportedGeometryStructureException`. GeoJSON encoding failures raise
`JsonEncodingException`, preserving the original exception. No partial JSON is returned.

## Examples and extension

See the executable [WKT](../tests/Unit/Strategy/Wkt/Examples/),
[WKB](../tests/Unit/Strategy/Wkb/Examples/),
[EWKB](../tests/Unit/Strategy/Ewkb/Examples/), and
[GeoJSON](../tests/Unit/Strategy/GeoJson/) examples.

To add a format, implement [StrategyInterface](../lib/Strategy/StrategyInterface.php)
and pass it to `Encoder`. See [Encoding tests](testing.md) for fixture guidance
and focused test commands.
