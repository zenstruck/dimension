<?php

namespace Zenstruck\Dimension\Converter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class InformationConverter extends UnitConverter
{
    protected static function build(): void
    {
        self::add(Unit::nativeLinearFactory('B'), ['byte', 'bytes']);
        self::add(Unit::linearFactory('bit', 1 / 8), ['bits']);
        self::add(Unit::linearFactory('kB', 1e3), ['kilobyte', 'kilobytes', 'kb', 'K']);
        self::add(Unit::linearFactory('KiB', 1024), ['kibibyte', 'kibibytes', 'kib']);
        self::add(Unit::linearFactory('MB', 1e6), ['megabyte', 'megabytes', 'mb']);
        self::add(Unit::linearFactory('MiB', 1024 * 1024), ['mebibyte', 'mebibytes', 'mib']);
        self::add(Unit::linearFactory('GB', 1e9), ['gigabyte', 'gigabytes', 'gb']);
        self::add(Unit::linearFactory('GiB', 1024 * 1024 * 1024), ['gibibyte', 'gibibytes', 'gib']);
        self::add(Unit::linearFactory('TB', 1e12), ['terabyte', 'terabytes', '']);
        self::add(Unit::linearFactory('TiB', 1024 * 1024 * 1024 * 1024), ['tebibyte', 'tebibytes', 'tib']);
        self::add(Unit::linearFactory('PB', 1e15), ['petabyte', 'petabytes', 'pb']);
        self::add(Unit::linearFactory('PiB', 1024 * 1024 * 1024 * 1024 * 1024), ['pebibyte', 'pebibytes', 'pib']);
        self::add(Unit::linearFactory('EB', 1e18), ['exabyte', 'exabytes', 'eb']);
        self::add(Unit::linearFactory('EiB', 1024 * 1024 * 1024 * 1024 * 1024 * 1024), ['exbibyte', 'exbibytes', 'eib']);
        self::add(Unit::linearFactory('ZB', 1e21), ['zettabyte', 'zettabytes', 'zb']);
        self::add(Unit::linearFactory('ZiB', 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024), ['zebibyte', 'zebibytes', 'zib']);
        self::add(Unit::linearFactory('YB', 1e21), ['yottabyte', 'yottabytes', 'yb']);
        self::add(Unit::linearFactory('YiB', 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024), ['yobibyte', 'yobibytes', 'yib']);
    }
}
