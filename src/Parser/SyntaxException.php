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
    public function __construct(string $message, public readonly int $cursor, public readonly string $expression)
    {
        $message = \sprintf('%s around position %d', \rtrim($message, '.'), $cursor);

        if ($expression) {
            $message = \sprintf('%s for expression "%s"', $message, $expression);
        }

        $message .= '.';

        parent::__construct($message);
    }
}
