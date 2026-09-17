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

namespace FiveLab\Component\Ruler\Executor\ClickHouse;

use FiveLab\Component\Ruler\Node\BinaryNode;
use FiveLab\Component\Ruler\Node\ConstantNode;
use FiveLab\Component\Ruler\Node\NameNode;
use FiveLab\Component\Ruler\Node\Node;
use FiveLab\Component\Ruler\Node\ParameterNode;
use FiveLab\Component\Ruler\Operator\Operators;
use FiveLab\Component\Ruler\Query\ClickHouseQuery;

readonly class ClickHouseVisitor
{
    public function visit(ClickHouseQuery $target, Node $node, array $parameters, Operators $operators): string
    {
        if ($node instanceof BinaryNode) {
            $leftSide = $this->visit($target, $node->left, $parameters, $operators);
            $rightSide = $this->visit($target, $node->right, $parameters, $operators);

            $operator = $operators->get($node->operator);

            return '('.$operator($leftSide, $rightSide).')';
        }

        if ($node instanceof NameNode) {
            // ClickHouse hasn't joins and resolves the dot itself (table alias, nested column, tuple element).
            // But the escaped dot (money\.amount) is a part of the name - quote this name with backticks.
            $parts = \array_map(static function (string $part): string {
                return \str_contains($part, '.') ? '`'.$part.'`' : $part;
            }, $node->getSplittedParts());

            return \implode('.', $parts);
        }

        if ($node instanceof ParameterNode) {
            // The placeholder in format of "smi2/phpclickhouse" client (ClickHouseDB\Query\Degeneration\Bindings).
            return ':'.$node->name;
        }

        if ($node instanceof ConstantNode) {
            return (string) $node;
        }

        throw new \InvalidArgumentException(\sprintf(
            'Unknown node "%s".',
            \get_class($node)
        ));
    }
}
