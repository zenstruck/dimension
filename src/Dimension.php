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

    private float|int $quantity;
    private string $unit;

    /**
     * @param string $unit The unit of measure the $quantity represents
     */
    final public function __construct(float|int $quantity, string $unit)
    {
        $unit = \trim($unit);

        static::normalizeAndValidate($quantity, $unit);

        $this->quantity = $quantity;
        $this->unit = $unit;
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
        return $this->convertTo($name);
    }

    final public static function from(mixed $value): static
    {
        if ($value instanceof self) {
            return static::class === $value::class ? $value : new static($value->quantity, $value->unit); // @phpstan-ignore-line
        }

        if (\is_array($value) && 2 === \count($value = \array_values($value))) {
            return new static($value[0], $value[1]); // todo type error checking
        }

        if (\is_string($value) && !\is_numeric($value) && \preg_match('#^(-?[\d,]+(.[\d,]+)?)([\s\-_]+)?(.+)$#', \trim($value), $matches)) {
            return new static(\str_replace(',', '', $matches[1]), $matches[4]); // @phpstan-ignore-line
        }

        try {
            if (\is_string($value) && \is_array($decoded = \json_decode($value, true, 2, \JSON_THROW_ON_ERROR))) {
                return static::from($decoded);
            }
        } catch (\JsonException) {
        }

        return static::createFrom($value);
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
     * @param mixed $other {@see from()}
     *
     * @throws ComparisonNotPossible If unable to compare with $other
     */
    final public function isEqualTo(mixed $other): bool
    {
        return static::converter()->isEqualTo($this, static::from($other));
    }

    /**
     * @param mixed $other {@see from()}
     *
     * @throws ComparisonNotPossible If unable to compare with $other
     */
    final public function isLargerThan(mixed $other): bool
    {
        return static::converter()->isLargerThan($this, static::from($other));
    }

    /**
     * @param mixed $other {@see from()}
     *
     * @throws ComparisonNotPossible If unable to compare with $other
     */
    final public function isLargerThanOrEqualTo(mixed $other): bool
    {
        return static::converter()->isLargerThanOrEqualTo($this, static::from($other));
    }

    /**
     * @param mixed $other {@see from()}
     *
     * @throws ComparisonNotPossible If unable to compare with $other
     */
    final public function isSmallerThan(mixed $other): bool
    {
        return static::converter()->isSmallerThan($this, static::from($other));
    }

    /**
     * @param mixed $other {@see from()}
     *
     * @throws ComparisonNotPossible If unable to compare with $other
     */
    final public function isSmallerThanOrEqualTo(mixed $other): bool
    {
        return static::converter()->isSmallerThanOrEqualTo($this, static::from($other));
    }

    /**
     * @param mixed $min       {@see from()}
     * @param mixed $max       {@see from()}
     * @param bool  $inclusive Whether to match the $min/$max exactly
     *
     * @throws ComparisonNotPossible If unable to compare with $min/$max
     */
    final public function isWithin(mixed $min, mixed $max, bool $inclusive = true): bool
    {
        if ($inclusive) {
            return $this->isLargerThanOrEqualTo($min) && $this->isSmallerThanOrEqualTo($max);
        }

        return $this->isLargerThan($min) && $this->isSmallerThan($max);
    }

    /**
     * @param mixed $min       {@see from()}
     * @param mixed $max       {@see from()}
     * @param bool  $inclusive Whether to match the $min/$max exactly
     *
     * @throws ComparisonNotPossible If unable to compare with $min/$max
     */
    final public function isOutside(mixed $min, mixed $max, bool $inclusive = false): bool
    {
        if ($inclusive) {
            return $this->isSmallerThanOrEqualTo($min) || $this->isLargerThanOrEqualTo($max);
        }

        return $this->isSmallerThan($min) || $this->isLargerThan($max);
    }

    protected static function createFrom(mixed $value): static
    {
        throw new \InvalidArgumentException(\sprintf('"%s" is an invalid dimensional value.', \is_scalar($value) ? $value : \get_debug_type($value)));
    }

    protected static function converter(): Converter
    {
        return self::$converter ??= ChainConverter::default();
    }

    /**
     * @throws \InvalidArgumentException If invalid
     */
    protected static function normalizeAndValidate(int|float &$quantity, string &$unit): void
    {
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
