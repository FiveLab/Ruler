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

namespace FiveLab\Component\Ruler\Node;

/**
 * Represent name node or property name
 */
class NameNode extends Node
{
    /**
     * @var string
     */
    private string $name;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Get the name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get parts splitted by dot.
     *
     * @return array<string>
     */
    public function getSplittedParts(): array
    {
        $parts = \explode('.', $this->name);
        $splittedParts = [];

        $path = '';

        while ($part = \array_shift($parts)) {
            if ('\\' === \substr($part, -1)) {
                // Escape dot.
                $part = \substr($part, 0, -1);
                $path .= $path ? '.'.$part : $part;
            } else {
                $splittedParts[] = $path ? $path.'.'.$part : $part;
                $path = '';
            }
        }

        return $splittedParts;
    }
}
