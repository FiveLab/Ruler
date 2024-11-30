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

use FiveLab\Component\Ruler\Node\NameNode;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class NameNodeTest extends TestCase
{
    #[Test]
    #[TestWith(['bar', ['bar']])]
    #[TestWith(['foo.bar', ['foo', 'bar']])]
    #[TestWith(['foo\.bar.some', ['foo.bar', 'some']])]
    #[TestWith(['foo\.bar\.some', ['foo.bar.some']])]
    public function shouldSuccessCreate(string $name, array $expectedSplittedParts): void
    {
        $node = new NameNode($name);

        self::assertEquals($expectedSplittedParts, $node->getSplittedParts());
    }
}
