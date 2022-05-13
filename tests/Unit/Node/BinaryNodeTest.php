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

use FiveLab\Component\Ruler\Node\BinaryNode;
use FiveLab\Component\Ruler\Node\ConstantNode;
use PHPUnit\Framework\TestCase;

class BinaryNodeTest extends TestCase
{
    /**
     * @test
     */
    public function shouldSuccessCreate(): void
    {
        $node = new BinaryNode('foo', new ConstantNode('bar'), new ConstantNode('foo'));

        self::assertEquals('foo', $node->getOperator());
        self::assertEquals(new ConstantNode('bar'), $node->getLeft());
        self::assertEquals(new ConstantNode('foo'), $node->getRight());
    }
}
