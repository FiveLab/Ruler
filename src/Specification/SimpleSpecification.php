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

class SimpleSpecification implements SpecificationInterface
{
    /**
     * Constructor.
     *
     * @param string               $rule
     * @param array<string, mixed> $parameters
     */
    public function __construct(private readonly string $rule, private readonly array $parameters)
    {
    }

    public function getRule(): string
    {
        return $this->rule;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
