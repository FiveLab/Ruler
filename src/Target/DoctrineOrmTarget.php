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

use Doctrine\ORM\QueryBuilder;
use FiveLab\Component\Ruler\Executor\DoctrineOrm\DoctrineOrmExecutor;
use FiveLab\Component\Ruler\Executor\DoctrineOrm\DoctrineOrmVisitor;
use FiveLab\Component\Ruler\Executor\ExecutorInterface;
use FiveLab\Component\Ruler\Operator\Operators;
use FiveLab\Component\Ruler\Operator\OperatorsConfigurator;

/**
 * The target for Doctrine ORM.
 */
class DoctrineOrmTarget implements IdentifiableTargetInterface
{
    /**
     * {@inheritdoc}
     *
     * @param QueryBuilder $target
     */
    public function supports(object $target): bool
    {
        return $target instanceof QueryBuilder;
    }

    /**
     * {@inheritdoc}
     *
     * @param QueryBuilder $target
     */
    public function createExecutor(object $target): ExecutorInterface
    {
        $operators = new Operators([]);

        OperatorsConfigurator::forSql()->configure($operators);

        $visitor = new DoctrineOrmVisitor($target->getEntityManager());

        return new DoctrineOrmExecutor($visitor, $operators);
    }

    /**
     * {@inheritdoc}
     *
     * @param QueryBuilder $target
     */
    public function getIdentifier(object $target): string
    {
        return \spl_object_hash($target->getEntityManager());
    }
}
