<?php

/*
 * This file is part of the zenstruck/dimension package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Dimension\Converter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class DurationConverter extends UnitConverter
{
    protected static function build(): void
    {
        self::addSIUnit(Unit::nativeLinearFactory('s'), ['sec', 'secs', 'second', 'seconds']);
        self::add(Unit::linearFactory('m', 60), ['min', 'mins', 'minute', 'minutes']);
        self::add(Unit::linearFactory('h', 3600), ['hr', 'hrs', 'hour', 'hours']);
        self::add(Unit::linearFactory('d', 86400), ['day', 'days']);
        self::add(Unit::linearFactory('w', 604800), ['wk', 'wks', 'week', 'weeks']);
        self::add(Unit::linearFactory('y', 31556952), ['yr', 'yrs', 'year', 'years']); // Gregorian year, understood as 365.2425 days
        self::add(Unit::linearFactory('jyr', 31557600), ['julian year', 'julian years']); // Julian year, understood as 365.25 days
    }
}
