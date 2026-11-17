# Strategy configuration

Options are resolved when the strategy is constructed. Invalid types, unsupported values and unknown options fail before execution with Symfony OptionsResolver exceptions.
The resolver is local to construction; only the resolved options are retained.

## Built-in behavior

All built-in strategies accept an optional options array. Their default output is unchanged.
The following built-in options are available:

| Strategies | Option | Default | Accepted type | Effect |
| --- | --- | --- | --- | --- |
| GeoJSON | `pretty_print` | `false` | `bool` | Add JSON indentation without changing coordinates or reference handling |
| WKB, EWKB | `byte_order` | `ByteOrderEnum::LITTLE_ENDIAN` | `LongitudeOne\Core\Enum\ByteOrderEnum` | Select byte order; big-endian is temporarily unavailable |

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

$wkb = new WkbBinaryStrategy([
    'byte_order' => ByteOrderEnum::LITTLE_ENDIAN,
]);
$ewkb = new EwkbBinaryStrategy([
    'byte_order' => ByteOrderEnum::LITTLE_ENDIAN,
]);
```

`ByteOrderEnum::BIG_ENDIAN` is recognized but temporarily rejected during
construction by both strategies with `\LogicException` and the exact message
`Big-endian encoding is not implemented yet.` No geometry is encoded.
Raw integers (including `0` and `1`), strings, booleans, null and other values
that are not `ByteOrderEnum` instances instead raise
`Symfony\Component\OptionsResolver\Exception\InvalidOptionsException`.
MySQL and other strategies do not accept `byte_order`.

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
