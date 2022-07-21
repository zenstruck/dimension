<?php

namespace Zenstruck\Dimension\Converter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class LengthConverter extends UnitConverter
{
    protected static function build(): void
    {
        self::addSIUnit(Unit::nativeLinearFactory('m'), ['meter', 'meters', 'metre', 'metres']);
        self::add(Unit::linearFactory('ft', 0.3048), ['feet', 'foot', "'"]);
        self::add(Unit::linearFactory('in', 0.0254), ['inch', 'inches', '"']);
        self::add(Unit::linearFactory('mi', 1609.344), ['mile', 'miles']);
        self::add(Unit::linearFactory('yd', 0.9144), ['yard', 'yards']);
        self::add(Unit::linearFactory('M', 1852), ['nautical mile', 'nautical mile', 'nm', 'NM']);
        self::add(Unit::linearFactory('mil', 10000)); // Scandinavian mil
        self::add(Unit::linearFactory('AU', 149597870700), ['au', 'astronomical unit', 'astronomical units']); // Astronomical Unit
    }
}
