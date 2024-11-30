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

namespace FiveLab\Component\Ruler\Tests\Unit\Executor\DoctrineOrm;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use FiveLab\Component\Ruler\Executor\DoctrineOrm\DoctrineOrmVisitor;
use FiveLab\Component\Ruler\Executor\ExecutionContext;
use FiveLab\Component\Ruler\Node\Node;
use FiveLab\Component\Ruler\Operator\Operators;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DoctrineOrmVisitorTest extends TestCase
{
    private DoctrineOrmVisitor $visitor;

    protected function setUp(): void
    {
        $this->visitor = new DoctrineOrmVisitor($this->createMock(EntityManagerInterface::class));
    }

    #[Test]
    public function shouldThrowErrorForUnknownNode(): void
    {
        $node = new Node();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown node "FiveLab\Component\Ruler\Node\Node"');

        $this->visitor->visit(
            new QueryBuilder($this->createMock(EntityManagerInterface::class)),
            $node,
            [],
            new Operators([]),
            new ExecutionContext()
        );
    }
}
