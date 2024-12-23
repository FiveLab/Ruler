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

namespace FiveLab\Component\Ruler\Tests\Unit\Executor\Elastica;

use Elastica\Query;
use FiveLab\Component\Ruler\Executor\Elastica\ElasticaVisitor;
use FiveLab\Component\Ruler\Node\Node;
use FiveLab\Component\Ruler\Operator\Operators;
use FiveLab\Component\Ruler\Query\RawSearchQuery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class ElasticaVisitorTest extends TestCase
{
    private ElasticaVisitor $visitor;

    protected function setUp(): void
    {
        $this->visitor = new ElasticaVisitor();
    }

    #[Test]
    #[TestWith([new Query()])]
    #[TestWith([new RawSearchQuery()])]
    public function shouldThrowErrorForUnknownNode(object $query): void
    {
        $node = new Node();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown node "FiveLab\Component\Ruler\Node\Node"');

        $this->visitor->visit($query, $node, [], new Operators([]));
    }
}
