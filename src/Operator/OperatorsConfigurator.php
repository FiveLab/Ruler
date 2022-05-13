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
 * The chain of operator configurators.
 */
class OperatorsConfigurator implements OperatorsConfiguratorInterface
{
    /**
     * @var array<OperatorsConfiguratorInterface>
     */
    private array $configurators;

    /**
     * Constructor.
     *
     * @param OperatorsConfiguratorInterface ...$configurators
     */
    public function __construct(OperatorsConfiguratorInterface ...$configurators)
    {
        $this->configurators = $configurators;
    }

    /**
     * Get configurator for SQL like
     *
     * @return OperatorsConfiguratorInterface
     */
    public static function forSql(): OperatorsConfiguratorInterface
    {
        return new self(
            new RegularOperatorsConfigurator(),
            new SqlOperatorsConfigurator()
        );
    }

    /**
     * Get configurator for ElasticSearch like
     *
     * @return OperatorsConfiguratorInterface
     */
    public static function forElasticSearch(): OperatorsConfiguratorInterface
    {
        return new self(new ElasticSearchOperatorsConfigurator());
    }

    /**
     * {@inheritdoc}
     */
    public function configure(Operators $operators): void
    {
        foreach ($this->configurators as $configurator) {
            $configurator->configure($operators);
        }
    }
}
