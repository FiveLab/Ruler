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

namespace FiveLab\Component\Ruler\Tests\Unit\Node;

use FiveLab\Component\Ruler\Node\ConstantNode;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class ConstantNodeTest extends TestCase
{
    #[Test]
    #[TestWith([true, 'true'])]
    #[TestWith([false, 'false'])]
    #[TestWith([null, 'null'])]
    #[TestWith([11, '11'])]
    #[TestWith([12.55, '12.55'])]
    #[TestWith(['bar', 'bar'])]
    public function shouldSuccessCreate(mixed $value, string $expectedString): void
    {
        $node = new ConstantNode($value);

        self::assertEquals($value, $node->value);
        self::assertEquals($expectedString, (string) $node);
    }
}
