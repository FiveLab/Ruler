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

namespace FiveLab\Component\Ruler\Tests\Unit\Specification;

use FiveLab\Component\Ruler\Specification\CompositeSpecification;
use FiveLab\Component\Ruler\Specification\SimpleSpecification;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CompositeSpecificationTest extends TestCase
{
    #[Test]
    public function shouldSuccessCreateWithOneSpec(): void
    {
        $spec = new CompositeSpecification('AND', new SimpleSpecification('1 = :param', ['param' => true]));

        self::assertEquals('(1 = :param)', $spec->getRule());
        self::assertEquals(['param' => true], $spec->getParameters());
    }

    #[Test]
    public function shouldSuccessCreateWithMoreSpec(): void
    {
        $now = new \DateTime();

        $spec = new CompositeSpecification(
            'OR',
            new SimpleSpecification('id = :id', ['id' => 123]),
            new SimpleSpecification('number > :number', ['number' => 'foo']),
            new SimpleSpecification('createdAt > :created', ['created' => $now])
        );

        self::assertEquals('(id = :id OR number > :number OR createdAt > :created)', $spec->getRule());
        self::assertEquals([
            'id'      => 123,
            'number'  => 'foo',
            'created' => $now,
        ], $spec->getParameters());
    }

    #[Test]
    public function shouldSuccessFixDuplicateParameterNames(): void
    {
        $spec = new CompositeSpecification(
            'OR',
            new SimpleSpecification('id = :id', ['id' => 111]),
            new SimpleSpecification('id = :id', ['id' => 222]),
            new SimpleSpecification('id = :id', ['id' => 333])
        );

        self::assertEquals('(id = :id OR id = :id_1 OR id = :id_2)', $spec->getRule());
        self::assertEquals([
            'id'   => 111,
            'id_1' => 222,
            'id_2' => 333,
        ], $spec->getParameters());
    }

    #[Test]
    public function shouldSuccessAddSpecifications(): void
    {
        $composite = new CompositeSpecification('AND');

        $spec1 = new SimpleSpecification('1', []);
        $spec2 = new SimpleSpecification('2', []);

        $composite = $composite->add($spec1);

        self::assertEquals([$spec1], $composite->specifications);

        $composite = $composite->add($spec2);

        self::assertEquals([$spec1, $spec2], $composite->specifications);
    }
}
