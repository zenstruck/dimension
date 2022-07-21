<?php

namespace Zenstruck\Dimension\Bridge\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Zenstruck\Dimension;
use Zenstruck\Dimension\Duration;
use Zenstruck\Dimension\Information;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DimensionExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('dimension', fn(mixed $value): Dimension => Dimension::from($value)),
            new TwigFilter('information', fn(mixed $value): Information => Information::from($value)),
            new TwigFilter('duration', fn(mixed $value): Duration => Duration::from($value)),
        ];
    }
}
