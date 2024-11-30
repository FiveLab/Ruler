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

use FiveLab\Component\Ruler\Parser\Token;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{
    #[Test]
    public function shouldSuccessCreate(): void
    {
        $token = new Token(Token::TYPE_NUMBER, 12, '333');

        self::assertEquals(Token::TYPE_NUMBER, $token->type);
        self::assertEquals(12, $token->cursor);
        self::assertEquals('333', $token->value);
    }

    #[Test]
    public function shouldThrowErrorForInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid token type "BAR". Possible types are "eof", "punctuation", "operator", "property", "parameter", "number".');

        new Token('BAR', 0, '');
    }
}
