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
readonly class BinaryNode extends Node
{
    public function __construct(public string $operator, public Node $left, public Node $right)
    {
    }
}
