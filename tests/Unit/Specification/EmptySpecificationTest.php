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

use FiveLab\Component\Ruler\Specification\EmptySpecification;
use PHPUnit\Framework\TestCase;

class EmptySpecificationTest extends TestCase
{
    /**
     * @test
     */
    public function shouldSuccessCreate(): void
    {
        $spec = new EmptySpecification();

        self::assertEquals('', $spec->getRule());
        self::assertEquals([], $spec->getParameters());
    }
}
