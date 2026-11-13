# Validate Before Encoding

Validate the geometry before passing it to `Encoder` or a strategy. The encoder
serializes the supplied object; it does not establish geometric validity or
repair incomplete coordinates, open rings, or other invalid structures.
This applies to both mutable and immutable spatial types.

Invalid input may cause an uncaught PHP error or warning, produce invalid output,
or undergo PHP's native numeric conversions. Successful encoding does not prove
that the geometry or its serialized representation is valid.

## A Point With a Missing Ordinate

`spatial-types` `1.0.0-RC.1` accepts this mutable point:

```php
require 'vendor/autoload.php';

use LongitudeOne\SpatialTypes\Types\Dimension2\Point;

$point = new Point(1, null);
// $point->toArray() is [1, null], not EMPTY. The default SRID is zero.
```

The immutable equivalent,
`new LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\PointImmutable(1, null)`,
has the same encoding outcomes. The following observations were checked with
PHP 8.4; binary outputs are shown as `bin2hex()` strings.

| Strategy | Observed result |
| --- | --- |
| `WktTextStrategy`, `EwktTextStrategy` | An uncaught `TypeError`: `WKT requires numeric ordinates; null given.` No text is returned. |
| `WkbBinaryStrategy`, `EwkbBinaryStrategy` | `0101000000000000000000f03f0000000000000000` — Missing Y becomes zero; see below. |
| `MySQLBinaryStrategy` | `000000000101000000000000000000f03f0000000000000000` — the same conversion, preceded by the zero SRID. |
| `GeoJsonStrategy` | `{"type":"Point","coordinates":[1,null]}` — valid JSON, but an invalid GeoJSON position. |

PHP's `pack()` converts the missing Y to zero. The WKB/EWKB bytes describe `(1, 0)`,
not the supplied incomplete coordinates.

The binary and GeoJSON cases return without an exception or warning on the
checked PHP runtime. The source point remains `[1, null]`: the binary conversion
does not repair it. These outcomes illustrate invalid input handling, rather
than a supported representation for missing ordinates.

WKT numeric formatting cannot consume a null ordinate. The strategy lets its
`TypeError` propagate; it does not translate it into an encoder exception.
Other malformed objects may encounter different failures. In particular, an
EMPTY ring can trigger an undefined-array-key warning during GeoJSON orientation
calculation. The caller must validate ring structure before encoding.

WKT defines ordinates as numbers (ISO/IEC CD 13249-3:201x(E), clause 5.1.57).
GeoJSON also requires numeric positions and closed linear rings with at least
four positions; see [RFC 7946 sections 3.1.1 and 3.1.6](https://www.rfc-editor.org/rfc/rfc7946.html#section-3.1.1).

## Check With Spatial Validator First

Use [`longitude-one/spatial-validator`](https://github.com/longitude-one/spatial-validator)
with Symfony Validator to reject invalid geometry before encoding. Install it
in the consuming application; it is an optional integration, not an encoder
runtime dependency:

```bash
composer require longitude-one/spatial-validator
```

**Compatibility:** the validator must support the installed `spatial-types`
version. The [validator implementation currently checked](https://github.com/longitude-one/spatial-validator/blob/main/lib/Validator/Constraints/CoordinateLayoutValidator.php)
still calls `isEmpty()`, which `spatial-types` `1.0.0-RC.1` no longer exposes.
It detects the partial point below, but validating a complete point currently
raises `Error: Call to undefined method ...::isEmpty()`. A compatible validator
update is required before using this combination as a general validation pipeline.
Do not ignore that error or proceed to encoding after validation fails.

This complete example uses the public `Spatial` constraint, which dispatches to
the geometry's validator. It stops before creating the encoder for `(1, null)`:

```php
require 'vendor/autoload.php';

use LongitudeOne\SpatialEncoder\Encoder;
use LongitudeOne\SpatialEncoder\Strategy\WktTextStrategy;
use LongitudeOne\SpatialTypes\Types\Dimension2\Point;
use LongitudeOne\SpatialValidator\Validator\Constraints\Spatial;
use Symfony\Component\Validator\Validation;

$point = new Point(1, null);
$violations = Validation::createValidator()->validate($point, new Spatial());

if (0 !== count($violations)) {
    throw new InvalidArgumentException((string) $violations);
}

echo (new Encoder(new WktTextStrategy()))->encode($point);
```

For this point, validation reports
`The declared coordinate layout and EMPTY state must be consistent.`
The application throws its own `InvalidArgumentException` and never calls the
encoder. The same check rejects the immutable equivalent.

After editing a mutable geometry, validate it again before encoding. Geometric
validity also does not remove a strategy's format restrictions: for example,
a valid measured point still cannot be encoded as GeoJSON. See the
[strategy guide](strategies.md) for supported types, dimensions, and format errors.
