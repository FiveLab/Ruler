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
 * Targetable specification for possible use one top-level specification for many targets.
 */
readonly class TargetableSpecification implements SpecificationInterface
{
    public const TARGET_DEFAULT = 'Default';

    /**
     * Constructor.
     *
     * @param array<string, SpecificationInterface> $targets
     */
    public function __construct(private array $targets)
    {
    }

    public function getForTarget(string $target): SpecificationInterface
    {
        if (!\array_key_exists($target, $this->targets)) {
            throw new \UnexpectedValueException(\sprintf(
                'The target "%s" is missed. Possible targets are "%s".',
                $target,
                \implode('", "', \array_keys($this->targets))
            ));
        }

        return $this->targets[$target];
    }

    public function getRule(): string
    {
        $specification = SpecificationFilter::filterByTarget($this->getForTarget(self::TARGET_DEFAULT), self::TARGET_DEFAULT, true);

        return $specification->getRule();
    }

    public function getParameters(): array
    {
        $specification = SpecificationFilter::filterByTarget($this->getForTarget(self::TARGET_DEFAULT), self::TARGET_DEFAULT, true);

        return $specification->getParameters();
    }
}
