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

namespace FiveLab\Component\Ruler\Tests\Functional\ClickHouse;

use FiveLab\Component\Ruler\Query\ClickHouseQuery;
use FiveLab\Component\Ruler\Ruler;
use FiveLab\Component\Ruler\Specification\AndX;
use FiveLab\Component\Ruler\Specification\EmptySpecification;
use FiveLab\Component\Ruler\Specification\SimpleSpecification;
use FiveLab\Component\Ruler\Specification\SpecificationFilter;
use FiveLab\Component\Ruler\Specification\TargetableSpecification;
use FiveLab\Component\Ruler\Target\ClickHouseTarget;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ClickHouseRulerTest extends TestCase
{
    private const TARGET_CLICKHOUSE = 'ClickHouse';

    private Ruler $ruler;

    protected function setUp(): void
    {
        $this->ruler = new Ruler(new ClickHouseTarget());
    }

    #[Test]
    #[DataProvider('provideDataForApply')]
    public function shouldSuccessApply(string $rule, array $params, string $expectedWhere): void
    {
        $query = new ClickHouseQuery();

        $this->ruler->apply($query, $rule, $params);

        self::assertEquals($expectedWhere, $query->getWhere());
        self::assertEquals($params, $query->getParameters());
    }

    #[Test]
    public function shouldSuccessApplyEmptySpecification(): void
    {
        $query = new ClickHouseQuery();

        $this->ruler->applySpec($query, new EmptySpecification());

        self::assertEquals('', $query->getWhere());
        self::assertEquals([], $query->getParameters());
    }

    #[Test]
    public function shouldSuccessApplyTargetableSpecification(): void
    {
        $specification = new AndX(
            new TargetableSpecification([
                TargetableSpecification::TARGET_DEFAULT => new SimpleSpecification('shop = :shop', ['shop' => 'foo']),
                self::TARGET_CLICKHOUSE                 => new SimpleSpecification('customer_shop = :shop', ['shop' => 'foo']),
            ]),
            new TargetableSpecification([
                TargetableSpecification::TARGET_DEFAULT => new SimpleSpecification('payWay.id IN (:pay_way_ids)', ['pay_way_ids' => ['9d46f5ca', '910bcda9']]),
                self::TARGET_CLICKHOUSE                 => new SimpleSpecification('transaction_pay_way_id IN (:pay_way_ids)', ['pay_way_ids' => ['9d46f5ca', '910bcda9']]),
            ])
        );

        $specification = SpecificationFilter::filterByTarget($specification, self::TARGET_CLICKHOUSE, false);

        $query = new ClickHouseQuery();

        $this->ruler->applySpec($query, $specification);

        self::assertEquals('((customer_shop = :shop) AND (transaction_pay_way_id IN (:pay_way_ids)))', $query->getWhere());

        self::assertEquals([
            'shop'        => 'foo',
            'pay_way_ids' => ['9d46f5ca', '910bcda9'],
        ], $query->getParameters());
    }

    #[Test]
    public function shouldSuccessApplySpecificationTwice(): void
    {
        $query = new ClickHouseQuery();

        $this->ruler->applySpec($query, new SimpleSpecification('customer_shop = :shop', ['shop' => 'foo']));
        $this->ruler->applySpec($query, new SimpleSpecification('amount > :amount', ['amount' => 100]));

        self::assertEquals('((customer_shop = :shop)) AND ((amount > :amount))', $query->getWhere());

        self::assertEquals([
            'shop'   => 'foo',
            'amount' => 100,
        ], $query->getParameters());
    }

    #[Test]
    public function shouldFailApplySpecificationTwiceWithDifferentValueForSameParameter(): void
    {
        $query = new ClickHouseQuery();

        $this->ruler->applySpec($query, new SimpleSpecification('customer_shop = :shop', ['shop' => 'foo']));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The parameter "shop" already added with another value.');

        $this->ruler->applySpec($query, new SimpleSpecification('customer_shop != :shop', ['shop' => 'bar']));
    }

    public static function provideDataForApply(): array
    {
        return [
            'eq' => [
                'id = :id',
                ['id' => 123],
                '(id = :id)',
            ],

            'not eq' => [
                'id != :id',
                ['id' => '321'],
                '(id != :id)',
            ],

            'eq null' => [
                'customer_shop = null',
                [],
                '(customer_shop IS NULL)',
            ],

            'not eq null' => [
                'customer_shop != null',
                [],
                '(customer_shop IS NOT NULL)',
            ],

            'gt' => [
                'amount > :amount',
                ['amount' => 150],
                '(amount > :amount)',
            ],

            'gte' => [
                'amount >= :amount',
                ['amount' => 200],
                '(amount >= :amount)',
            ],

            'lt' => [
                'amount < :amount',
                ['amount' => 100],
                '(amount < :amount)',
            ],

            'lte' => [
                'amount <= :amount',
                ['amount' => 200],
                '(amount <= :amount)',
            ],

            'in' => [
                'transaction_pay_way_id IN (:pay_way_ids)',
                ['pay_way_ids' => ['9d46f5ca', '910bcda9']],
                '(transaction_pay_way_id IN (:pay_way_ids))',
            ],

            'not in' => [
                'transaction_pay_way_id NOT IN (:pay_way_ids)',
                ['pay_way_ids' => ['9d46f5ca', '910bcda9']],
                '(transaction_pay_way_id NOT IN (:pay_way_ids))',
            ],

            'like' => [
                'customer_shop like :shop',
                ['shop' => '%foo%'],
                '(customer_shop LIKE :shop)',
            ],

            'and' => [
                'customer_shop = :shop AND amount > :amount',
                ['shop' => 'foo', 'amount' => 100],
                '((customer_shop = :shop) AND (amount > :amount))',
            ],

            'or' => [
                'customer_shop = :shop OR customer_shop = :another_shop',
                ['shop' => 'foo', 'another_shop' => 'bar'],
                '((customer_shop = :shop) OR (customer_shop = :another_shop))',
            ],

            'combined logical' => [
                '(customer_shop = :shop OR customer_shop = :another_shop) AND amount > :amount',
                ['shop' => 'foo', 'another_shop' => 'bar', 'amount' => 100],
                '(((customer_shop = :shop) OR (customer_shop = :another_shop)) AND (amount > :amount))',
            ],

            'constants' => [
                'amount > 100 AND success = true',
                [],
                '((amount > 100) AND (success = true))',
            ],

            'name with dot' => [
                'customer.shop = :shop',
                ['shop' => 'foo'],
                '(customer.shop = :shop)',
            ],

            'name with escaped dot' => [
                'money\.amount > :amount',
                ['amount' => 100],
                '(`money.amount` > :amount)',
            ],
        ];
    }
}
