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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
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
        $target->setParameters($this->makeParametersForQueryBuilder($parameters)); // @phpstan-ignore-line

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

    private function makeParametersForQueryBuilder(array $parameters): array|ArrayCollection
    {
        static $expectedCollection = null;

        if (null === $expectedCollection) {
            $methodRef = new \ReflectionMethod(QueryBuilder::class, 'setParameters');
            $argumentRef = $methodRef->getParameters()[0];

            $expectedCollection = $argumentRef->getType()?->getName() === ArrayCollection::class; // @phpstan-ignore-line
        }

        if ($expectedCollection) {
            $parameters = \array_map(static function (string $key, mixed $value): Parameter {
                return new Parameter($key, $value);
            }, \array_keys($parameters), \array_values($parameters));

            $parameters = new ArrayCollection($parameters);
        }

        return $parameters;
    }
}
