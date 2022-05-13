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

use FiveLab\Component\Ruler\Parser\Parser;
use FiveLab\Component\Ruler\Parser\SyntaxException;
use FiveLab\Component\Ruler\Parser\Token;
use FiveLab\Component\Ruler\Parser\TokenStream;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    /**
     * @var Parser
     */
    private Parser $parser;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->parser = new Parser();
    }

    /**
     * @test
     */
    public function shouldFailForInvalidToken(): void
    {
        $token = $this->createMock(Token::class);

        $token->expects(self::any())
            ->method('getType')
            ->willReturn('foo');

        $this->expectException(SyntaxException::class);
        $this->expectExceptionMessage('Unexpected token "foo" of value "" around position 0 for expression "!".');

        $this->parser->parse(new TokenStream(
            '!',
            $token
        ));
    }
}
