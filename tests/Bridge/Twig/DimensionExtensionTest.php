<?php

namespace Zenstruck\Dimension\Tests\Bridge\Twig;

use Twig\Test\IntegrationTestCase;
use Zenstruck\Dimension\Bridge\Twig\DimensionExtension;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DimensionExtensionTest extends IntegrationTestCase
{
    protected function getExtensions(): array
    {
        return [new DimensionExtension()];
    }

    protected function getFixturesDir(): string
    {
        return __DIR__.'/Fixtures/';
    }
}
