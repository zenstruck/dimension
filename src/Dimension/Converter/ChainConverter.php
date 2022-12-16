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

use Zenstruck\Dimension;
use Zenstruck\Dimension\Converter;
use Zenstruck\Dimension\Exception\ComparisonNotPossible;
use Zenstruck\Dimension\Exception\ConversionNotPossible;
use Zenstruck\Dimension\Exception\OperationNotPossible;

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

    public function sum(Dimension $first, Dimension $second): Dimension
    {
        foreach ($this->converters as $converter) {
            try {
                return $converter->sum($first, $second);
            } catch (OperationNotPossible) {
                continue;
            }
        }

        throw new OperationNotPossible(\sprintf('No converter registered to add "%s" to "%s".', $second, $first));
    }

    public function subtract(Dimension $first, Dimension $second): Dimension
    {
        foreach ($this->converters as $converter) {
            try {
                return $converter->subtract($first, $second);
            } catch (OperationNotPossible) {
                continue;
            }
        }

        throw new OperationNotPossible(\sprintf('No converter registered to subtract "%s" from "%s".', $second, $first));
    }

    public function isEqualTo(Dimension $first, Dimension $second): bool
    {
        return $this->compare($first, $second, __FUNCTION__);
    }

    public function isLargerThan(Dimension $first, Dimension $second): bool
    {
        return $this->compare($first, $second, __FUNCTION__);
    }

    public function isLargerThanOrEqualTo(Dimension $first, Dimension $second): bool
    {
        return $this->compare($first, $second, __FUNCTION__);
    }

    public function isSmallerThan(Dimension $first, Dimension $second): bool
    {
        return $this->compare($first, $second, __FUNCTION__);
    }

    public function isSmallerThanOrEqualTo(Dimension $first, Dimension $second): bool
    {
        return $this->compare($first, $second, __FUNCTION__);
    }

    private function compare(Dimension $first, Dimension $second, string $method): bool
    {
        foreach ($this->converters as $converter) {
            try {
                return $converter->{$method}($first, $second);
            } catch (ComparisonNotPossible) {
                continue;
            }
        }

        throw new ComparisonNotPossible(\sprintf('No converter registered to compare "%s" with "%s".', $first, $second));
    }
}
