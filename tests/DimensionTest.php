<?php

namespace Zenstruck\Dimension\Tests;

use PHPUnit\Framework\TestCase;
use Zenstruck\Dimension;
use Zenstruck\Dimension\Exception\ConversionNotPossible;

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
}
