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
 * "AND" Composite specification
 */
class AndX extends CompositeSpecification
{
    /**
     * Constructor.
     *
     * @param SpecificationInterface ...$specifications
     */
    public function __construct(SpecificationInterface ...$specifications)
    {
        parent::__construct('AND', ...$specifications);
    }
}
