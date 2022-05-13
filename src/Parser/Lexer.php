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

namespace FiveLab\Component\Ruler\Parser;

/**
 * A lexer for tokenize expression string.
 */
class Lexer
{
    /**
     * Tokenize expression and get token stream
     *
     * @param string $expression
     *
     * @return TokenStream
     *
     * @throws SyntaxException
     */
    public function tokenize(string $expression): TokenStream
    {
        $precedencePatterns = [];

        foreach (Parser::PRECEDENCES as $operator => $precedence) {
            $precedencePatterns[] = \preg_quote($operator, '/');
        }

        $precedencePattern = \implode('|', $precedencePatterns);

        $expression = str_replace(["\r", "\n", "\t", "\v", "\f"], ' ', $expression);
        $cursor = 0;
        $tokens = [];
        $brackets = [];
        $end = \strlen($expression);

        while ($cursor < $end) {
            if (' ' === $expression[$cursor]) {
                $cursor++;

                continue;
            }

            if (\preg_match('/\d+(\.\d+)?/A', $expression, $match, 0, $cursor)) {
                // Numbers
                $number = (float) $match[0];

                if (preg_match('/^\d+$/', $match[0]) && $number <= \PHP_INT_MAX) {
                    $number = (int) $match[0]; // integers lower than the maximum
                }

                $tokens[] = new Token(Token::TYPE_NUMBER, $cursor + 1, $number);
                $cursor += \strlen($match[0]);
            } elseif ('(' === $expression[$cursor]) {
                // Opening bracket
                $brackets[] = [$expression[$cursor], $cursor];

                $tokens[] = new Token(Token::TYPE_PUNCTUATION, $cursor + 1, $expression[$cursor]);
                $cursor++;
            } elseif (')' === $expression[$cursor]) {
                // Closing bracket
                if (!\count($brackets)) {
                    throw new SyntaxException(\sprintf('Unexpected "%s".', $expression[$cursor]), $cursor, $expression);
                }

                \array_pop($brackets);

                $tokens[] = new Token(Token::TYPE_PUNCTUATION, $cursor + 1, $expression[$cursor]);
                $cursor++;
            } elseif (\preg_match('/('.$precedencePattern.')\s+/Ai', $expression, $match, 0, $cursor)) {
                // Operator
                $tokens[] = new Token(Token::TYPE_OPERATOR, $cursor + 1, \strtolower($match[1]));
                $cursor += \strlen($match[0]);
            } elseif (\preg_match('/([a-z_\.\d\\\]+)/Ai', $expression, $match, 0, $cursor)) {
                // Property
                $tokens[] = new Token(Token::TYPE_PROPERTY, $cursor + 1, $match[0]);
                $cursor += \strlen($match[0]);
            } elseif (\preg_match('/:([a-z_\d]+)/Ai', $expression, $match, 0, $cursor)) {
                // Parameter
                $tokens[] = new Token(Token::TYPE_PARAMETER, $cursor + 1, $match[1]);
                $cursor += \strlen($match[0]);
            } else {
                throw new SyntaxException(\sprintf('Unexpected charset "%s".', $expression[$cursor]), $cursor, $expression);
            }
        }

        if (\count($brackets)) {
            [$expect, $cur] = \array_pop($brackets);

            throw new SyntaxException(\sprintf('Unclosed "%s".', $expect), $cur, $expression);
        }

        $tokens[] = new Token(Token::TYPE_EOF, $cursor, '');

        return new TokenStream($expression, ...$tokens);
    }
}
