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

namespace FiveLab\Component\Ruler\Executor\DoctrineOrm;

use Doctrine\ORM\QueryBuilder;
use FiveLab\Component\Ruler\Executor\ExecutionContext;
use FiveLab\Component\Ruler\Executor\ExecutorInterface;
use FiveLab\Component\Ruler\Node\Node;
use FiveLab\Component\Ruler\Operator\Operators;

/**
 * @implements ExecutorInterface<QueryBuilder>
 */
readonly class DoctrineOrmExecutor implements ExecutorInterface
{
    public function __construct(private DoctrineOrmVisitor $visitor, private Operators $operators)
    {
    }

    public function execute(object $target, Node $node, array $parameters): void
    {
        $context = new ExecutionContext([
            'joins'     => [],
            'rootAlias' => $target->getRootAliases()[0],
        ]);

        $rule = $this->visitor->visit($target, $node, $parameters, $this->operators, $context);

        $target->andWhere($rule);

        foreach ($parameters as $name => $value) {
            // setParameter() keeps parameters already set on the query builder; setParameters() would replace them.
            $target->setParameter($name, $value);
        }

        $joins = $context->get('joins');
        $addedJoins = [];

        foreach ($joins as $join) {
            $joinKey = $join['join'].$join['alias'];

            if (\in_array($joinKey, $addedJoins, true)) {
                // JOIN already exist.
                continue;
            }

            $target->leftJoin($join['join'], $join['alias']);

            $addedJoins[] = $joinKey;
        }
    }
}
