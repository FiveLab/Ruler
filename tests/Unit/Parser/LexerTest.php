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

use FiveLab\Component\Ruler\Parser\Lexer;
use FiveLab\Component\Ruler\Parser\SyntaxException;
use FiveLab\Component\Ruler\Parser\Token;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class LexerTest extends TestCase
{
    #[Test]
    #[DataProvider('provideFailExpressions')]
    public function shouldFailTokenize(string $expression, \Throwable $expectedException): void
    {
        $this->expectException(\get_class($expectedException));
        $this->expectExceptionMessage($expectedException->getMessage());

        (new Lexer())->tokenize($expression);
    }

    public static function provideFailExpressions(): array
    {
        return [
            [
                'foo = 1 and )',
                new SyntaxException('Unexpected ")".', 12, 'foo = 1 and )'),
            ],

            [
                '(foo = 2',
                new SyntaxException('Unclosed "(".', 0, '(foo = 2'),
            ],

            [
                'bar = 1 ! and foo = 2',
                new SyntaxException('Unexpected charset "!"', 8, 'bar = 1 ! and foo = 2'),
            ],

            [
                'amount = .5',
                new SyntaxException('Unexpected charset "."', 9, 'amount = .5'),
            ],

            [
                'foo..bar = 1',
                new SyntaxException('Unexpected charset "."', 3, 'foo..bar = 1'),
            ],

            [
                'foo. = 1',
                new SyntaxException('Unexpected charset "."', 3, 'foo. = 1'),
            ],

            [
                'foo.bar\ = 1',
                new SyntaxException('Unexpected charset "\"', 7, 'foo.bar\ = 1'),
            ],

            [
                'foo\bar = 1',
                new SyntaxException('Unexpected charset "\"', 3, 'foo\bar = 1'),
            ],
        ];
    }

    #[Test]
    #[TestWith(['foo'])]
    #[TestWith(['foo.bar_1'])]
    #[TestWith(['items.0.price'])]
    #[TestWith(['money\.amount'])]
    #[TestWith(['order.money\.amount'])]
    public function shouldSuccessTokenizeProperty(string $property): void
    {
        $stream = (new Lexer())->tokenize($property.' = 1');

        self::assertEquals(new Token(Token::TYPE_PROPERTY, 1, $property), $stream->current());
    }
}
