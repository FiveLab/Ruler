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
use FiveLab\Component\Ruler\Query\RawSearchQuery;
use FiveLab\Component\Ruler\Ruler;
use FiveLab\Component\Ruler\Target\ElasticaTarget;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class ElasticaRulerTest extends TestCase
{
    private Ruler $ruler;

    protected function setUp(): void
    {
        $this->ruler = new Ruler(new ElasticaTarget());
    }

    #[Test]
    #[DataProvider('provideDataForApply')]
    public function shouldSuccessApply(string $file, object $query): void
    {
        $data = \json_decode(\file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);

        $rule = $data['rule'];
        $params = $data['params'];
        $expectedQuery = $data['query'];

        $this->ruler->apply($query, $rule, $params);

        self::assertEquals($expectedQuery, $query->toArray()['query']);
    }

    #[Test]
    #[TestWith([new Query()])]
    #[TestWith([new RawSearchQuery()])]
    public function shouldFailForMoreNested(object $query): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Only one nested level supported.');

        $this->ruler->apply($query, 'products.variants.categories.key = :key', [
            'key' => 'foo',
        ]);
    }

    #[Test]
    #[TestWith([new Query()])]
    #[TestWith([new RawSearchQuery()])]
    public function shouldFailIfParameterMissed(object $query): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The parameter "id" is missed. Possible parameters are "foo", "some".');

        $this->ruler->apply($query, 'id = :id', [
            'foo'  => 'bar',
            'some' => 1,
        ]);
    }

    public static function provideDataForApply(): array
    {
        $files = [
            __DIR__.'/Resources/eq.json',
            __DIR__.'/Resources/not-eq.json',
            __DIR__.'/Resources/in.json',
            __DIR__.'/Resources/not-in.json',
            __DIR__.'/Resources/gt.json',
            __DIR__.'/Resources/gte.json',
            __DIR__.'/Resources/lt.json',
            __DIR__.'/Resources/lte.json',
            __DIR__.'/Resources/like.json',
            __DIR__.'/Resources/and.json',
            __DIR__.'/Resources/or.json',
            __DIR__.'/Resources/nested.json',
            __DIR__.'/Resources/combined-logical.json',
        ];

        return \array_merge(
            \array_map(static function (string $file): array {
                return [$file, new Query()];
            }, $files),
            \array_map(static function (string $file): array {
                return [$file, new RawSearchQuery()];
            }, $files)
        );
    }
}
