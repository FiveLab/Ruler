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
 * Represent parameter node
 */
class ParameterNode extends Node
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
     * Get the name of parameter
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Implement __toString
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
