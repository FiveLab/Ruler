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

namespace FiveLab\Component\Ruler\Operator;

/**
 * All operator configurators should implement this interface.
 */
interface OperatorsConfiguratorInterface
{
    /**
     * Configure operators
     *
     * @param Operators $operators
     */
    public function configure(Operators $operators): void;
}
