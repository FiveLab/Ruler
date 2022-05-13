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

namespace FiveLab\Component\Ruler\Node;

/**
 * Represent binary node with specific operator.
 */
class BinaryNode extends Node
{
    /**
     * @var string
     */
    private string $operator;

    /**
     * @var Node
     */
    private Node $left;

    /**
     * @var Node
     */
    private Node $right;

    /**
     * Constructor.
     *
     * @param string $operator
     * @param Node   $left
     * @param Node   $right
     */
    public function __construct(string $operator, Node $left, Node $right)
    {
        $this->operator = $operator;
        $this->left = $left;
        $this->right = $right;
    }

    /**
     * Get operator
     *
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * Get left node
     *
     * @return Node
     */
    public function getLeft(): Node
    {
        return $this->left;
    }

    /**
     * Get right node
     *
     * @return Node
     */
    public function getRight(): Node
    {
        return $this->right;
    }
}
