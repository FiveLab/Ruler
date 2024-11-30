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
 * A value object for presentation token.
 */
readonly class Token
{
    public const TYPE_EOF         = 'eof';
    public const TYPE_PUNCTUATION = 'punctuation';
    public const TYPE_OPERATOR    = 'operator';
    public const TYPE_PROPERTY    = 'property';
    public const TYPE_NUMBER      = 'number';
    public const TYPE_PARAMETER   = 'parameter';

    public function __construct(public string $type, public int $cursor, public mixed $value)
    {
        static $possibleTypes = [
            self::TYPE_EOF,
            self::TYPE_PUNCTUATION,
            self::TYPE_OPERATOR,
            self::TYPE_PROPERTY,
            self::TYPE_PARAMETER,
            self::TYPE_NUMBER,
        ];

        if (!\in_array($this->type, $possibleTypes, true)) {
            throw new \InvalidArgumentException(\sprintf(
                'Invalid token type "%s". Possible types are "%s".',
                $type,
                \implode('", "', $possibleTypes)
            ));
        }
    }

    public function test(string $type): bool
    {
        return $this->type === $type;
    }
}
