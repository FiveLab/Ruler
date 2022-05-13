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
 * A simple specification
 */
class SimpleSpecification implements SpecificationInterface
{
    /**
     * @var string
     */
    private string $rule;

    /**
     * @var array<string, mixed>
     */
    private array $parameters;

    /**
     * Constructor.
     *
     * @param string               $rule
     * @param array<string, mixed> $parameters
     */
    public function __construct(string $rule, array $parameters)
    {
        $this->rule = $rule;
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getRule(): string
    {
        return $this->rule;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
