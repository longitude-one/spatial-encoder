# Encoding tests

This guide explains how to add encoding fixtures.
See [Tests](../tests/README.md) for setup and general commands.

## Writing fixtures

- Construct inputs explicitly with literal coordinates.
- Write complete expected strings, hexadecimal bytes, or JSON structures.
- Do not generate expected output with the encoder under test.
- For binary fixtures, add a readable WKT comment.
- Record the reference tool, version, and options when checking external output.

External tools help verify interoperability; their output alone does not
establish standards compliance. Databases are not required to run the tests.

## Choosing a test location

Paths below are relative to `tests/Unit/Strategy/`.

| Location | Purpose |
| --- | --- |
| `Wkt/Examples/` | Explicit WKT examples |
| `Wkb/Examples/` | Explicit ISO WKB examples |
| `Ewkb/Examples/` | Explicit EWKB examples |
| `Wkb/ByteOrder/`, `Ewkb/ByteOrder/` | Literal big-endian and explicit little-endian fixtures |
| `WkbByteOrderTest.php`, `EwkbByteOrderTest.php` | Configuration, nested collections, SRID and fractional mutable coordinates |
| `MySQL/` | Coordinate order and rejected inputs |
| `GeoJson/` | Geometry output and exception contracts |
| `MutableEncodingTest.php` | Encoding before and after mutation |
| `UnvalidatedPointTest.php` | Observed outcomes for incomplete coordinates |

## Cases to consider

Cover applicable geometry types, coordinate layouts, empty values,
nested collections, spatial references, and rejected inputs.

Check mutable and immutable inputs where relevant.
Use ordinary constructors rather than bypassing model constraints.

For binary formats, compare the complete hexadecimal output.
For JSON, compare the complete structure without requiring property order
or whitespace.

## WKB and EWKB byte-order coverage inventory

Both strategies currently support the following inventory. Each applicable
layout has explicit non-empty and EMPTY hexadecimal fixtures for both byte
orders under `Wkb/ByteOrder/` and `Ewkb/ByteOrder/`. Existing `Examples/` tests
retain the default little-endian regression fixtures.

| Geometry type | XY | XYZ | XYM | XYZM | EMPTY payload |
| --- | --- | --- | --- | --- | --- |
| Point | Yes | Yes | Yes | Yes | Quiet NaN for each ordinate |
| LineString | Yes | Yes | Yes | Yes | Zero point count |
| Polygon | Yes | Yes | Yes | Yes | Zero ring count |
| MultiPoint | Yes | Yes | Yes | Yes | Zero member count |
| MultiLineString | Yes | Yes | Yes | Yes | Zero member count |
| MultiPolygon | Yes | Yes | Yes | Yes | Zero member count |
| GeometryCollection | Yes | Yes | Yes | Yes | Zero member count |
| Triangle | Yes | Yes | Yes | Yes | Zero ring count |
| PolyhedralSurface | No | Yes | No | Yes | Zero patch count |

The stored big-endian fixtures reverse individual 32-bit integer and 64-bit
double fields of the existing literal reference fixtures, following ISO/IEC CD
13249-3:201x(E), §5.1.58 cp. Expected outputs are stored directly in each test;
no expected output is computed at runtime by a conversion helper or encoder.
Fixtures also preserve EWKB flags and root-only SRIDs.
Additional configuration tests cover nested collections, zero/nonzero SRIDs,
negative fractional XYZM coordinates and unchanged mutable input state.

## Running focused tests

Run these commands from the repository root:

```bash
composer test -- tests/Unit/Strategy/Wkb
composer test -- tests/Unit/Strategy/Ewkb
composer test -- --filter CoordinateOrderTest
composer test -- --filter GeoJson
composer test -- --filter MutableEncodingTest
```

Tests documenting invalid inputs describe observed behavior; they do not
make those inputs supported. See [Validate Before Encoding](validation.md).
