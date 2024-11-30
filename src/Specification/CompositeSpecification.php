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
 * A composite specification.
 */
class CompositeSpecification implements SpecificationInterface
{
    public readonly string $operator;
    public readonly array $specifications;
    private ?string $processedRule = null;
    private ?array $processedParameters = null;

    public function __construct(string $operator, SpecificationInterface ...$specifications)
    {
        $this->operator = \trim($operator);
        $this->specifications = $specifications;
    }

    public function add(SpecificationInterface $specification): self
    {
        $specifications = $this->specifications;
        $specifications[] = $specification;

        return new self($this->operator, ...$specifications);
    }

    public function getRule(): string
    {
        $this->processForControlDuplicates();

        return (string) $this->processedRule;
    }

    public function getParameters(): array
    {
        $this->processForControlDuplicates();

        return (array) $this->processedParameters;
    }

    private function processForControlDuplicates(): void
    {
        if (null !== $this->processedRule) {
            return;
        }

        $processedParameters = [];
        $processedRules = [];

        $duplicatedParameters = [];

        foreach ($this->specifications as $specification) {
            $rule = $specification->getRule();
            $parameters = $specification->getParameters();

            foreach ($parameters as $parameterKey => $parameterValue) {
                if (\array_key_exists($parameterKey, $processedParameters)) {
                    // The parameter already exist in list. Add custom suffix.
                    if (!\array_key_exists($parameterKey, $duplicatedParameters)) {
                        $duplicatedParameters[$parameterKey] = 0;
                    }

                    $duplicatedParameters[$parameterKey]++;

                    $modifiedParameterKey = $parameterKey.'_'.$duplicatedParameters[$parameterKey];
                    $rule = \str_replace(':'.$parameterKey, ':'.$modifiedParameterKey, $rule);

                    $processedParameters[$modifiedParameterKey] = $parameterValue;
                } else {
                    $processedParameters[$parameterKey] = $parameterValue;
                }
            }

            $processedRules[] = $rule;
        }

        $processedRules = \array_filter($processedRules);
        $normalizedRules = \implode(' '.$this->operator.' ', $processedRules);

        $this->processedRule = $normalizedRules ? '('.$normalizedRules.')' : '';
        $this->processedParameters = $processedParameters;
    }
}
