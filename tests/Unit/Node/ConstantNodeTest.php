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

namespace FiveLab\Component\Ruler\Tests\Node;

use FiveLab\Component\Ruler\Node\ConstantNode;
use PHPUnit\Framework\TestCase;

class ConstantNodeTest extends TestCase
{
    /**
     * @test
     *
     * @param        $value
     * @param string $expectedString
     *
     * @dataProvider provideData
     */
    public function shouldSuccessCreate($value, string $expectedString): void
    {
        $node = new ConstantNode($value);

        self::assertEquals($value, $node->getValue());
        self::assertEquals($expectedString, (string) $node);
    }

    /**
     * Provide data for testing
     *
     * @return array
     */
    public function provideData(): array
    {
        return [
            [true, 'true'],
            [false, 'false'],
            [null, 'null'],
            [11, '11'],
            [12.55, '12.55'],
            ['bar', 'bar'],
        ];
    }
}
