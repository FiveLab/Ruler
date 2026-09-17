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

namespace FiveLab\Component\Ruler\Query;

/**
 * The query for ClickHouse. Collects the condition and parameters for insert it into your own SQL.
 */
class ClickHouseQuery
{
    /**
     * @var array<string>
     */
    private array $conditions = [];

    /**
     * @var array<string, mixed>
     */
    private array $parameters = [];

    /**
     * @param string               $where
     * @param array<string, mixed> $parameters
     *
     * @return self
     */
    public function andWhere(string $where, array $parameters = []): self
    {
        $where = \trim($where);

        if ('' !== $where) {
            $this->conditions[] = $where;
        }

        foreach ($parameters as $name => $value) {
            if (\array_key_exists($name, $this->parameters) && $this->parameters[$name] !== $value) {
                throw new \LogicException(\sprintf(
                    'The parameter "%s" already added with another value.',
                    $name
                ));
            }

            $this->parameters[$name] = $value;
        }

        return $this;
    }

    public function getWhere(): string
    {
        if (!\count($this->conditions)) {
            return '';
        }

        if (1 === \count($this->conditions)) {
            return $this->conditions[0];
        }

        // Wrap each condition into brackets: the "OR" inside any condition must not change precedence after join via "AND".
        $conditions = \array_map(static function (string $condition): string {
            return '('.$condition.')';
        }, $this->conditions);

        return \implode(' AND ', $conditions);
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
