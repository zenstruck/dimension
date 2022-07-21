<?php

namespace Zenstruck\Dimension;

use Zenstruck\Dimension;
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
}
