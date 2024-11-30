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
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class RegularOperatorsConfiguratorTest extends TestCase
{
    private Operators $operators;

    protected function setUp(): void
    {
        $this->operators = new Operators([]);

        (new RegularOperatorsConfigurator())->configure($this->operators);
    }

    #[Test]
    #[TestWith(['=', 'foo', '1', 'foo = 1'])]
    #[TestWith(['!=', 'bar', 'some', 'bar != some'])]
    #[TestWith(['>', '1', '2', '1 > 2'])]
    #[TestWith(['>=', '3', '4', '3 >= 4'])]
    #[TestWith(['<', '1', '1', '1 < 1'])]
    #[TestWith(['<=', '2', '2', '2 <= 2'])]
    #[TestWith(['+', 123, 321, '123 + 321'])]
    #[TestWith(['-', 'foo', 11, 'foo - 11'])]
    #[TestWith(['*', 'bar', 'foo', 'bar * foo'])]
    #[TestWith(['/', '5.44', 4.21, '5.44 / 4.21'])]
    public function shouldSuccessHandle(string $operator, $a, $b, string $expected): void
    {
        $handle = $this->operators->get($operator);

        $result = $handle($a, $b);

        self::assertEquals($expected, $result);
    }
}
