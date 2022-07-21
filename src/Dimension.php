<?php

namespace Zenstruck;

use Zenstruck\Dimension\Converter;
use Zenstruck\Dimension\Converter\ChainConverter;
use Zenstruck\Dimension\Exception\ComparisonNotPossible;
use Zenstruck\Dimension\Exception\ConversionNotPossible;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class Dimension implements \Stringable, \JsonSerializable
{
    /** @var array<string,\NumberFormatter> */
    private static array $formatters = [];
    private static Converter $converter;

    private string $unit;

    /**
     * @param string $unit The unit of measure the $quantity represents
     */
    final public function __construct(private float|int $quantity, string $unit)
    {
        $this->unit = static::normalizeAndValidateUnits($unit);
    }

    final public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @param mixed[] $arguments
     */
    final public function __call(string $name, array $arguments): static
    {
        return $this->convertTo(static::normalizeAndValidateUnits($name));
    }

    /**
     * @param string|array{0:int|float,1:string}|self $value
     */
    public static function from(string|array|self $value): static
    {
        if ($value instanceof static) {
            return $value;
        }

        if (\is_array($value) && 2 === \count($value = \array_values($value))) {
            return new static($value[0], $value[1]); // todo error checking
        }

        if (!\is_string($value)) {
            throw new \InvalidArgumentException('Invalid dimensional array.');
        }

        if (\preg_match('#^(-?[\d,]+(.[\d,]+)?)([\s\-_]+)?(.+)$#', \trim($value), $matches)) {
            return new static(\str_replace(',', '', $matches[1]), $matches[4]); // @phpstan-ignore-line
        }

        try {
            if (\is_array($decoded = \json_decode($value, true, 2, \JSON_THROW_ON_ERROR))) {
                return static::from($decoded); // @phpstan-ignore-line
            }
        } catch (\JsonException) {
        }

        throw new \InvalidArgumentException(\sprintf('"%s" is an invalid dimensional value.', $value));
    }

    final public function quantity(): float|int
    {
        return $this->quantity;
    }

    final public function unit(): string
    {
        return $this->unit;
    }

    final public function toString(): string
    {
        if (!\class_exists(\NumberFormatter::class)) {
            return \sprintf('%.2f %s', $this->quantity(), $this->unit());
        }

        return \sprintf('%s %s', self::formatter()->format($this->quantity()), $this->unit());
    }

    /**
     * @throws ConversionNotPossible If unable to convert to $unit
     */
    final public function convertTo(string $unit): static
    {
        if ($unit === $this->unit) {
            return $this;
        }

        return static::converter()->convertTo($this, $unit);
    }

    /**
     * @return array{0:int|float,1:string}
     */
    final public function toArray(): array
    {
        return [$this->quantity(), $this->unit()];
    }

    /**
     * @return array{0:int|float,1:string}
     */
    final public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param string|array{0:int|float,1:string}|self $other {@see from()}
     *
     * @throws ComparisonNotPossible If unable to compare with $other
     */
    public function isEqualTo(string|array|self $other): bool
    {
        return static::converter()->isEqualTo($this, static::from($other));
    }

    /**
     * @param string|array{0:int|float,1:string}|self $other {@see from()}
     *
     * @throws ComparisonNotPossible If unable to compare with $other
     */
    public function isLargerThan(string|array|self $other): bool
    {
        return static::converter()->isLargerThan($this, static::from($other));
    }

    /**
     * @param string|array{0:int|float,1:string}|self $other {@see from()}
     *
     * @throws ComparisonNotPossible If unable to compare with $other
     */
    public function isLargerThanOrEqualTo(string|array|self $other): bool
    {
        return static::converter()->isLargerThanOrEqualTo($this, static::from($other));
    }

    /**
     * @param string|array{0:int|float,1:string}|self $other {@see from()}
     *
     * @throws ComparisonNotPossible If unable to compare with $other
     */
    public function isSmallerThan(string|array|self $other): bool
    {
        return static::converter()->isSmallerThan($this, static::from($other));
    }

    /**
     * @param string|array{0:int|float,1:string}|self $other {@see from()}
     *
     * @throws ComparisonNotPossible If unable to compare with $other
     */
    public function isSmallerThanOrEqualTo(string|array|self $other): bool
    {
        return static::converter()->isSmallerThanOrEqualTo($this, static::from($other));
    }

    /**
     * @param string|array{0:int|float,1:string}|self $min       {@see from()}
     * @param string|array{0:int|float,1:string}|self $max       {@see from()}
     * @param bool                                    $inclusive Whether to match the $min/$max exactly
     *
     * @throws ComparisonNotPossible If unable to compare with $min/$max
     */
    public function isWithin(string|array|self $min, string|array|self $max, bool $inclusive = true): bool
    {
        if ($inclusive) {
            return $this->isLargerThanOrEqualTo($min) && $this->isSmallerThanOrEqualTo($max);
        }

        return $this->isLargerThan($min) && $this->isSmallerThan($max);
    }

    /**
     * @param string|array{0:int|float,1:string}|self $min       {@see from()}
     * @param string|array{0:int|float,1:string}|self $max       {@see from()}
     * @param bool                                    $inclusive Whether to match the $min/$max exactly
     *
     * @throws ComparisonNotPossible If unable to compare with $min/$max
     */
    public function isOutside(string|array|self $min, string|array|self $max, bool $inclusive = false): bool
    {
        if ($inclusive) {
            return $this->isSmallerThanOrEqualTo($min) || $this->isLargerThanOrEqualTo($max);
        }

        return $this->isSmallerThan($min) || $this->isLargerThan($max);
    }

    protected static function converter(): Converter
    {
        return self::$converter ??= ChainConverter::default();
    }

    /**
     * @throws \InvalidArgumentException If invalid
     */
    protected static function normalizeAndValidateUnits(string $unit): string
    {
        return $unit;
    }

    private static function formatter(): \NumberFormatter
    {
        if (isset(self::$formatters[$locale = \Locale::getDefault()])) {
            return self::$formatters[$locale];
        }

        self::$formatters[$locale] = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::DECIMAL);
        self::$formatters[$locale]->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 2);
        self::$formatters[$locale]->setAttribute(\NumberFormatter::ROUNDING_MODE, \NumberFormatter::ROUND_HALFUP);

        return self::$formatters[$locale];
    }
}
