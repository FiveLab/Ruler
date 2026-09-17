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

namespace FiveLab\Component\Ruler\Target;

use FiveLab\Component\Ruler\Executor\ClickHouse\ClickHouseExecutor;
use FiveLab\Component\Ruler\Executor\ClickHouse\ClickHouseVisitor;
use FiveLab\Component\Ruler\Executor\ExecutorInterface;
use FiveLab\Component\Ruler\Operator\Operators;
use FiveLab\Component\Ruler\Operator\OperatorsConfigurator;
use FiveLab\Component\Ruler\Query\ClickHouseQuery;

/**
 * The target for ClickHouse.
 *
 * @implements TargetInterface<ClickHouseQuery>
 */
readonly class ClickHouseTarget implements TargetInterface
{
    public function supports(object $target): bool
    {
        return $target instanceof ClickHouseQuery;
    }

    public function createExecutor(object $target): ExecutorInterface
    {
        $operators = new Operators([]);

        OperatorsConfigurator::forSql()->configure($operators);

        $visitor = new ClickHouseVisitor();

        return new ClickHouseExecutor($visitor, $operators);
    }
}
