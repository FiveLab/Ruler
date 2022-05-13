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

use FiveLab\Component\Ruler\Node\NameNode;
use PHPUnit\Framework\TestCase;

class NameNodeTest extends TestCase
{
    /**
     * @test
     *
     * @param string $name
     * @param array  $expectedSplittedParts
     *
     * @dataProvider provideData
     */
    public function shouldSuccessCreate(string $name, array $expectedSplittedParts): void
    {
        $node = new NameNode($name);

        self::assertEquals($expectedSplittedParts, $node->getSplittedParts());
    }

    /**
     * Provide data for testing
     *
     * @return array[]
     */
    public function provideData(): array
    {
        return [
            ['bar', ['bar']],
            ['foo.bar', ['foo', 'bar']],
            ['foo\.bar.some', ['foo.bar', 'some']],
            ['foo\.bar\.some', ['foo.bar.some']],
        ];
    }
}
