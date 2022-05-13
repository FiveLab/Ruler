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

use FiveLab\Component\Ruler\Node\ParameterNode;
use PHPUnit\Framework\TestCase;

class ParameterNodeTest extends TestCase
{
    /**
     * @test
     */
    public function shouldSuccessCreate(): void
    {
        $node = new ParameterNode('foo_bar');

        self::assertEquals('foo_bar', $node->getName());
        self::assertEquals('foo_bar', (string) $node);
    }
}
