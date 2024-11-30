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

namespace FiveLab\Component\Ruler\Target;

use FiveLab\Component\Ruler\Executor\ExecutorInterface;

/**
 * A collection of all possible targets.
 */
class Targets implements TargetInterface
{
    /**
     * @var array<TargetInterface>
     */
    private readonly array $targets;

    /**
     * @var array<ExecutorInterface<object>>
     */
    private array $executors = [];

    public function __construct(TargetInterface ...$targets)
    {
        $this->targets = $targets;
    }

    public function supports(object $target): bool
    {
        foreach ($this->targets as $targetFactory) {
            if ($targetFactory->supports($target)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @return ExecutorInterface<object>
     */
    public function createExecutor(object $target): ExecutorInterface
    {
        foreach ($this->targets as $targetFactory) {
            if ($targetFactory->supports($target)) {
                $targetIdentifier = \get_class($target);

                if ($targetFactory instanceof IdentifiableTargetInterface) {
                    $targetIdentifier .= '::'.$targetFactory->getIdentifier($target);
                }

                if (\array_key_exists($targetIdentifier, $this->executors)) {
                    return $this->executors[$targetIdentifier];
                }

                $this->executors[$targetIdentifier] = $targetFactory->createExecutor($target);

                return $this->executors[$targetIdentifier];
            }
        }

        throw new \RuntimeException(\sprintf(
            'Any target support "%s".',
            \get_class($target)
        ));
    }
}
