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

namespace FiveLab\Component\Ruler\Unit\Elastica;

use Elastica\Query;
use FiveLab\Component\Ruler\Query\RawSearchQuery;
use FiveLab\Component\Ruler\Target\ElasticaTarget;
use PHPUnit\Framework\TestCase;

class ElasticaTargetTest extends TestCase
{
    /**
     * @var ElasticaTarget
     */
    private ElasticaTarget $target;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->target = new ElasticaTarget();
    }

    /**
     * @test
     *
     * @param mixed $query
     *
     * @dataProvider provideQuery
     */
    public function shouldSuccessSupports(mixed $query): void
    {
        $supports = $this->target->supports($query);

        self::assertTrue($supports);
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
     * Provide query
     *
     * @return array
     */
    public function provideQuery(): array
    {
        return [
            [new Query()],
            [new RawSearchQuery()],
        ];
    }
}
