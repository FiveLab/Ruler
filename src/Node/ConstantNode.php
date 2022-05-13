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
 * Represent constant node (true/false/null/number/etc...)
 */
class ConstantNode extends Node
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * Constructor.
     *
     * @param null|int|float|string|bool $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Get the value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Implement __toString
     *
     * @return string
     */
    public function __toString(): string
    {
        if (true === $this->value) {
            return 'true';
        }

        if (false === $this->value) {
            return 'false';
        }

        if (null === $this->value) {
            return 'null';
        }

        return (string) $this->value;
    }
}
