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

namespace FiveLab\Component\Ruler\Tests\Functional\Elastica;

use Elastica\Query;
use FiveLab\Component\Ruler\Ruler;
use FiveLab\Component\Ruler\Target\ElasticaTarget;
use PHPUnit\Framework\TestCase;

class ElasticaRulerTest extends TestCase
{
    /**
     * @var Ruler
     */
    private Ruler $ruler;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->ruler = new Ruler(new ElasticaTarget());
    }

    /**
     * @test
     *
     * @param string $file
     *
     * @dataProvider provideDataForApply
     */
    public function shouldSuccessApply(string $file): void
    {
        $data = \json_decode(\file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);

        $rule = $data['rule'];
        $params = $data['params'];
        $expectedQuery = $data['query'];

        $query = new Query();

        $this->ruler->apply($query, $rule, $params);

        self::assertEquals($expectedQuery, $query->toArray()['query']);
    }

    /**
     * @test
     */
    public function shouldFailForMoreNested(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Only one nested level supported.');

        $this->ruler->apply(new Query(), 'products.variants.categories.key = :key', [
            'key' => 'foo',
        ]);
    }

    /**
     * @test
     */
    public function shouldFailIfParameterMissed(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The parameter "id" is missed. Possible parameters are "foo", "some".');

        $this->ruler->apply(new Query(), 'id = :id', [
            'foo'  => 'bar',
            'some' => 1,
        ]);
    }

    /**
     * Provide data for apply
     *
     * @return array
     */
    public function provideDataForApply(): array
    {
        return [
            [__DIR__.'/Resources/eq.json'],
            [__DIR__.'/Resources/not-eq.json'],
            [__DIR__.'/Resources/in.json'],
            [__DIR__.'/Resources/not-in.json'],
            [__DIR__.'/Resources/gt.json'],
            [__DIR__.'/Resources/gte.json'],
            [__DIR__.'/Resources/lt.json'],
            [__DIR__.'/Resources/lte.json'],
            [__DIR__.'/Resources/like.json'],
            [__DIR__.'/Resources/and.json'],
            [__DIR__.'/Resources/or.json'],
            [__DIR__.'/Resources/nested.json'],
            [__DIR__.'/Resources/combined-logical.json'],
        ];
    }
}
