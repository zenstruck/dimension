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
 * @author Jonathan Hanson <jonathan@jonathan-hanson.org>
 * @source https://github.com/PhpUnitsOfMeasure/php-units-of-measure
 *
 * @internal
 */
final class Unit
{
    /**
     * @param callable(float):float $fromNative
     * @param callable(float):float $toNative
     */
    public function __construct(private string $name, private $fromNative, private $toNative)
    {
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * For the special case of units that have a linear conversion factor, this factory
     * method simplifies the construction of the unit of measure.
     *
     * For example the relationship between meters and feet is a simple multiplicative factor of
     * 0.3048 meters in a foot. Converting back and forth between these two units is a matter of
     * multiplication or division by this scaling factor.
     *
     * In contrast, converting Celsius to Fahrenheit involves an offset calculation, and cannot
     * be represented by a simple conversion factor. In such cases this class's constructor should be
     * invoked directly.
     *
     * To help in getting the multiplication and division right, think of the toNativeUnitFactor
     * as the number you'd multiply this unit by to get to the native unit of measure. In
     * other words:
     * 'Value in the native unit of measure' = 'Value in this unit of measure' * toNativeUnitFactor
     *
     * @param string $name               This unit of measure's canonical name
     * @param float  $toNativeUnitFactor The factor to scale the unit by where factor * base unit = this unit
     */
    public static function linearFactory(string $name, float $toNativeUnitFactor): self
    {
        return new self(
            $name,
            fn($valueInNativeUnit) => $valueInNativeUnit / $toNativeUnitFactor,
            fn($valueInThisUnit) => $valueInThisUnit * $toNativeUnitFactor
        );
    }

    /**
     * This is a special case of the linear unit factory above, for use in generating the native
     * unit of measure for a given physical quantity. By definition, the conversion factor is 1.
     *
     * @param string $name This unit of measure's canonical name
     */
    public static function nativeLinearFactory(string $name): self
    {
        return self::linearFactory($name, 1);
    }

    public function convertToNative(float $quantity): float
    {
        return ($this->toNative)($quantity);
    }

    public function convertFromNative(float $quantity): float
    {
        return ($this->fromNative)($quantity);
    }
}
