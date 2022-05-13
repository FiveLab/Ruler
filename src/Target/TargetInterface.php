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

use FiveLab\Component\Ruler\Executor\ExecutorInterface;

/**
 * All targets should implement this interface.
 */
interface TargetInterface
{
    /**
     * Is target supported?
     *
     * @param object $target
     *
     * @return bool
     */
    public function supports(object $target): bool;

    /**
     * Create executor for target
     *
     * @param object $target
     *
     * @return ExecutorInterface
     */
    public function createExecutor(object $target): ExecutorInterface;
}
