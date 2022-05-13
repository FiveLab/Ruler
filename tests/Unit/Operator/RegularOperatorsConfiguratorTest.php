<?php

/*
 * This file is part of the FiveLab Ruler package
 *
 * (c) FiveLab
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

declare(strict_types = 1);

namespace FiveLab\Component\Ruler\Tests\Unit\Operator;

use FiveLab\Component\Ruler\Operator\Operators;
use FiveLab\Component\Ruler\Operator\RegularOperatorsConfigurator;
use PHPUnit\Framework\TestCase;

class RegularOperatorsConfiguratorTest extends TestCase
{
    /**
     * @var Operators
     */
    private Operators $operators;

    /**
     * @test
     */
    protected function setUp(): void
    {
        $this->operators = new Operators([]);

        (new RegularOperatorsConfigurator())->configure($this->operators);
    }

    /**
     * @test
     *
     * @param string $operator
     * @param mixed  $a
     * @param mixed  $b
     * @param string $expected
     *
     * @dataProvider provideDataForHandle
     */
    public function shouldSuccessHandle(string $operator, $a, $b, string $expected): void
    {
        $handle = $this->operators->get($operator);

        $result = $handle($a, $b);

        self::assertEquals($expected, $result);
    }

    /**
     * Provide data for handle
     *
     * @return array
     */
    public function provideDataForHandle(): array
    {
        return [
            ['=', 'foo', '1', 'foo = 1'],
            ['!=', 'bar', 'some', 'bar != some'],
            ['>', '1', '2', '1 > 2'],
            ['>=', '3', '4', '3 >= 4'],
            ['<', '1', '1', '1 < 1'],
            ['<=', '2', '2', '2 <= 2'],
            ['+', 123, 321, '123 + 321'],
            ['-', 'foo', 11, 'foo - 11'],
            ['*', 'bar', 'foo', 'bar * foo'],
            ['/', '5.44', 4.21, '5.44 / 4.21'],
        ];
    }
}
