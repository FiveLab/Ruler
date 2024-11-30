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

final readonly class EmptySpecification implements SpecificationInterface
{
    public function getRule(): string
    {
        return '';
    }

    public function getParameters(): array
    {
        return [];
    }
}
