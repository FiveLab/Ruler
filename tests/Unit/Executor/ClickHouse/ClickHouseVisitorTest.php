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

namespace FiveLab\Component\Ruler\Tests\Unit\Executor\ClickHouse;

use FiveLab\Component\Ruler\Executor\ClickHouse\ClickHouseVisitor;
use FiveLab\Component\Ruler\Node\Node;
use FiveLab\Component\Ruler\Operator\Operators;
use FiveLab\Component\Ruler\Query\ClickHouseQuery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ClickHouseVisitorTest extends TestCase
{
    private ClickHouseVisitor $visitor;

    protected function setUp(): void
    {
        $this->visitor = new ClickHouseVisitor();
    }

    #[Test]
    public function shouldThrowErrorForUnknownNode(): void
    {
        $node = new Node();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown node "FiveLab\Component\Ruler\Node\Node"');

        $this->visitor->visit(new ClickHouseQuery(), $node, [], new Operators([]));
    }
}
