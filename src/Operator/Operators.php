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

namespace FiveLab\Component\Ruler\Operator;

/**
 * Operators collection.
 */
class Operators
{
    /**
     * @var array<string, array<\Closure>>
     */
    private array $operators = [];

    /**
     * Constructor.
     *
     * @param array<string, \Closure> $operators
     */
    public function __construct(array $operators)
    {
        foreach ($operators as $operatorName => $operatorHandle) {
            $this->add($operatorName, $operatorHandle);
        }
    }

    /**
     * Add operator
     *
     * @param string   $operator
     * @param \Closure $handler
     */
    public function add(string $operator, \Closure $handler): void
    {
        $operators = $this;

        $innerHandler = static function ($a, $b, $operator) use ($handler, $operators) {
            if (\is_callable($a)) {
                return $a($b, $operator, $operators);
            }

            return $handler($a, $b);
        };

        if (!\array_key_exists($operator, $this->operators)) {
            $this->operators[$operator] = [];
        }

        \array_unshift($this->operators[$operator], $innerHandler);
    }

    /**
     * Get operator
     *
     * @param string $operator
     *
     * @return \Closure
     */
    public function get(string $operator): \Closure
    {
        if (!\array_key_exists($operator, $this->operators)) {
            throw new \RuntimeException(\sprintf(
                'The operator "%s" was not found. Possible operators are "%s".',
                $operator,
                \implode('", "', \array_keys($this->operators))
            ));
        }

        $operatorHandlers = $this->operators[$operator];

        return static function ($a, $b) use ($operatorHandlers, $operator) {
            foreach ($operatorHandlers as $operatorHandler) {
                $value = $operatorHandler($a, $b, $operator);

                if (null !== $value) {
                    return $value;
                }
            }

            throw new \RuntimeException(\sprintf(
                'Any handlers for operator "%s" returns correct value.',
                $operator
            ));
        };
    }
}
