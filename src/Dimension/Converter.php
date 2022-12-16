<?php

/*
 * This file is part of the zenstruck/dimension package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Dimension;

use Zenstruck\Dimension;
use Zenstruck\Dimension\Exception\ComparisonNotPossible;
use Zenstruck\Dimension\Exception\ConversionNotPossible;
use Zenstruck\Dimension\Exception\OperationNotPossible;

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
     * @template T of Dimension
     *
     * @param T $first
     *
     * @return T
     *
     * @throws OperationNotPossible
     */
    public function sum(Dimension $first, Dimension $second): Dimension;

    /**
     * @template T of Dimension
     *
     * @param T $first
     *
     * @return T
     *
     * @throws OperationNotPossible
     */
    public function subtract(Dimension $first, Dimension $second): Dimension;

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
