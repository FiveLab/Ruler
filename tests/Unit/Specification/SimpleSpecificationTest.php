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

use FiveLab\Component\Ruler\Specification\SimpleSpecification;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SimpleSpecificationTest extends TestCase
{
    #[Test]
    public function shouldSuccessCreate(): void
    {
        $spec = new SimpleSpecification('foo = :bar', ['bar' => 'some']);

        self::assertEquals('foo = :bar', $spec->getRule());
        self::assertEquals(['bar' => 'some'], $spec->getParameters());
    }
}
