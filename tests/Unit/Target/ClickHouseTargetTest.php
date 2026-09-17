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

use FiveLab\Component\Ruler\Executor\ClickHouse\ClickHouseExecutor;
use FiveLab\Component\Ruler\Query\ClickHouseQuery;
use FiveLab\Component\Ruler\Target\ClickHouseTarget;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ClickHouseTargetTest extends TestCase
{
    private ClickHouseTarget $target;

    protected function setUp(): void
    {
        $this->target = new ClickHouseTarget();
    }

    #[Test]
    public function shouldSuccessSupports(): void
    {
        $supports = $this->target->supports(new ClickHouseQuery());

        self::assertTrue($supports);
    }

    #[Test]
    public function shouldNotSupports(): void
    {
        $supports = $this->target->supports(new \stdClass());

        self::assertFalse($supports);
    }

    #[Test]
    public function shouldSuccessCreateExecutor(): void
    {
        $executor = $this->target->createExecutor(new ClickHouseQuery());

        self::assertInstanceOf(ClickHouseExecutor::class, $executor);
    }
}
