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
    /**
     * @var string
     */
    private string $operator;

    /**
     * @var array<SpecificationInterface>
     */
    private array $specifications;

    /**
     * @var string|null
     */
    private ?string $processedRule = null;

    /**
     * @var array<string, mixed>|null
     */
    private ?array $processedParameters = null;

    /**
     * Constructor.
     *
     * @param string                 $operator
     * @param SpecificationInterface ...$specifications
     */
    public function __construct(string $operator, SpecificationInterface ...$specifications)
    {
        $this->operator = \trim($operator);
        $this->specifications = $specifications;
    }

    /**
     * Add specification to composition
     *
     * @param SpecificationInterface $specification
     *
     * @return self
     */
    public function add(SpecificationInterface $specification): self
    {
        $cloned = clone $this;
        $cloned->specifications[] = $specification;

        $cloned->processedRule = null;
        $cloned->processedParameters = null;

        return $cloned;
    }

    /**
     * Get all specifications
     *
     * @return SpecificationInterface[]
     */
    public function getSpecifications(): array
    {
        return $this->specifications;
    }

    /**
     * Get operator
     *
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * {@inheritdoc}
     */
    public function getRule(): string
    {
        $this->processForControlDuplicates();

        return (string) $this->processedRule;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(): array
    {
        $this->processForControlDuplicates();

        return (array) $this->processedParameters;
    }

    /**
     * Process rule and parameters for control duplicates.
     */
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
