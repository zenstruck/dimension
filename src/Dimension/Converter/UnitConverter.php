<?php

namespace Zenstruck\Dimension\Converter;

use Zenstruck\Dimension;
use Zenstruck\Dimension\Converter;
use Zenstruck\Dimension\Exception\ComparisonNotPossible;
use Zenstruck\Dimension\Exception\ConversionNotPossible;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
abstract class UnitConverter implements Converter
{
    private const SI_PREFIXES = [
        [
            'abbr_prefix' => 'Y',
            'long_prefix' => 'yotta',
            'factor' => 1e24,
        ],
        [
            'abbr_prefix' => 'Z',
            'long_prefix' => 'zetta',
            'factor' => 1e21,
        ],
        [
            'abbr_prefix' => 'E',
            'long_prefix' => 'exa',
            'factor' => 1e18,
        ],
        [
            'abbr_prefix' => 'P',
            'long_prefix' => 'peta',
            'factor' => 1e15,
        ],
        [
            'abbr_prefix' => 'T',
            'long_prefix' => 'tera',
            'factor' => 1e12,
        ],
        [
            'abbr_prefix' => 'G',
            'long_prefix' => 'giga',
            'factor' => 1e9,
        ],
        [
            'abbr_prefix' => 'M',
            'long_prefix' => 'mega',
            'factor' => 1e6,
        ],
        [
            'abbr_prefix' => 'k',
            'long_prefix' => 'kilo',
            'factor' => 1e3,
        ],
        [
            'abbr_prefix' => 'h',
            'long_prefix' => 'hecto',
            'factor' => 1e2,
        ],
        [
            'abbr_prefix' => 'da',
            'long_prefix' => 'deca',
            'factor' => 1e1,
        ],
        [
            'abbr_prefix' => 'd',
            'long_prefix' => 'deci',
            'factor' => 1e-1,
        ],
        [
            'abbr_prefix' => 'c',
            'long_prefix' => 'centi',
            'factor' => 1e-2,
        ],
        [
            'abbr_prefix' => 'm',
            'long_prefix' => 'milli',
            'factor' => 1e-3,
        ],
        [
            'abbr_prefix' => 'µ',
            'long_prefix' => 'micro',
            'factor' => 1e-6,
        ],
        [
            'abbr_prefix' => 'n',
            'long_prefix' => 'nano',
            'factor' => 1e-9,
        ],
        [
            'abbr_prefix' => 'p',
            'long_prefix' => 'pico',
            'factor' => 1e-12,
        ],
        [
            'abbr_prefix' => 'f',
            'long_prefix' => 'femto',
            'factor' => 1e-15,
        ],
        [
            'abbr_prefix' => 'a',
            'long_prefix' => 'atto',
            'factor' => 1e-18,
        ],
        [
            'abbr_prefix' => 'z',
            'long_prefix' => 'zepto',
            'factor' => 1e-21,
        ],
        [
            'abbr_prefix' => 'y',
            'long_prefix' => 'yocto',
            'factor' => 1e-24,
        ],
    ];

    /** @var array<class-string,array<string,Unit>> */
    private static array $units = [];

    final public function convertTo(Dimension $from, string $to): Dimension
    {
        if ($from->unit() === $to) {
            return $from;
        }

        $fromUnit = self::get($from->unit());
        $toUnit = self::get($to);

        $nativeQuantity = $fromUnit->convertToNative($from->quantity());
        $newQuantity = $toUnit->convertFromNative($nativeQuantity);
        $class = $from::class;

        return new $class($newQuantity, $to);
    }

    final public function isEqualTo(Dimension $first, Dimension $second): bool
    {
        return $this->compare($first, $second, '=');
    }

    public function isLargerThan(Dimension $first, Dimension $second): bool
    {
        return $this->compare($first, $second, '>');
    }

    public function isLargerThanOrEqualTo(Dimension $first, Dimension $second): bool
    {
        return $this->compare($first, $second, '>=');
    }

    public function isSmallerThan(Dimension $first, Dimension $second): bool
    {
        return $this->compare($first, $second, '<');
    }

    public function isSmallerThanOrEqualTo(Dimension $first, Dimension $second): bool
    {
        return $this->compare($first, $second, '<=');
    }

    /**
     * @param string[] $aliases
     */
    final protected static function add(Unit $unit, array $aliases = []): void
    {
        self::$units[static::class][$unit->name()] = $unit;

        foreach ($aliases as $alias) {
            self::$units[static::class][$alias] = $unit;
        }
    }

    /**
     * @param string[] $aliases
     */
    final protected static function addSIUnit(Unit $unit, array $aliases = []): void
    {
        self::add($unit, $aliases);

        foreach (self::SI_PREFIXES as $prefix) {
            static::add(
                Unit::linearFactory($prefix['abbr_prefix'].$unit->name(), $prefix['factor']),
                \array_map(static fn($alias) => $prefix['long_prefix'].$alias, $aliases)
            );
        }
    }

    abstract protected static function build(): void;

    private function compare(Dimension $first, Dimension $second, string $operator): bool
    {
        try {
            $firstQty = self::get($first->unit())->convertToNative($first->quantity());
            $secondQty = self::get($second->unit())->convertToNative($second->quantity());
        } catch (ConversionNotPossible $e) {
            throw new ComparisonNotPossible(\sprintf('Not possible to compare "%s" with "%s".', $first, $second), previous: $e);
        }

        return match ($operator) {
            '>' => $firstQty > $secondQty,
            '>=' => $firstQty >= $secondQty,
            '<' => $firstQty < $secondQty,
            '<=' => $firstQty <= $secondQty,
            '=' => $firstQty === $secondQty,
            default => throw new \LogicException('Invalid operator.'),
        };
    }

    /**
     * @return Unit[]
     */
    private static function units(): array
    {
        if (isset(self::$units[static::class])) {
            return self::$units[static::class];
        }

        self::$units[static::class] = [];
        static::build();

        return self::units();
    }

    private static function get(string $name): Unit
    {
        if (!isset(self::units()[$name])) {
            throw new ConversionNotPossible(\sprintf('Unit "%s" not registered for "%s".', $name, static::class));
        }

        return self::units()[$name];
    }
}
