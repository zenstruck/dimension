<?php

namespace Zenstruck\Dimension\Tests;

use PHPUnit\Framework\TestCase;
use Zenstruck\Dimension;
use Zenstruck\Dimension\Exception\ConversionNotPossible;
use Zenstruck\Dimension\Information;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DimensionTest extends TestCase
{
    /**
     * @test
     * @dataProvider quantityUnitProvider
     */
    public function can_get_quantity_and_unit($quantity, $expected): void
    {
        $dimension = new Dimension($quantity, 'mm');

        $this->assertSame($expected, $dimension->quantity());
        $this->assertSame('mm', $dimension->unit());
        $this->assertSame(\sprintf('%s mm', $expected), (string) $dimension);
        $this->assertSame([$expected, 'mm'], $dimension->jsonSerialize());
    }

    public static function quantityUnitProvider(): iterable
    {
        yield [5, 5];
        yield [5.0, 5.0];
        yield ['5', 5];
        yield ['5.0', 5.0];
    }

    /**
     * @test
     * @dataProvider createProvider
     */
    public function can_create_from($value, $expectedQuantity, $expectedUnit)
    {
        $dimension = Dimension::from($value);

        $this->assertSame($expectedQuantity, $dimension->quantity());
        $this->assertSame($expectedUnit, $dimension->unit());
    }

    public static function createProvider(): iterable
    {
        yield ['45000mm', 45000, 'mm'];
        yield ['45,000mm', 45000, 'mm'];
        yield ['-45,000.000,001C', -45000.000001, 'C'];
        yield ['45mm', 45, 'mm'];
        yield ['45 mm', 45, 'mm'];
        yield ['45    - mm', 45, 'mm'];
        yield ['45.0mm', 45.0, 'mm'];
        yield ['45.0546 mm', 45.0546, 'mm'];
        yield [new Dimension(45, 'mm'), 45, 'mm'];
        yield [['quantity' => 45, 'unit' => 'mm'], 45, 'mm'];
        yield [[45.0, 'mm'], 45.0, 'mm'];
        yield [['45.0', 'mm'], 45.0, 'mm'];
    }

    /**
     * @test
     */
    public function create_from_self(): void
    {
        $dimension = Dimension::from('10k');

        $this->assertSame($dimension, Dimension::from($dimension));
        $this->assertSame(Dimension::class, Dimension::from($dimension)::class);
        $this->assertNotSame($dimension, Information::from($dimension));
        $this->assertSame(Information::class, Information::from($dimension)::class);

        $information = Information::from('10k');

        $this->assertNotSame($information, Dimension::from($information));
        $this->assertSame(Dimension::class, Dimension::from($information)::class);
        $this->assertSame($information, Information::from($information));
        $this->assertSame(Information::class, Information::from($information)::class);
    }

    /**
     * @test
     */
    public function create_from_invalid_array(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Dimension::from([]);
    }

    /**
     * @test
     */
    public function create_from_invalid_json_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Dimension::from('[]');
    }

    /**
     * @test
     */
    public function create_from_invalid_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Dimension::from('blah');
    }

    /**
     * @test
     */
    public function create_from_dimensionless_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Dimension::from('60');
    }

    /**
     * @test
     */
    public function can_convert_to_string(): void
    {
        $this->assertSame('45 mm', (string) new Dimension(45, 'mm'));
        $this->assertSame('45,000 mm', (string) new Dimension(45000, 'mm'));
        $this->assertSame('-45,000 mm', (string) new Dimension(-45000, 'mm'));
        $this->assertSame('45,000.1 mm', (string) new Dimension(45000.1, 'mm'));
        $this->assertSame('45,000 mm', (string) new Dimension(45000.001, 'mm'));
        $this->assertSame('45,000.01 mm', (string) new Dimension(45000.006, 'mm'));
    }

    /**
     * @test
     */
    public function can_json_encode_and_decode(): void
    {
        $dimension = new Dimension(45, 'mm');
        $encoded = \json_encode($dimension);

        $this->assertSame('[45,"mm"]', $encoded);
        $this->assertEquals($dimension, Dimension::from($encoded));
    }

    /**
     * @test
     */
    public function can_serialize(): void
    {
        $dimension = new Dimension(45, 'mm');

        $this->assertEquals($dimension, \unserialize(\serialize($dimension)));
    }

    /**
     * @test
     * @dataProvider conversionProvider
     */
    public function can_convert_itself($dimension, $toUnit, $expected): void
    {
        $this->assertSame($expected, (string) Dimension::from($dimension)->convertTo($toUnit));
    }

    public static function conversionProvider(): iterable
    {
        yield ['1in', 'mm', '25.4 mm'];
        yield ['1ft', 'in', '12 in'];
        yield ['1yd', 'ft', '3 ft'];
        yield ['22m', 'ft', '72.18 ft'];
        yield ['5m', 'm', '5 m'];
        yield ['72.18 ft', 'm', '22 m'];
        yield ['72.18 feet', 'metres', '22 metres'];
        yield ['32.1km', 'miles', '19.95 miles'];
        yield ['32.1 kilometers', 'miles', '19.95 miles'];
        yield ['16.2 m', 'mm', '16,200 mm'];
        yield ['66.6543 K', 'F', '-339.69 F'];
        yield ['4250 celsius', 'kilokelvin', '4.52 kilokelvin'];
        yield ['5 kilograms', 'g', '5,000 g'];
        yield ['5mg', 'micrograms', '5,000 micrograms'];
        yield ['55kg', 'lbs', '121.25 lbs'];
        yield ['6kg', 'stone', '0.94 stone'];
        yield ['500kg', 'tonne', '0.5 tonne'];
        yield ['32 bytes', 'bits', '256 bits'];
        yield ['1 byte', 'bits', '8 bits'];
        yield ['1 bit', 'bytes', '0.13 bytes'];
        yield ['12 MiB', 'MB', '12.58 MB'];
        yield ['1.2 GB', 'MB', '1,200 MB'];
        yield ['1m', 's', '60 s'];
        yield ['32w', 'y', '0.61 y'];
        yield ['6"', "'", "0.5 '"];
        yield ['1m', 's', '60 s'];
        yield ['60s', 'm', '1 m'];
        yield ['1B', 'bits', '8 bits'];
        yield ['24B', 'bits', '192 bits'];
    }

    /**
     * @test
     */
    public function from_unit_not_registered(): void
    {
        $this->expectException(ConversionNotPossible::class);

        Dimension::from('22foo')->convertTo('m');
    }

    /**
     * @test
     */
    public function to_unit_not_registered(): void
    {
        $this->expectException(ConversionNotPossible::class);

        Dimension::from('22m')->convertTo('bar');
    }

    /**
     * @test
     */
    public function unit_mismatch(): void
    {
        $this->expectException(ConversionNotPossible::class);

        Dimension::from('22s')->convertTo('meter');
    }

    /**
     * @test
     * @dataProvider comparisonProvider
     */
    public function comparison_test(string $first, string $method, string $second, bool $expected): void
    {
        $this->assertSame($expected, Dimension::from($first)->{$method}($second));
    }

    public static function comparisonProvider(): iterable
    {
        yield ['10m', 'isLargerThan', '5m', true];
        yield ['10m', 'isLargerThan', '15m', false];
        yield ['10m', 'isLargerThan', '10m', false];
        yield ['1.2 TB', 'isLargerThan', '1.1TB', true];
        yield ['1.2 TB', 'isLargerThan', '1.3TB', false];

        yield ['10m', 'isLargerThanOrEqualTo', '5m', true];
        yield ['10m', 'isLargerThanOrEqualTo', '15m', false];
        yield ['10m', 'isLargerThanOrEqualTo', '10m', true];
        yield ['1.2 TB', 'isLargerThanOrEqualTo', '1.1TB', true];
        yield ['1.2 TB', 'isLargerThanOrEqualTo', '1.3TB', false];

        yield ['10m', 'isSmallerThan', '5m', false];
        yield ['10m', 'isSmallerThan', '15m', true];
        yield ['10m', 'isSmallerThan', '10m', false];
        yield ['1.2 TB', 'isSmallerThan', '1.3TB', true];
        yield ['1.2 TB', 'isSmallerThan', '1.1TB', false];

        yield ['10m', 'isSmallerThanOrEqualTo', '5m', false];
        yield ['10m', 'isSmallerThanOrEqualTo', '15m', true];
        yield ['10m', 'isSmallerThanOrEqualTo', '10m', true];
        yield ['1.2 TB', 'isSmallerThanOrEqualTo', '1.3TB', true];
        yield ['1.2 TB', 'isSmallerThanOrEqualTo', '1.1TB', false];

        yield ['10m', 'isEqualTo', '5m', false];
        yield ['10m', 'isEqualTo', '15m', false];
        yield ['10m', 'isEqualTo', '10m', true];
        yield ['1.2 TB', 'isEqualTo', '1.3TB', false];
        yield ['1.2 TB', 'isEqualTo', '1.1TB', false];
        yield ['1.2 TB', 'isEqualTo', '1.2 TB', true];
    }

    /**
     * @test
     * @dataProvider withinRangeProvider
     */
    public function within_range(string $value, string $min, string $max, bool $inclusive, bool $expected): void
    {
        $this->assertSame($expected, Dimension::from($value)->isWithin($min, $max, $inclusive));
    }

    public static function withinRangeProvider(): iterable
    {
        yield ['56m', '40m', '60m', true, true];
        yield ['40m', '40m', '40m', true, true];
        yield ['40m', '40m', '40m', false, false];
        yield ['39m', '40m', '60m', true, false];
        yield ['80m', '40m', '60m', true, false];
        yield ['1.3GB', '1.1GB', '1.5GiB', true, true];
        yield ['1.3MB', '1.1GB', '1.5GiB', true, false];
        yield ['1.3TB', '1.1GB', '1.5GiB', true, false];
    }

    /**
     * @test
     * @dataProvider outsideRangeProvider
     */
    public function outside_range(string $value, string $min, string $max, bool $inclusive, bool $expected): void
    {
        $this->assertSame($expected, Dimension::from($value)->isOutside($min, $max, $inclusive));
    }

    public static function outsideRangeProvider(): iterable
    {
        yield ['56m', '40m', '60m', true, false];
        yield ['40m', '40m', '40m', true, true];
        yield ['40m', '40m', '40m', false, false];
        yield ['39m', '40m', '60m', true, true];
        yield ['80m', '40m', '60m', true, true];
        yield ['1.3GB', '1.1GB', '1.5GiB', true, false];
        yield ['1.3MB', '1.1GB', '1.5GiB', true, true];
        yield ['1.3TB', '1.1GB', '1.5GiB', true, true];
    }
}
