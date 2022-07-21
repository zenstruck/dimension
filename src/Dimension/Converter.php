<?php

namespace Zenstruck\Dimension;

use Zenstruck\Dimension;
use Zenstruck\Dimension\Exception\ComparisonNotPossible;
use Zenstruck\Dimension\Exception\ConversionNotPossible;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
interface Converter
{
    /**
     * @template T of Dimension
     *
     * @param T $from
     *
     * @return T
     *
     * @throws ConversionNotPossible
     */
    public function convertTo(Dimension $from, string $to): Dimension;

    /**
     * @throws ComparisonNotPossible
     */
    public function isEqualTo(Dimension $first, Dimension $second): bool;

    /**
     * @throws ComparisonNotPossible
     */
    public function isLargerThan(Dimension $first, Dimension $second): bool;

    /**
     * @throws ComparisonNotPossible
     */
    public function isLargerThanOrEqualTo(Dimension $first, Dimension $second): bool;

    /**
     * @throws ComparisonNotPossible
     */
    public function isSmallerThan(Dimension $first, Dimension $second): bool;

    /**
     * @throws ComparisonNotPossible
     */
    public function isSmallerThanOrEqualTo(Dimension $first, Dimension $second): bool;
}
