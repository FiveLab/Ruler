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

use FiveLab\Component\Ruler\Specification\AndX;
use FiveLab\Component\Ruler\Specification\CompositeSpecification;
use FiveLab\Component\Ruler\Specification\EmptySpecification;
use FiveLab\Component\Ruler\Specification\OrX;
use FiveLab\Component\Ruler\Specification\SimpleSpecification;
use FiveLab\Component\Ruler\Specification\SpecificationFilter;
use FiveLab\Component\Ruler\Specification\SpecificationInterface;
use FiveLab\Component\Ruler\Specification\TargetableSpecification;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SpecificationFilterTest extends TestCase
{
    #[Test]
    public function shouldSuccessFilter(): void
    {
        $composite = new CompositeSpecification(
            'OR',
            new SimpleSpecification('1', []),
            new SimpleSpecification('2', []),
            new SimpleSpecification('3', []),
            new CompositeSpecification(
                'AND',
                new SimpleSpecification('4', []),
                new SimpleSpecification('5', []),
            )
        );

        $filtered = SpecificationFilter::filter($composite, static function (SpecificationInterface $specification) {
            return \in_array($specification->getRule(), ['1', '2', '5'], true);
        });

        self::assertEquals([
            new SimpleSpecification('1', []),
            new SimpleSpecification('2', []),
            new SimpleSpecification('5', []),
        ], $filtered);
    }

    #[Test]
    public function shouldSuccessFilterForNonComposite(): void
    {
        $spec = new SimpleSpecification('1', []);

        $filtered = SpecificationFilter::filter($spec, static function () {
            return true;
        });

        self::assertEquals([], $filtered);
    }

    #[Test]
    public function shouldSuccessFilterTargetableSpecification(): void
    {
        $specification = new AndX(
            new TargetableSpecification([
                'Default'       => new SimpleSpecification('items.available = :available', ['available' => true]),
                'ElasticSearch' => new SimpleSpecification('itemsAvailable = :available_items', ['available_items' => true]),
            ]),
            new SimpleSpecification('price >= :min_price', ['min_price' => 100]),
            new TargetableSpecification([
                'Default'       => new OrX(
                    new SimpleSpecification('status = :status_pending', ['status_pending' => 'Pending']),
                    new SimpleSpecification('status = :status_success', ['status_success' => 'Success']),
                ),
                'ElasticSearch' => new OrX(
                    new SimpleSpecification('extra\.status = :status_pending', ['status_pending' => 'P']),
                    new SimpleSpecification('extra\.status = :status_success', ['status_success' => 'S']),
                ),
            ])
        );

        // Check Default target
        $defaultSpec = SpecificationFilter::filterByTarget($specification, 'Default');

        self::assertEquals('(items.available = :available AND price >= :min_price AND (status = :status_pending OR status = :status_success))', $defaultSpec->getRule());

        self::assertEquals([
            'available'      => true,
            'min_price'      => 100,
            'status_pending' => 'Pending',
            'status_success' => 'Success',
        ], $defaultSpec->getParameters());

        // Check ElasticSearch target
        $esSpec = SpecificationFilter::filterByTarget($specification, 'ElasticSearch');

        self::assertEquals('(itemsAvailable = :available_items AND price >= :min_price AND (extra\.status = :status_pending OR extra\.status = :status_success))', $esSpec->getRule());

        self::assertEquals([
            'available_items' => true,
            'min_price'       => 100,
            'status_pending'  => 'P',
            'status_success'  => 'S',
        ], $esSpec->getParameters());
    }

    #[Test]
    public function shouldSuccessFilterByTargetIfMissedForDefault(): void
    {
        $specification = new AndX(
            new TargetableSpecification([
                'Default'       => new SimpleSpecification('items.available = :available', ['available' => true]),
                'ElasticSearch' => new SimpleSpecification('itemsAvailable = :available_items', ['available_items' => true]),
            ]),
            new SimpleSpecification('price >= :min_price', ['min_price' => 100]),
        );

        $defaultSpec = SpecificationFilter::filterByTarget($specification, 'Default', false);

        self::assertEquals('(items.available = :available AND price >= :min_price)', $defaultSpec->getRule());

        self::assertEquals([
            'available' => true,
            'min_price' => 100,
        ], $defaultSpec->getParameters());
    }

    #[Test]
    public function shouldFailFilterByTargetIfMissed(): void
    {
        $specification = new AndX(
            new TargetableSpecification([
                'Default'       => new SimpleSpecification('items.available = :available', ['available' => true]),
                'ElasticSearch' => new SimpleSpecification('itemsAvailable = :available_items', ['available_items' => true]),
            ]),
            new SimpleSpecification('price >= :min_price', ['min_price' => 100]),
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Found no-targetable specification "FiveLab\Component\Ruler\Specification\SimpleSpecification" with rule "price >= :min_price"');

        SpecificationFilter::filterByTarget($specification, 'ElasticSearch', false);
    }

    #[Test]
    public function shouldSuccessFilterByInstanceOf(): void
    {
        $spec = new CompositeSpecification(
            'FOO',
            new AndX(
                new SimpleSpecification('1', []),
            ),
            new OrX(
                new SimpleSpecification('2', []),
            ),
            new EmptySpecification()
        );

        $filtered = SpecificationFilter::filterByInstanceof($spec, EmptySpecification::class);

        self::assertEquals([
            new EmptySpecification(),
        ], $filtered);
    }
}
