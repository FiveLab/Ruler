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

namespace FiveLab\Component\Ruler\Tests\Unit\Query;

use FiveLab\Component\Ruler\Query\ClickHouseQuery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ClickHouseQueryTest extends TestCase
{
    private ClickHouseQuery $query;

    protected function setUp(): void
    {
        $this->query = new ClickHouseQuery();
    }

    #[Test]
    public function shouldSuccessCreate(): void
    {
        self::assertEquals('', $this->query->getWhere());
        self::assertEquals([], $this->query->getParameters());
    }

    #[Test]
    public function shouldSuccessAddCondition(): void
    {
        $this->query->andWhere('(shop = :shop)', ['shop' => 'foo']);

        self::assertEquals('(shop = :shop)', $this->query->getWhere());
        self::assertEquals(['shop' => 'foo'], $this->query->getParameters());
    }

    #[Test]
    public function shouldSuccessAddManyConditions(): void
    {
        $this->query->andWhere('shop = :shop', ['shop' => 'foo']);
        $this->query->andWhere('amount > :amount OR success = true', ['amount' => 100]);

        self::assertEquals('(shop = :shop) AND (amount > :amount OR success = true)', $this->query->getWhere());

        self::assertEquals([
            'shop'   => 'foo',
            'amount' => 100,
        ], $this->query->getParameters());
    }

    #[Test]
    public function shouldSuccessSkipEmptyCondition(): void
    {
        $this->query->andWhere('shop = :shop', ['shop' => 'foo']);
        $this->query->andWhere('   ', ['amount' => 100]);

        self::assertEquals('shop = :shop', $this->query->getWhere());

        self::assertEquals([
            'shop'   => 'foo',
            'amount' => 100,
        ], $this->query->getParameters());
    }

    #[Test]
    public function shouldSuccessAddSameParameterWithSameValue(): void
    {
        $this->query->andWhere('shop = :shop', ['shop' => ['foo', 'bar']]);
        $this->query->andWhere('another_shop = :shop', ['shop' => ['foo', 'bar']]);

        self::assertEquals('(shop = :shop) AND (another_shop = :shop)', $this->query->getWhere());
        self::assertEquals(['shop' => ['foo', 'bar']], $this->query->getParameters());
    }

    #[Test]
    public function shouldFailAddSameParameterWithAnotherValue(): void
    {
        $this->query->andWhere('shop = :shop', ['shop' => 'foo']);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The parameter "shop" already added with another value.');

        $this->query->andWhere('another_shop = :shop', ['shop' => 'bar']);
    }
}
