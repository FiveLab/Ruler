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

namespace FiveLab\Component\Ruler\Specification;

/**
 * All specifications should implement this interface.
 */
interface SpecificationInterface
{
    /**
     * Get the rule
     *
     * @return string
     */
    public function getRule(): string;

    /**
     * Get parameters of specification
     *
     * @return array<string, mixed>
     */
    public function getParameters(): array;
}
