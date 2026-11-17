# Strategy configuration

Options are resolved when the strategy is constructed. Invalid types, unsupported values and unknown options fail before execution with Symfony OptionsResolver exceptions.
The resolver is local to construction; only the resolved options are retained.

## Built-in behavior

All built-in strategies accept an optional options array. Their default output is unchanged.
The following built-in options are available:

| Strategies | Option | Default | Accepted type | Effect |
| --- | --- | --- | --- | --- |
| GeoJSON | `pretty_print` | `false` | `bool` | Add JSON indentation without changing coordinates or reference handling |
| WKB, EWKB | `byte_order` | `ByteOrderEnum::LITTLE_ENDIAN` | `LongitudeOne\Core\Enum\ByteOrderEnum` | Select little-endian or big-endian output |

```php
use LongitudeOne\SpatialEncoder\Encoder;
use LongitudeOne\SpatialEncoder\Strategy\GeoJsonStrategy;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\PointImmutable;

$encoder = new Encoder(new GeoJsonStrategy(['pretty_print' => true]));
$json = $encoder->encode(new PointImmutable(1, 2, 4326));
```

Other formats reject `pretty_print`. No precision reduction, dimension conversion or SRID override is introduced.

### WKB and EWKB byte order

Both binary strategies accept only the core enum for `byte_order`. Omitted and
explicit `LITTLE_ENDIAN` configurations produce exactly the same existing bytes.
WKB keeps ISO dimension offsets and omits SRIDs; EWKB keeps dimension flags and
includes a nonzero SRID only on the outer geometry.

```php
use LongitudeOne\Core\Enum\ByteOrderEnum;
use LongitudeOne\SpatialEncoder\Strategy\EwkbBinaryStrategy;
use LongitudeOne\SpatialEncoder\Strategy\WkbBinaryStrategy;
use LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\PointImmutable;

$point = new PointImmutable(1, 2, 4326);
$wkb = new WkbBinaryStrategy([
    'byte_order' => ByteOrderEnum::LITTLE_ENDIAN,
]);
$ewkb = new EwkbBinaryStrategy([
    'byte_order' => ByteOrderEnum::LITTLE_ENDIAN,
]);
$bigWkb = new WkbBinaryStrategy([
    'byte_order' => ByteOrderEnum::BIG_ENDIAN,
]);
$bigEwkb = new EwkbBinaryStrategy([
    'byte_order' => ByteOrderEnum::BIG_ENDIAN,
]);

echo bin2hex($wkb->encode($point));
// 0101000000000000000000f03f0000000000000040
echo bin2hex($ewkb->encode($point));
// 0101000020e6100000000000000000f03f0000000000000040
echo bin2hex($bigWkb->encode($point));
// 00000000013ff00000000000004000000000000000
echo bin2hex($bigEwkb->encode($point));
// 0020000001000010e63ff00000000000004000000000000000
```

`ByteOrderEnum::BIG_ENDIAN` writes marker `00` and big-endian 32-bit integers
and 64-bit doubles; `LITTLE_ENDIAN` writes marker `01` and little-endian numbers.
The selected order applies to every nested geometry, count, coordinate and
serialized EWKB SRID. EMPTY points retain their endian-correct quiet NaN ordinates.
Coordinates are preserved in X/Y/Z/M order without transformation or mutation.
Raw integers (including `0` and `1`), strings, booleans, null and other values
that are not `ByteOrderEnum` instances raise
`Symfony\Component\OptionsResolver\Exception\InvalidOptionsException`.
MySQL and other strategies do not accept `byte_order`.

The numeric ordering follows ISO/IEC CD 13249-3:201x(E), Committee Draft dated
2009-01-16, §5.1.58 cj–cp (printed page 179). EWKB keeps its extension-specific
[dimension flags and SRID layout](https://github.com/postgis/postgis/blob/master/doc/ZMSgeoms.txt);
PostGIS documents both NDR and XDR output in [ST_AsEWKB](https://postgis.net/docs/ST_AsEWKB.html).

## Optional base class

Implement `Strategy\ConfigurableStrategyInterface::configureOptions(OptionsResolver $resolver): void` to declare options.
Extend `Strategy\AbstractConfigurableStrategy` for an optional constructor accepting `array $options = []` and a protected readonly `$options` array.
The abstract class does not implement the execution interface: each strategy still implements its existing execution contract.
Use `setDefaults`, `setAllowedTypes`, `setAllowedValues` and `setNormalizer` to declare strategy-owned configuration.
Allowed values are checked before normalization.

## External strategy without inheritance

The orchestrator does not require the configuration interface or base class. This complete example owns its configuration lifecycle:

```php
use LongitudeOne\SpatialEncoder\Strategy\ConfigurableStrategyInterface;
use LongitudeOne\SpatialEncoder\Strategy\StrategyInterface;
use LongitudeOne\SpatialTypes\Interfaces\SpatialInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


final class ExternalStrategy implements ConfigurableStrategyInterface, StrategyInterface
{
    /** @var array<string, mixed> Resolved options. */
    private readonly array $options;

    /** @param array<string, mixed> $options application format options */
    public function __construct(array $options = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
    }

    /** @param OptionsResolver $resolver local configuration resolver */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('include_srid', false);
        $resolver->setAllowedTypes('include_srid', 'bool');
    }

    /**
     * Encode an application-specific array representation.
     *
     * @param SpatialInterface $spatial source geometry
     *
     * @return array<string, mixed>
     */
    public function encode(SpatialInterface $spatial): array
    {
        $result = ['coordinates' => $spatial->toArray()];
        if ($this->options['include_srid']) {
            $result['srid'] = $spatial->getSrid();
        }

        return $result;
    }
}
```

```php
$encoder = new \LongitudeOne\SpatialEncoder\Encoder(new ExternalStrategy(['include_srid' => true]));
$result = $encoder->encode(new \LongitudeOne\SpatialTypes\Types\Dimension2\Geometry\PointImmutable(1, 2, 4326));
// ['coordinates' => [1, 2], 'srid' => 4326]
```

## Symfony compatibility

The dependency supports Symfony OptionsResolver 5.4, 6, 7 and 8. Configuration uses the shared APIs available since 5.4.
No Symfony dependency or configuration abstraction is added to spatial-core.
