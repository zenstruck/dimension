<?php

namespace Zenstruck\Dimension\Tests;

use PHPUnit\Framework\TestCase;
use Zenstruck\Dimension\Duration;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DurationTest extends TestCase
{
    /**
     * @test
     */
    public function can_create_from_seconds(): void
    {
        $this->assertSame('10 s', Duration::from(10)->toString());
    }

    /**
     * @test
     */
    public function can_create_from_date_interval(): void
    {
        $this->assertSame('777,600 s', Duration::from(new \DateInterval('P1W2D'))->toString());
    }

    /**
     * @test
     */
    public function quantity_cannot_be_negative(): void
    {
        $this->expectException(\LogicException::class);

        Duration::from(-10);
    }

    /**
     * @test
     * @dataProvider humanizeProvider
     */
    public function can_humanize($value, $expected): void
    {
        $this->assertSame($expected, (string) Duration::from($value)->humanize());
    }

    public static function humanizeProvider(): iterable
    {
        return [
            [0, '0 secs'],
            [0.3, '0 secs'],
            [1, '1 sec'],
            [2, '2 secs'],
            [59, '59 secs'],
            [60, '1 min'],
            ['1m', '1 min'],
            [61, '1 min'],
            [119, '1 min'],
            [120, '2 mins'],
            [121, '2 mins'],
            [3599, '59 mins'],
            [3600, '1 hr'],
            [7199, '1 hr'],
            [7200, '2 hrs'],
            [7201, '2 hrs'],
            [86399, '23 hrs'],
            [86400, '1 day'],
            [86401, '1 day'],
            [172799, '1 day'],
            [172800, '2 days'],
            [172801, '2 days'],
        ];
    }
}
