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
     * @var array<Token>
     */
    private readonly array $tokens;
    private int $position = 0;

    public function __construct(public readonly string $expression, Token ...$tokens)
    {
        $this->tokens = $tokens;
    }

    public function current(): Token
    {
        return $this->tokens[$this->position];
    }

    public function next(): void
    {
        $nextPosition = $this->position + 1;

        if (!isset($this->tokens[$nextPosition])) {
            throw new SyntaxException('Unexpected end of expression.', $this->current()->cursor, $this->expression);
        }

        $this->position = $nextPosition;
    }

    public function isEof(): bool
    {
        return $this->current()->type === Token::TYPE_EOF;
    }
}
