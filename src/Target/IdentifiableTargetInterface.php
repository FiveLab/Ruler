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

/**
 * Implement this interface if target can have specific identifier based on any parameter.
 */
interface IdentifiableTargetInterface extends TargetInterface
{
    /**
     * Get identifier for target
     *
     * @param object $target
     *
     * @return string
     */
    public function getIdentifier(object $target): string;
}
