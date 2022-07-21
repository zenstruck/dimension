<?php

namespace Zenstruck\Dimension\Converter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class MassConverter extends UnitConverter
{
    protected static function build(): void
    {
        self::addSIUnit(Unit::nativeLinearFactory('g'), ['gram', 'grams']);
        self::add(Unit::linearFactory('t', 1e6), ['ton', 'tons', 'tonne', 'tonnes']);
        self::add(Unit::linearFactory('lb', 453.59237), ['lbs', 'pound', 'pounds']);
        self::add(Unit::linearFactory('oz', 453.59237 / 16), ['ounce', 'ounces']);
        self::add(Unit::linearFactory('st', 453.59237 * 14), ['stone', 'stones']);
    }
}
