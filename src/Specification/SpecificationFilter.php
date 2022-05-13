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
 * A helper for filter specifications.
 */
class SpecificationFilter
{
    /**
     * Filter specification by filter
     *
     * @param SpecificationInterface $specification
     * @param \Closure               $filter
     *
     * @return array<SpecificationInterface>
     */
    public static function filter(SpecificationInterface $specification, \Closure $filter): array
    {
        if ($specification instanceof CompositeSpecification) {
            return self::doFilter($specification, $filter);
        }

        return [];
    }

    /**
     * Filter specification by instanceof
     *
     * @param SpecificationInterface $specification
     * @param string                 $class
     *
     * @return array<SpecificationInterface>
     */
    public static function filterByInstanceof(SpecificationInterface $specification, string $class): array
    {
        return self::filter($specification, static function (SpecificationInterface $specification) use ($class) {
            return $specification instanceof $class;
        });
    }

    /**
     * Self process of filter specifications
     *
     * @param CompositeSpecification $specification
     * @param \Closure               $filter
     *
     * @return array<SpecificationInterface>
     */
    private static function doFilter(CompositeSpecification $specification, \Closure $filter): array
    {
        $filteredSpecifications = [[]];

        foreach ($specification->getSpecifications() as $innerSpecification) {
            if ($innerSpecification instanceof CompositeSpecification) {
                $filteredSpecifications[] = self::doFilter($innerSpecification, $filter);
            } elseif ($filter($innerSpecification)) {
                $filteredSpecifications[] = [$innerSpecification];
            }
        }

        return \array_merge(...$filteredSpecifications);
    }
}
