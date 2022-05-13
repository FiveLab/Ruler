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

namespace FiveLab\Component\Ruler\Unit\Target;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use FiveLab\Component\Ruler\Target\DoctrineOrmTarget;
use PHPUnit\Framework\TestCase;

class DoctrineOrmTargetTest extends TestCase
{
    /**
     * @var DoctrineOrmTarget
     */
    private DoctrineOrmTarget $target;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->target = new DoctrineOrmTarget();
    }

    /**
     * @test
     */
    public function shouldNotSupports(): void
    {
        $supports = $this->target->supports(new \stdClass());

        self::assertFalse($supports);
    }

    /**
     * @test
     */
    public function shouldSuccessSupports(): void
    {
        $qb = $this->createMock(QueryBuilder::class);

        $supports = $this->target->supports($qb);

        self::assertTrue($supports);
    }

    /**
     * @test
     */
    public function shouldSuccessGetIdentifier(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $expectedIdentifier = \spl_object_hash($em);

        $qb = new QueryBuilder($em);

        $identifier = $this->target->getIdentifier($qb);

        self::assertEquals($expectedIdentifier, $identifier);
    }
}
