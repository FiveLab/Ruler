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
     */
    public function shouldSuccessSupports(): void
    {
        $supports = $this->target->supports(new Query());

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
}
