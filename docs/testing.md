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
