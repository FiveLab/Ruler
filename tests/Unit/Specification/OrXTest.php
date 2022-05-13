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

namespace FiveLab\Component\Ruler\Tests\Unit\Specification;

use FiveLab\Component\Ruler\Specification\OrX;
use FiveLab\Component\Ruler\Specification\SimpleSpecification;
use PHPUnit\Framework\TestCase;

class OrXTest extends TestCase
{
    /**
     * @test
     */
    public function shouldSuccessCreate(): void
    {
        $spec = new OrX(
            new SimpleSpecification('id = :id', ['id' => 1]),
            new SimpleSpecification('primary = :primary', ['primary' => true])
        );

        self::assertEquals('(id = :id OR primary = :primary)', $spec->getRule());
        self::assertEquals([
            'id'      => 1,
            'primary' => true,
        ], $spec->getParameters());
    }
}
