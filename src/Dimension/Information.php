<?php

namespace Zenstruck\Dimension;

use Zenstruck\Dimension;
use Zenstruck\Dimension\Converter\InformationConverter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @method self kb() Convert to kB
 * @method self mb() Convert to MB
 * @method self gb() Convert to GB
 *
 * @immutable
 */
final class Information extends Dimension
{
    private const DECIMAL = 1000;
    private const BINARY = 1024;
    private const DECIMAL_UNITS = ['b' => 'B', 'kb' => 'kB', 'mb' => 'MB', 'gb' => 'GB', 'tb' => 'TB', 'pb' => 'PB', 'eb' => 'EB', 'zb' => 'ZB', 'yb' => 'YB'];
    private const BINARY_UNITS = ['b' => 'B', 'kib' => 'KiB', 'mib' => 'MiB', 'gib' => 'GiB', 'tib' => 'TiB', 'pib' => 'PiB', 'eib' => 'EiB', 'zib' => 'ZiB', 'yib' => 'YiB'];
    private const ALTERNATE_MAP = [
        'k' => 'kB',
        'bit' => 'bit',
        'bits' => 'bit',
        // todo add others (kilobyte, kilobytes, etc)
    ];

    private static InformationConverter $converter;

    private int $factor;

    /**
     * Create from bytes (ie 546548) or a human-readable format (ie "1.1kB", "3.42 MiB").
     * Auto determines system from suffix (ie kB = decimal, MiB = binary) if possible,
     * otherwise, defaults to decimal.
     *
     * @param numeric                                 $value Bytes
     * @param string|array{0:int|float,1:string}|self $value
     */
    public static function from(Dimension|array|string|int $value): static
    {
        if (\is_numeric($value)) {
            return new self((int) $value, 'B');
        }

        return parent::from($value);
    }

    /**
     * Create in the binary system (ie MiB).
     *
     * @param Dimension|array{0:int|float,1:string}|string|int $value {@see from()}
     */
    public static function binary(Dimension|array|string|int $value): self
    {
        return self::from($value)->asBinary();
    }

    /**
     * Create in the decimal system (ie MB).
     *
     * @param Dimension|array{0:int|float,1:string}|string|int $value {@see from()}
     */
    public static function decimal(Dimension|array|string|int $value): self
    {
        return self::from($value)->asDecimal();
    }

    /**
     * Convert to the binary system (ie MB).
     */
    public function asBinary(): self
    {
        $clone = clone $this;
        $clone->factor = self::BINARY;

        return $clone;
    }

    /**
     * Convert to the decimal system (ie MB).
     */
    public function asDecimal(): self
    {
        $clone = clone $this;
        $clone->factor = self::DECIMAL;

        return $clone;
    }

    public function bytes(): int
    {
        return (int) $this->convertTo('B')->quantity();
    }

    public function bits(): int
    {
        return $this->bytes() * 8;
    }

    public function humanize(): self
    {
        $this->factor ??= \in_array($this->unit(), self::DECIMAL_UNITS) ? self::DECIMAL : self::BINARY;
        $i = 0;
        $units = \array_values(self::DECIMAL === $this->factor ? self::DECIMAL_UNITS : self::BINARY_UNITS);
        $quantity = $this->bytes();

        while (($quantity / $this->factor) >= 1 && $i < (\count($units) - 1)) {
            $quantity /= $this->factor;
            ++$i;
        }

        return new self($quantity, $units[$i]);
    }

    /**
     * @param numeric                                 $other Bytes
     * @param string|array{0:int|float,1:string}|self $other
     */
    public function isEqualTo(Dimension|array|string|int $other): bool
    {
        return parent::isEqualTo(self::from($other));
    }

    /**
     * @param numeric                                 $other Bytes
     * @param string|array{0:int|float,1:string}|self $other
     */
    public function isLargerThan(Dimension|array|string|int $other): bool
    {
        return parent::isLargerThan(self::from($other));
    }

    /**
     * @param numeric                                 $other Bytes
     * @param string|array{0:int|float,1:string}|self $other
     */
    public function isLargerThanOrEqualTo(Dimension|array|string|int $other): bool
    {
        return parent::isLargerThanOrEqualTo(self::from($other));
    }

    /**
     * @param numeric                                 $other Bytes
     * @param string|array{0:int|float,1:string}|self $other
     */
    public function isSmallerThan(Dimension|array|string|int $other): bool
    {
        return parent::isSmallerThan(self::from($other));
    }

    /**
     * @param numeric                                 $other Bytes
     * @param string|array{0:int|float,1:string}|self $other
     */
    public function isSmallerThanOrEqualTo(Dimension|array|string|int $other): bool
    {
        return parent::isSmallerThanOrEqualTo(self::from($other));
    }

    /**
     * @param numeric                                 $min Bytes
     * @param string|array{0:int|float,1:string}|self $min
     * @param numeric                                 $max Bytes
     * @param string|array{0:int|float,1:string}|self $max
     */
    public function isWithin(Dimension|array|string|int $min, Dimension|array|string|int $max, bool $inclusive = true): bool
    {
        return parent::isWithin(self::from($min), self::from($max), $inclusive);
    }

    /**
     * @param numeric                                 $min Bytes
     * @param string|array{0:int|float,1:string}|self $min
     * @param numeric                                 $max Bytes
     * @param string|array{0:int|float,1:string}|self $max
     */
    public function isOutside(Dimension|array|string|int $min, Dimension|array|string|int $max, bool $inclusive = false): bool
    {
        return parent::isOutside(self::from($min), self::from($max), $inclusive);
    }

    protected static function normalizeAndValidateUnits(string $unit): string
    {
        $lower = \mb_strtolower($unit);

        return self::BINARY_UNITS[$lower] ?? self::DECIMAL_UNITS[$lower] ?? self::ALTERNATE_MAP[$lower] ?? throw new \InvalidArgumentException(\sprintf('"%s" is an invalid informational unit. Valid units: %s.', $unit, \implode(', ', \array_merge(self::DECIMAL_UNITS, self::BINARY_UNITS))));
    }

    protected static function converter(): Converter
    {
        return self::$converter ??= new InformationConverter();
    }
}
