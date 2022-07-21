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
        'bits' => 'bits',
        // todo add others (kilobyte, kilobytes, etc)
    ];

    private static InformationConverter $converter;

    private int $factor;

    /**
     * Create in the binary system (ie MiB).
     *
     * @param mixed $value {@see from()}
     */
    public static function binary(mixed $value): self
    {
        return self::from($value)->asBinary();
    }

    /**
     * Create in the decimal system (ie MB).
     *
     * @param mixed $value {@see from()}
     */
    public static function decimal(mixed $value): self
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

    public function humanize(): string
    {
        $this->factor ??= \in_array($this->unit(), self::DECIMAL_UNITS) ? self::DECIMAL : self::BINARY;
        $i = 0;
        $units = \array_values(self::DECIMAL === $this->factor ? self::DECIMAL_UNITS : self::BINARY_UNITS);
        $quantity = $this->bytes();

        while (($quantity / $this->factor) >= 1 && $i < (\count($units) - 1)) {
            $quantity /= $this->factor;
            ++$i;
        }

        return (string) new self($quantity, $units[$i]);
    }

    protected static function createFrom(mixed $value): static
    {
        if (\is_numeric($value)) {
            return new self((int) $value, 'B'); // default to bytes
        }

        return parent::createFrom($value);
    }

    protected static function normalizeAndValidate(int|float &$quantity, string &$unit): void
    {
        $lower = \mb_strtolower($unit);
        $unit = self::BINARY_UNITS[$lower] ?? self::DECIMAL_UNITS[$lower] ?? self::ALTERNATE_MAP[$lower] ?? throw new \InvalidArgumentException(\sprintf('"%s" is an invalid informational unit. Valid units: %s.', $unit, \implode(', ', \array_merge(self::DECIMAL_UNITS, self::BINARY_UNITS))));

        if (\in_array($unit, ['B', 'bit', 'bits'])) {
            $quantity = (int) $quantity;
        }
    }

    protected static function converter(): Converter
    {
        return self::$converter ??= new InformationConverter();
    }
}
