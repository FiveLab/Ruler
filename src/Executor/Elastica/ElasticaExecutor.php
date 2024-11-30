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

namespace FiveLab\Component\Ruler\Executor\Elastica;

use Elastica\Query;
use FiveLab\Component\Ruler\Executor\ExecutorInterface;
use FiveLab\Component\Ruler\Node\Node;
use FiveLab\Component\Ruler\Operator\Operators;
use FiveLab\Component\Ruler\Query\RawSearchQuery;

/**
 * @implements ExecutorInterface<Query|RawSearchQuery>
 */
readonly class ElasticaExecutor implements ExecutorInterface
{
    public function __construct(private ElasticaVisitor $visitor, private Operators $operators)
    {
    }

    public function execute(object $target, Node $node, array $parameters): void
    {
        /** @var array<string, mixed> $query */
        $query = $this->visitor->visit($target, $node, $parameters, $this->operators);

        $target->setRawQuery([
            'query' => $query,
        ]);
    }
}
