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

namespace FiveLab\Component\Ruler;

use FiveLab\Component\Ruler\Specification\SpecificationInterface;

/**
 * All rulers should implement this interface.
 */
interface RulerInterface
{
    /**
     * Apply rule to target
     *
     * @param object               $target
     * @param string               $rule
     * @param array<string, mixed> $parameters
     */
    public function apply(object $target, string $rule, array $parameters): void;

    /**
     * Apply specification
     *
     * @param object                 $target
     * @param SpecificationInterface $specification
     */
    public function applySpec(object $target, SpecificationInterface $specification): void;
}
