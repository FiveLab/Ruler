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

namespace FiveLab\Component\Ruler\Executor;

/**
 * The context of execution. Any executors can write specific data to context.
 */
class ExecutionContext
{
    /**
     * @var array<string, mixed>
     */
    private array $payload;

    /**
     * Constructor.
     *
     * @param array<string, mixed> $context
     */
    public function __construct(array $context = [])
    {
        $this->payload = $context;
    }

    public function set(string $key, mixed$data): void
    {
        $this->payload[$key] = $data;
    }

    public function add(string $key, ?string $inner, mixed $data): void
    {
        if (!\array_key_exists($key, $this->payload)) {
            throw new \RuntimeException(\sprintf(
                'Can\'t add inner element to data, "%s" missed in context.',
                $key
            ));
        }

        if (!\is_array($this->payload[$key])) {
            throw new \RuntimeException(\sprintf(
                'Can\'t add inner element to data, "%s" is not an array.',
                $key
            ));
        }

        if (null === $inner) {
            $this->payload[$key][] = $data;
        } else {
            $this->payload[$key][$inner] = $data;
        }
    }

    public function get(string $key): mixed
    {
        return $this->payload[$key] ?? throw new \RuntimeException(\sprintf(
            'The data "%s" missed in context. Possible keys are "%s".',
            $key,
            \implode('", "', \array_keys($this->payload))
        ));
    }
}
