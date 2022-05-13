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
class Token
{
    public const TYPE_EOF         = 'eof';
    public const TYPE_PUNCTUATION = 'punctuation';
    public const TYPE_OPERATOR    = 'operator';
    public const TYPE_PROPERTY    = 'property';
    public const TYPE_NUMBER      = 'number';
    public const TYPE_PARAMETER   = 'parameter';

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var string
     */
    private string $type;

    /**
     * @var int
     */
    private int $cursor;

    /**
     * Constructor.
     *
     * @param string $type
     * @param int    $cursor
     * @param mixed  $value
     */
    public function __construct(string $type, int $cursor, $value)
    {
        $possibleTypes = self::getPossibleTypes();

        if (!\in_array($type, $possibleTypes, true)) {
            throw new \InvalidArgumentException(\sprintf(
                'Invalid token type "%s". Possible types are "%s".',
                $type,
                \implode('", "', $possibleTypes)
            ));
        }

        $this->type = $type;
        $this->cursor = $cursor;
        $this->value = $value;
    }

    /**
     * Get value of token
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get type of token
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get cursor of token
     *
     * @return int
     */
    public function getCursor(): int
    {
        return $this->cursor;
    }

    /**
     * Test token by type
     *
     * @param string $type
     *
     * @return bool
     */
    public function test(string $type): bool
    {
        return $this->type === $type;
    }

    /**
     * Get possible types
     *
     * @return string[]
     */
    public static function getPossibleTypes(): array
    {
        return [
            self::TYPE_EOF,
            self::TYPE_PUNCTUATION,
            self::TYPE_OPERATOR,
            self::TYPE_PROPERTY,
            self::TYPE_PARAMETER,
            self::TYPE_NUMBER,
        ];
    }
}
