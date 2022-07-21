<?php

namespace Zenstruck\Dimension\Tests;

use PHPUnit\Framework\TestCase;
use Zenstruck\Dimension\Information;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class InformationTest extends TestCase
{
    /**
     * @test
     * @dataProvider fromValueProvider
     */
    public function can_create_from_value(string|int|Information $value, int $bytes, string $humanized): void
    {
        $information = Information::from($value);

        $this->assertSame($bytes, $information->bytes());
        $this->assertSame($bytes * 8, $information->bits());
        $this->assertSame($humanized, (string) $information->humanize());
    }

    public static function fromValueProvider(): iterable
    {
        yield [400, 400, '400 B'];
        yield [400000, 400000, '400 kB'];
        yield [Information::from(400), 400, '400 B'];
        yield ['400', 400, '400 B'];
        yield ['400B', 400, '400 B'];
        yield ['400  B', 400, '400 B'];
        yield ['400  b', 4_00, '400 B'];
        yield ['4.2 KB', 4_200, '4.2 kB'];
        yield ['4.2 MB', 4_200_000, '4.2 MB'];
        yield ['4.2 mb', 4_200_000, '4.2 MB'];
        yield ['521565415613.5468 kb', 521_565_415_613_546, '521.57 TB'];
        yield ['4.2 KiB', 4_300, '4.2 KiB'];
        yield ['4.2 MiB', 4_404_019, '4.2 MiB'];
        yield ['4.2 mib', 4_404_019, '4.2 MiB'];
        yield ['521565415613.5468 kib', 534_082_985_588_271, '485.75 TiB'];
        yield ['400K', 400_000, '400 kB'];
    }

    /**
     * @test
     * @dataProvider fromBinaryProvider
     */
    public function can_create_from_binary(int $bytes, string $humanized): void
    {
        $information = Information::binary($bytes);

        $this->assertSame($bytes, $information->bytes());
        $this->assertSame($humanized, (string) $information->humanize());
    }

    public static function fromBinaryProvider(): iterable
    {
        yield [0, '0 B'];
        yield [400, '400 B'];
        yield [1023, '1,023 B'];
        yield [1024, '1 KiB'];
        yield [54651654, '52.12 MiB'];
    }

    /**
     * @test
     * @dataProvider fromDecimalProvider
     */
    public function can_create_from_decimal(int $bytes, string $humanized): void
    {
        $information = Information::decimal($bytes);

        $this->assertSame($bytes, $information->bytes());
        $this->assertSame($humanized, (string) $information->humanize());
    }

    public static function fromDecimalProvider(): iterable
    {
        yield [0, '0 B'];
        yield [400, '400 B'];
        yield [999, '999 B'];
        yield [1000, '1 kB'];
        yield [54651654, '54.65 MB'];
    }

    /**
     * @test
     */
    public function can_convert_between_systems(): void
    {
        $information = Information::decimal(1024);

        $this->assertSame('1.02 kB', (string) $information->humanize());

        $information = $information->asBinary();

        $this->assertSame('1 KiB', (string) $information->humanize());

        $information = $information->asDecimal();

        $this->assertSame('1.02 kB', (string) $information->humanize());
    }

    /**
     * @test
     */
    public function create_with_invalid_unit(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Information::from('6.6 foo');
    }

    /**
     * @test
     */
    public function magic_methods(): void
    {
        $information = Information::from(4_256_001);

        $this->assertSame('4,256 kB', $information->kb()->toString());
        $this->assertSame('4.26 MB', $information->mb()->toString());
        $this->assertSame('0 GB', $information->gb()->toString());
    }
}
