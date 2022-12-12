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
use FiveLab\Component\Ruler\Specification\TargetableSpecification;
use PHPUnit\Framework\TestCase;

class TargetableSpecificationTest extends TestCase
{
    /**
     * @test
     */
    public function shouldSuccessCreate(): void
    {
        $spec = new TargetableSpecification([
            'Default' => new SimpleSpecification('default', [1]),
            'FooBar'  => new SimpleSpecification('foo-bar', [2]),
        ]);

        self::assertEquals(new SimpleSpecification('default', [1]), $spec->getForTarget('Default'));
        self::assertEquals(new SimpleSpecification('foo-bar', [2]), $spec->getForTarget('FooBar'));
    }

    /**
     * @test
     */
    public function shouldSuccessGetRuleAndParameters(): void
    {
        $spec = new TargetableSpecification([
            'Default' => new SimpleSpecification('default', [1]),
            'FooBar'  => new SimpleSpecification('foo-bar', [2]),
        ]);

        self::assertEquals('default', $spec->getRule());
        self::assertEquals([1], $spec->getParameters());
    }

    /**
     * @test
     */
    public function shouldThrowErrorIfTargetNotFound(): void
    {
        $spec = new TargetableSpecification([
            'Default' => new SimpleSpecification('default', [1]),
            'FooBar'  => new SimpleSpecification('foo-bar', [2]),
        ]);

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('The target "BarFoo" is missed. Possible targets are "Default", "FooBar".');

        $spec->getForTarget('BarFoo');
    }
}
