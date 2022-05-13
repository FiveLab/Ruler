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

namespace FiveLab\Component\Ruler\Specification;

/**
 * Empty specification.
 */
final class EmptySpecification implements SpecificationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRule(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(): array
    {
        return [];
    }
}
