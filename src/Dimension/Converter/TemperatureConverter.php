<?php

namespace Zenstruck\Dimension\Converter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class TemperatureConverter extends UnitConverter
{
    protected static function build(): void
    {
        self::addSIUnit(Unit::nativeLinearFactory('K'), ['°K', 'kelvin']);
        self::add(
            new Unit('C', static fn($x) => $x - 273.15, static fn($x) => $x + 273.15),
            ['°C', 'celsius']
        );
        self::add(
            new Unit('F', static fn($x) => ($x * 9 / 5) - 459.67, static fn($x) => ($x + 459.67) * 5 / 9),
            ['°F', 'fahrenheit']
        );
    }
}
