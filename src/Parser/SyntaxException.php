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
 * Throw this error on invalid expression (syntax error)
 */
class SyntaxException extends \Exception
{
    /**
     * @var int
     */
    private int $cursor;

    /**
     * @var string
     */
    private string $expression;

    /**
     * Constructor.
     *
     * @param string $message
     * @param int    $cursor
     * @param string $expression
     */
    public function __construct(string $message, int $cursor, string $expression)
    {
        $message = \sprintf('%s around position %d', \rtrim($message, '.'), $cursor);

        if ($expression) {
            $message = \sprintf('%s for expression "%s"', $message, $expression);
        }

        $message .= '.';

        parent::__construct($message);

        $this->cursor = $cursor;
        $this->expression = $expression;
    }

    /**
     * Get cursor
     *
     * @return int
     */
    public function getCursor(): int
    {
        return $this->cursor;
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
}
