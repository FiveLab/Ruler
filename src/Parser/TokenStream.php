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
 * A stream for possible iterate all tokens.
 */
class TokenStream
{
    /**
     * @var string
     */
    private string $expression;

    /**
     * @var array<Token>
     */
    private array $tokens;

    /**
     * @var int
     */
    private int $position = 0;

    /**
     * Constructor.
     *
     * @param string $expression
     * @param Token  ...$tokens
     */
    public function __construct(string $expression, Token ...$tokens)
    {
        $this->expression = $expression;
        $this->tokens = $tokens;
    }

    /**
     * Get expression
     *
     * @return string
     */
    public function getExpression(): string
    {
        return $this->expression;
    }

    /**
     * Get current token
     *
     * @return Token
     */
    public function current(): Token
    {
        return $this->tokens[$this->position];
    }

    /**
     * Move cursor to next token
     */
    public function next(): void
    {
        $nextPosition = $this->position + 1;

        if (!isset($this->tokens[$nextPosition])) {
            throw new SyntaxException('Unexpected end of expression.', $this->current()->getCursor(), $this->expression);
        }

        $this->position = $nextPosition;
    }

    /**
     * Is end of stream?
     *
     * @return bool
     */
    public function isEof(): bool
    {
        return $this->current()->getType() === Token::TYPE_EOF;
    }
}
