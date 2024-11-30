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
 * "OR" Composite specification
 */
class OrX extends CompositeSpecification
{
    public function __construct(SpecificationInterface ...$specification)
    {
        parent::__construct('OR', ...$specification);
    }
}
