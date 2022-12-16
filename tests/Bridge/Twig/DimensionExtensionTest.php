<?php

/*
 * This file is part of the zenstruck/dimension package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
