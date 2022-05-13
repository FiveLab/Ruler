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

use FiveLab\Component\Ruler\Parser\Lexer;
use FiveLab\Component\Ruler\Parser\SyntaxException;
use PHPUnit\Framework\TestCase;

class LexerTest extends TestCase
{
    /**
     * @test
     *
     * @param string     $expression
     * @param \Throwable $expectedException
     *
     * @dataProvider provideFailExpressions
     */
    public function shouldFailTokenize(string $expression, \Throwable $expectedException): void
    {
        $this->expectException(\get_class($expectedException));
        $this->expectExceptionMessage($expectedException->getMessage());

        (new Lexer())->tokenize($expression);
    }

    /**
     * Provide fail expressions
     *
     * @return array
     */
    public function provideFailExpressions(): array
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
        ];
    }
}
