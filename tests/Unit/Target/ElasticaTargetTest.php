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

use Elastica\Query;
use FiveLab\Component\Ruler\Query\RawSearchQuery;
use FiveLab\Component\Ruler\Target\ElasticaTarget;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class ElasticaTargetTest extends TestCase
{
    private ElasticaTarget $target;

    protected function setUp(): void
    {
        $this->target = new ElasticaTarget();
    }

    #[Test]
    #[TestWith([new Query()])]
    #[TestWith([new RawSearchQuery()])]
    public function shouldSuccessSupports(object $query): void
    {
        $supports = $this->target->supports($query);

        self::assertTrue($supports);
    }

    #[Test]
    public function shouldNotSupports(): void
    {
        $supports = $this->target->supports(new \stdClass());

        self::assertFalse($supports);
    }
}
