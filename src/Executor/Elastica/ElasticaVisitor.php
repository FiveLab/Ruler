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
    private const LOGICAL_OPERATORS = ['and', 'or'];

    public function visit(Query|RawSearchQuery $target, Node $node, array $parameters, Operators $operators): array|string|int|float|bool|\Closure|null
    {
        if ($node instanceof BinaryNode) {
            $this->assertOperands($node);

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
            // array_key_exists(), not "??": a parameter passed as null must resolve to null, not "missing".
            if (!\array_key_exists($node->name, $parameters)) {
                throw new \LogicException(\sprintf(
                    'The parameter "%s" is missed. Possible parameters are "%s".',
                    $node->name,
                    \implode('", "', \array_keys($parameters))
                ));
            }

            return $this->normalizeValue($parameters[$node->name], $node->name);
        }

        if ($node instanceof ConstantNode) {
            return $node->value;
        }

        throw new \InvalidArgumentException(\sprintf(
            'Unknown node "%s".',
            \get_class($node)
        ));
    }

    private function assertOperands(BinaryNode $node): void
    {
        // Elasticsearch builds a query clause for one field, so a comparison can't be reversed or made
        // between two fields: such a rule silently builds a clause for a "field" named after the value.
        if (\in_array($node->operator, self::LOGICAL_OPERATORS, true)) {
            foreach ([$node->left, $node->right] as $side) {
                if (!$side instanceof BinaryNode) {
                    throw new \LogicException(\sprintf(
                        'The operator "%s" can combine only conditions, %s given.',
                        $node->operator,
                        self::describeNode($side)
                    ));
                }
            }

            return;
        }

        if (!$node->left instanceof NameNode) {
            throw new \LogicException(\sprintf(
                'The left side of the operator "%s" must be a field, %s given.',
                $node->operator,
                self::describeNode($node->left)
            ));
        }

        if (!$node->right instanceof ParameterNode && !$node->right instanceof ConstantNode) {
            throw new \LogicException(\sprintf(
                'The right side of the operator "%s" must be a parameter or a constant, %s given.',
                $node->operator,
                self::describeNode($node->right)
            ));
        }
    }

    private static function describeNode(Node $node): string
    {
        return match (true) {
            $node instanceof NameNode      => \sprintf('the field "%s"', $node->name),
            $node instanceof ParameterNode => \sprintf('the parameter ":%s"', $node->name),
            $node instanceof ConstantNode  => \sprintf('the constant "%s"', $node),
            $node instanceof BinaryNode    => \sprintf('the condition with the operator "%s"', $node->operator),
            default                        => \sprintf('the node "%s"', \get_class($node)),
        };
    }

    private function normalizeValue(mixed $value, string $parameterName): array|string|int|float|bool|null
    {
        // Elasticsearch accepts only plain values. A list must stay a list: a filtered array (array_filter) keeps
        // the original keys and would be encoded as a JSON object. An array with string keys is an object on
        // purpose (a terms lookup: index, id, path), so it is kept as it is.
        if (\is_array($value)) {
            $normalized = \array_map(
                fn (mixed $item): array|string|int|float|bool|null => $this->normalizeValue($item, $parameterName),
                $value
            );

            return \array_filter(\array_keys($normalized), 'is_string') ? $normalized : \array_values($normalized);
        }

        if ($value instanceof \DateTimeInterface) {
            // ISO 8601 with milliseconds: the precision of the "date" type, read by its default format.
            return $value->format(\DateTimeInterface::RFC3339_EXTENDED);
        }

        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if ($value instanceof \Stringable) {
            return (string) $value;
        }

        if (\is_object($value)) {
            throw new \LogicException(\sprintf(
                'The value of the parameter "%s" must be a scalar, a date, a backed enum, a stringable object or an array of them, "%s" given.',
                $parameterName,
                \get_class($value)
            ));
        }

        return $value;
    }
}
