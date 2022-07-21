<?php

namespace Zenstruck\Dimension;

use Zenstruck\Dimension;
use Zenstruck\Dimension\Converter\DurationConverter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Duration extends Dimension
{
    private const TIME_FORMATS = [
        [0, '< 1 sec'],
        [1, '1 sec'],
        [2, 'secs', 1],
        [60, '1 min'],
        [120, 'mins', 60],
        [3600, '1 hr'],
        [7200, 'hrs', 3600],
        [86400, '1 day'],
        [172800, 'days', 86400],
    ];

    private static DurationConverter $converter;

    /**
     * @author Fabien Potencier <fabien@symfony.com>
     * @source https://github.com/symfony/symfony/blob/9ccf3651a8a2ac7aee0002f67e8a27b2b85d34d0/src/Symfony/Component/Console/Helper/Helper.php#L94
     */
    public function humanize(): string
    {
        $seconds = $this->convertTo('s')->quantity();

        foreach (self::TIME_FORMATS as $i => $format) {
            if ($seconds >= $format[0]) {
                if ((isset(self::TIME_FORMATS[$i + 1]) && $seconds < self::TIME_FORMATS[$i + 1][0]) || $i === \count(self::TIME_FORMATS) - 1) {
                    if (2 === \count($format)) {
                        return $format[1];
                    }

                    return \floor($seconds / ($format[2] ?? 1)).' '.$format[1];
                }
            }
        }

        throw new \LogicException('Unable to humanize.');
    }

    protected static function createFrom(mixed $value): static
    {
        if (\is_numeric($value)) {
            return new self((int) $value, 's'); // default to seconds
        }

        if ($value instanceof \DateInterval) {
            return self::createFrom(\DateTime::createFromFormat('U', '0')->add($value)->getTimestamp()); // @phpstan-ignore-line
        }

        return parent::createFrom($value);
    }

    protected static function normalizeAndValidate(float|int &$quantity, string &$unit): void
    {
        if ($quantity < 0) {
            throw new \LogicException('Quantity cannot be less than zero.');
        }
    }

    protected static function converter(): Converter
    {
        return self::$converter ??= new DurationConverter();
    }
}
