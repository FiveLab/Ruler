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

namespace FiveLab\Component\Ruler\Tests\Unit\Parser;

use FiveLab\Component\Ruler\Parser\SyntaxException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SyntaxExceptionTest extends TestCase
{
    #[Test]
    public function shouldSuccessCreate(): void
    {
        $error = new SyntaxException('foo bar.', 10, 'some = 1');

        self::assertEquals('foo bar around position 10 for expression "some = 1".', $error->getMessage());
        self::assertEquals(10, $error->cursor);
        self::assertEquals('some = 1', $error->expression);
    }
}
