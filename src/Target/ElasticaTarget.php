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

use Elastica\Query;
use FiveLab\Component\Ruler\Executor\Elastica\ElasticaExecutor;
use FiveLab\Component\Ruler\Executor\Elastica\ElasticaVisitor;
use FiveLab\Component\Ruler\Executor\ExecutorInterface;
use FiveLab\Component\Ruler\Operator\Operators;
use FiveLab\Component\Ruler\Operator\OperatorsConfigurator;

/**
 * The target for ElasticSearch based on "ruflin/elastica" package.
 */
class ElasticaTarget implements TargetInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(object $target): bool
    {
        return $target instanceof Query;
    }

    /**
     * {@inheritdoc}
     */
    public function createExecutor(object $target): ExecutorInterface
    {
        $operators = new Operators([]);

        OperatorsConfigurator::forElasticSearch()->configure($operators);

        $visitor = new ElasticaVisitor();

        return new ElasticaExecutor($visitor, $operators);
    }
}
