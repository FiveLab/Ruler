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
readonly class NameNode extends Node
{
    public function __construct(public string $name)
    {
    }

    /**
     * Get parts splitted by dot.
     *
     * @return array<string>
     */
    public function getSplittedParts(): array
    {
        $parts = \explode('.', $this->name);
        $lastIndex = \count($parts) - 1;
        $splittedParts = [];

        $path = null;

        // Walk over all parts: a "0" or an empty part must not stop the loop and drop the rest of the path.
        foreach ($parts as $index => $part) {
            if ($index < $lastIndex && \str_ends_with($part, '\\')) {
                // Escape dot (a backslash at the very end has no dot to escape).
                $part = \substr($part, 0, -1);
                $path = null === $path ? $part : $path.'.'.$part;
            } else {
                $splittedParts[] = null === $path ? $part : $path.'.'.$part;
                $path = null;
            }
        }

        return $splittedParts;
    }
}
