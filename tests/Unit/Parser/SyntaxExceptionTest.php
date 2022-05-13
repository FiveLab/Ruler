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

namespace FiveLab\Component\Ruler\Tests\Parser;

use FiveLab\Component\Ruler\Parser\SyntaxException;
use PHPUnit\Framework\TestCase;

class SyntaxExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldSuccessCreate(): void
    {
        $error = new SyntaxException('foo bar.', 10, 'some = 1');

        self::assertEquals('foo bar around position 10 for expression "some = 1".', $error->getMessage());
        self::assertEquals(10, $error->getCursor());
        self::assertEquals('some = 1', $error->getExpression());
    }
}
