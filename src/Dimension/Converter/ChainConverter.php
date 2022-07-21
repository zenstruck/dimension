<?php

namespace Zenstruck\Dimension\Converter;

use Zenstruck\Dimension;
use Zenstruck\Dimension\Converter;
use Zenstruck\Dimension\Exception\ConversionNotPossible;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class ChainConverter implements Converter
{
    /**
     * @param Converter[] $converters
     */
    public function __construct(private iterable $converters)
    {
    }

    public static function default(): self
    {
        return new self([
            new LengthConverter(),
            new MassConverter(),
            new TemperatureConverter(),
            new InformationConverter(),
            new DurationConverter(),
        ]);
    }

    public function convertTo(Dimension $from, string $to): Dimension
    {
        foreach ($this->converters as $converter) {
            try {
                return $converter->convertTo($from, $to);
            } catch (ConversionNotPossible) {
                continue;
            }
        }

        throw new ConversionNotPossible(\sprintf('No converter registered to convert "%s" to "%s".', $from->unit(), $to));
    }
}
