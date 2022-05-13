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

namespace FiveLab\Component\Ruler\Executor;

use FiveLab\Component\Ruler\Node\Node;

/**
 * All executors should implement this interface.
 */
interface ExecutorInterface
{
    /**
     * Execute node with parameters for target
     *
     * @param object               $target
     * @param Node                 $node
     * @param array<string, mixed> $parameters
     *
     * @return void
     */
    public function execute(object $target, Node $node, array $parameters): void;
}
