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
WKT, EWKT, WKB, and EWKB also support CircularString in all four dimensions.
WKT, EWKT, WKB, and EWKB also support CompoundCurve in all four dimensions.
Other curved geometry types and TIN remain unsupported.

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
- **WKB / EWKB:** the enum-only `byte_order` option defaults to
  `ByteOrderEnum::LITTLE_ENDIAN`; use `ByteOrderEnum::BIG_ENDIAN` for big-endian output. See [Strategy configuration](strategy-configuration.md#wkb-and-ewkb-byte-order).
  WKB uses ISO dimension offsets;
  EWKB uses dimension flags and writes a nonzero SRID only on the outer geometry.
- **MySQL:** output includes a four-byte SRID prefix and is not plain WKB.
  Supply X/longitude before Y/latitude. Only XY is accepted; empty values are
  limited to geometry collections, including nested members.
- **GeoJSON:** supply WGS 84 longitude/latitude coordinates, with height in metres
  for XYZ. Reference metadata is omitted without checking the supplied SRID.
  Measured coordinates, Triangle, and PolyhedralSurface are rejected.

### Circular strings in WKT and EWKT

Circular strings retain every ordered control point, including shared arc
endpoints, repeated closing coordinates, and collinear points. No curve is
linearized or approximated. Empty curves retain their dimension marker.

```php
use LongitudeOne\SpatialEncoder\Strategy\EwktTextStrategy;
use LongitudeOne\SpatialTypes\Types\Dimension3m\Geometry\CircularStringImmutable;

$curve = new CircularStringImmutable([[0, 0, 6], [1, 1, 7], [2, 0, 8]], 4326);

echo new EwktTextStrategy()->encode($curve);
// SRID=4326;CIRCULARSTRING M (0 0 6, 1 1 7, 2 0 8)
```

`WktTextStrategy` produces the same text without the `SRID=4326;` prefix.
For an empty XYM curve, it produces `CIRCULARSTRING M EMPTY`.
These strategies follow the circular-string text productions in
ISO/IEC CD 13249-3:201x(E), clause 5.1.57 (Committee Draft, 2009-01-16).
EWKT adds the existing nonzero-SRID prefix as a format extension.

### Compound curves in WKT and EWKT

Compound curves retain component order and boundaries. Linear components use
parenthesized coordinates without a `LINESTRING` keyword; circular components
retain their `CIRCULARSTRING` keyword and dimension marker. Components are
never flattened or linearized, and open endpoints and closing coordinates are preserved.

```php
use LongitudeOne\SpatialEncoder\Strategy\EwktTextStrategy;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\CircularStringImmutable;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\CompoundCurveImmutable;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\LineStringImmutable;

$curve = new CompoundCurveImmutable([
    new LineStringImmutable([[0, 0], [2, 0]], 4326),
    new CircularStringImmutable([[2, 0], [3, 1], [4, 0]], 4326),
], 4326);

echo new EwktTextStrategy()->encode($curve);
// SRID=4326;COMPOUNDCURVE ((0 0, 2 0), CIRCULARSTRING (2 0, 3 1, 4 0))
```

WKT omits the SRID prefix. EMPTY retains its dimension marker, for example
`COMPOUNDCURVE ZM EMPTY`. The syntax follows the compound-curve and single-curve
text productions in ISO/IEC CD 13249-3:201x(E), clause 5.1.57
(Committee Draft, 2009-01-16). EWKT adds the existing nonzero-SRID prefix.
CompoundCurve remains unsupported by the MySQL binary and GeoJSON strategies.

### Circular strings in WKB and EWKB

Both binary strategies write the number of control points followed by every
ordered coordinate, without interpolating the curve. Closed, multi-arc, and
collinear circular strings retain their complete control-point sequences.

| Dimensions | WKB type | EWKB type before the optional SRID flag |
| --- | ---: | --- |
| XY | 8 | `0x00000008` |
| XYZ | 1008 | `0x80000008` |
| XYM | 2008 | `0x40000008` |
| XYZM | 3008 | `0xC0000008` |

WKB type identifiers and coordinate layouts follow ISO/IEC CD
13249-3:201x(E), clause 5.1.58 and Table 15 (Committee Draft, 2009-01-16).
EWKB follows the [PostGIS extension](https://github.com/postgis/postgis/blob/master/liblwgeom/lwout_wkb.c):
it adds the existing dimension flags and, for a nonzero outer SRID, the
`0x20000000` flag followed by the SRID. Collection members omit that SRID field.
The configured byte order applies to the header, point count, and ordinates.

An EMPTY circular string retains its dimension and writes an explicit unsigned
32-bit zero point count. This is the approved LongitudeOne compatibility
decision for both formats, consistent with existing binary point sequences.
It differs from the header-only EMPTY production in the available ISO draft,
clause 5.1.58, description r)–u). For example, an EMPTY XY curve with SRID zero
produces these complete hexadecimal representations:

| Byte order | WKB and EWKB |
| --- | --- |
| Little-endian (default) | `010800000000000000` |
| Big-endian | `000000000800000000` |

The executable [WKB](../tests/Unit/Strategy/Wkb/CircularStringTest.php) and
[EWKB](../tests/Unit/Strategy/Ewkb/CircularStringTest.php) fixtures cover these
zero counts, all four dimensions, both byte orders, and SRID handling.

### Compound curves in WKB and EWKB

Both strategies write a component count followed by each complete LineString
or CircularString binary geometry, including its own byte-order and type header.
Component order, boundaries, shared endpoints, open endpoints, and closing
coordinates are preserved without flattening or linearization.

| Dimensions | WKB type | EWKB type before the optional SRID flag |
| --- | ---: | --- |
| XY | 9 | `0x00000009` |
| XYZ | 1009 | `0x80000009` |
| XYM | 2009 | `0x40000009` |
| XYZM | 3009 | `0xC0000009` |

WKB follows the compound-curve productions and Table 15 in ISO/IEC CD
13249-3:201x(E), clause 5.1.58 (Committee Draft, 2009-01-16).
EWKB uses the [PostGIS extension](https://github.com/postgis/postgis/blob/master/liblwgeom/lwout_wkb.c).
The configured byte order applies to the parent and every component.
WKB omits all SRIDs; EWKB writes a nonzero SRID only on the outermost geometry,
including when a compound curve is nested in a geometry collection.

An EMPTY compound curve retains its dimensions and writes a uint32 zero
component count. This [approved LongitudeOne compatibility decision](https://github.com/longitude-one/spatial-encoder/issues/35#issuecomment-6976091064)
follows PostGIS behavior for both formats. It differs from the header-only
EMPTY production in the available ISO draft, clause 5.1.58, description v)–y).
Complete EMPTY XY encodings are:

| Format / SRID | Little-endian (default) | Big-endian |
| --- | --- | --- |
| WKB, SRID omitted | `010900000000000000` | `000000000900000000` |
| EWKB, SRID 0 | `010900000000000000` | `000000000900000000` |
| EWKB, SRID 4326 | `0109000020e610000000000000` | `0020000009000010e600000000` |

The executable [WKB](../tests/Unit/Strategy/Wkb/CompoundCurveTest.php) and
[EWKB](../tests/Unit/Strategy/Ewkb/CompoundCurveTest.php) fixtures cover these
outputs, all four dimensions, both byte orders, linear-only, circular-only,
and mixed components, open/closed curves, and nested collections.

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

See [Strategy configuration](strategy-configuration.md) for validated options and custom strategies.
