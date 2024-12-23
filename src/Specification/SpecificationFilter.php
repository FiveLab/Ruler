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
readonly final class SpecificationFilter
{
    public static function filter(SpecificationInterface $specification, \Closure $filter): array
    {
        if ($specification instanceof CompositeSpecification) {
            return self::doFilter($specification, $filter);
        }

        return [];
    }

    public static function filterByTarget(SpecificationInterface $specification, string $target, bool $useSimpleAsFallback = true): SpecificationInterface
    {
        if ($specification instanceof CompositeSpecification) {
            $filteredSpecifications = [];

            foreach ($specification->specifications as $innerSpecification) {
                $filteredSpecifications[] = self::filterByTarget($innerSpecification, $target, $useSimpleAsFallback);
            }

            return new CompositeSpecification($specification->operator, ...$filteredSpecifications);
        }

        if ($specification instanceof TargetableSpecification) {
            $targetableSpecification = $specification->getForTarget($target);

            if ($targetableSpecification instanceof CompositeSpecification) {
                return self::filterByTarget($targetableSpecification, $target, $useSimpleAsFallback);
            }

            return $targetableSpecification;
        }

        if ($useSimpleAsFallback || TargetableSpecification::TARGET_DEFAULT === $target) {
            return $specification;
        }

        throw new \RuntimeException(\sprintf(
            'Found no-targetable specification "%s" with rule "%s".',
            \get_class($specification),
            $specification->getRule()
        ));
    }

    public static function filterByInstanceof(SpecificationInterface $specification, string $class): array
    {
        return self::filter($specification, static function (SpecificationInterface $specification) use ($class) {
            return $specification instanceof $class;
        });
    }

    private static function doFilter(CompositeSpecification $specification, \Closure $filter): array
    {
        $filteredSpecifications = [[]];

        foreach ($specification->specifications as $innerSpecification) {
            if ($innerSpecification instanceof CompositeSpecification) {
                $filteredSpecifications[] = self::doFilter($innerSpecification, $filter);
            } elseif ($filter($innerSpecification)) {
                $filteredSpecifications[] = [$innerSpecification];
            }
        }

        return \array_merge(...$filteredSpecifications);
    }
}
