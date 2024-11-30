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

namespace FiveLab\Component\Ruler\Tests\Unit\Target;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use FiveLab\Component\Ruler\Target\DoctrineOrmTarget;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DoctrineOrmTargetTest extends TestCase
{
    private DoctrineOrmTarget $target;

    protected function setUp(): void
    {
        $this->target = new DoctrineOrmTarget();
    }

    #[Test]
    public function shouldNotSupports(): void
    {
        $supports = $this->target->supports(new \stdClass());

        self::assertFalse($supports);
    }

    #[Test]
    public function shouldSuccessSupports(): void
    {
        $qb = $this->createMock(QueryBuilder::class);

        $supports = $this->target->supports($qb);

        self::assertTrue($supports);
    }

    #[Test]
    public function shouldSuccessGetIdentifier(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $expectedIdentifier = \spl_object_hash($em);

        $qb = new QueryBuilder($em);

        $identifier = $this->target->getIdentifier($qb);

        self::assertEquals($expectedIdentifier, $identifier);
    }
}
