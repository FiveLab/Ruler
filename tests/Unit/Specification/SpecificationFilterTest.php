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

use FiveLab\Component\Ruler\Specification\AndX;
use FiveLab\Component\Ruler\Specification\CompositeSpecification;
use FiveLab\Component\Ruler\Specification\EmptySpecification;
use FiveLab\Component\Ruler\Specification\OrX;
use FiveLab\Component\Ruler\Specification\SimpleSpecification;
use FiveLab\Component\Ruler\Specification\SpecificationFilter;
use FiveLab\Component\Ruler\Specification\SpecificationInterface;
use PHPUnit\Framework\TestCase;

class SpecificationFilterTest extends TestCase
{
    /**
     * @test
     */
    public function shouldSuccessFilter(): void
    {
        $composite = new CompositeSpecification(
            'OR',
            new SimpleSpecification('1', []),
            new SimpleSpecification('2', []),
            new SimpleSpecification('3', []),
            new CompositeSpecification(
                'AND',
                new SimpleSpecification('4', []),
                new SimpleSpecification('5', []),
            )
        );

        $filtered = SpecificationFilter::filter($composite, static function (SpecificationInterface $specification) {
            return \in_array($specification->getRule(), ['1', '2', '5'], true);
        });

        self::assertEquals([
            new SimpleSpecification('1', []),
            new SimpleSpecification('2', []),
            new SimpleSpecification('5', []),
        ], $filtered);
    }

    /**
     * @test
     */
    public function shouldSuccessFilterForNonComposite(): void
    {
        $spec = new SimpleSpecification('1', []);

        $filtered = SpecificationFilter::filter($spec, static function () {
            return true;
        });

        self::assertEquals([], $filtered);
    }

    /**
     * @test
     */
    public function shouldSuccessFilterByInstanceOf(): void
    {
        $spec = new CompositeSpecification(
            'FOO',
            new AndX(
                new SimpleSpecification('1', []),
            ),
            new OrX(
                new SimpleSpecification('2', []),
            ),
            new EmptySpecification()
        );

        $filtered = SpecificationFilter::filterByInstanceof($spec, EmptySpecification::class);

        self::assertEquals([
            new EmptySpecification(),
        ], $filtered);
    }
}
