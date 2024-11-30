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
use FiveLab\Component\Ruler\Parser\Token;
use FiveLab\Component\Ruler\Parser\TokenStream;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TokenStreamTest extends TestCase
{
    #[Test]
    public function shouldThrowOnNextIfMissed(): void
    {
        $stream = new TokenStream('', new Token(Token::TYPE_EOF, 0, ''));

        $this->expectException(SyntaxException::class);
        $this->expectExceptionMessage('Unexpected end of expression around position 0.');

        $stream->next();
    }
}
