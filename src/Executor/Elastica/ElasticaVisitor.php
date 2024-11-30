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

namespace FiveLab\Component\Ruler\Executor\Elastica;

use Elastica\Query;
use FiveLab\Component\Ruler\Node\BinaryNode;
use FiveLab\Component\Ruler\Node\ConstantNode;
use FiveLab\Component\Ruler\Node\NameNode;
use FiveLab\Component\Ruler\Node\Node;
use FiveLab\Component\Ruler\Node\ParameterNode;
use FiveLab\Component\Ruler\Operator\Operators;
use FiveLab\Component\Ruler\Query\RawSearchQuery;

readonly class ElasticaVisitor
{
    public function visit(Query|RawSearchQuery $target, Node $node, array $parameters, Operators $operators): array|string|int|float|bool|\Closure
    {
        if ($node instanceof BinaryNode) {
            $leftSide = $this->visit($target, $node->left, $parameters, $operators);
            $rightSide = $this->visit($target, $node->right, $parameters, $operators);

            $operator = $operators->get($node->operator);

            return $operator($leftSide, $rightSide);
        }

        if ($node instanceof NameNode) {
            $parts = $node->getSplittedParts();

            if (\count($parts) === 1) {
                return $parts[0];
            }

            if (\count($parts) > 2) {
                throw new \RuntimeException('Only one nested level supported.');
            }

            return static function ($value, string $operator, Operators $operators) use ($parts) {
                $operatorHandler = $operators->get($operator);

                $query = $operatorHandler(\implode('.', $parts), $value);

                return [
                    'nested' => [
                        'path'  => $parts[0],
                        'query' => $query,
                    ],
                ];
            };
        }

        if ($node instanceof ParameterNode) {
            return $parameters[$node->name] ?? throw new \LogicException(\sprintf(
                'The parameter "%s" is missed. Possible parameters are "%s".',
                $node->name,
                \implode('", "', \array_keys($parameters))
            ));
        }

        if ($node instanceof ConstantNode) {
            return $node->value;
        }

        throw new \InvalidArgumentException(\sprintf(
            'Unknown node "%s".',
            \get_class($node)
        ));
    }
}
