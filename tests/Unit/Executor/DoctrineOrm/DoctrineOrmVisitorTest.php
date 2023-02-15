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
use PHPUnit\Framework\TestCase;

class DoctrineOrmVisitorTest extends TestCase
{
    /**
     * @var DoctrineOrmVisitor
     */
    private DoctrineOrmVisitor $visitor;

    /**
     * @test
     */
    protected function setUp(): void
    {
        $this->visitor = new DoctrineOrmVisitor($this->createMock(EntityManagerInterface::class));
    }

    /**
     * @test
     */
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
