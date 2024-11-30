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

use FiveLab\Component\Ruler\Node\BinaryNode;
use FiveLab\Component\Ruler\Node\ConstantNode;
use FiveLab\Component\Ruler\Node\NameNode;
use FiveLab\Component\Ruler\Node\Node;
use FiveLab\Component\Ruler\Node\ParameterNode;

/**
 * A parser for parse tokenized expression
 *
 * @see http://en.wikipedia.org/wiki/Operator-precedence_parser
 */
readonly class Parser
{
    /**
     * List of possible precedences
     */
    public const PRECEDENCES = [
        'or'     => 10,
        'and'    => 15,
        '='      => 20,
        '!='     => 20,
        '<'      => 20,
        '>'      => 20,
        '<='     => 20,
        '>='     => 20,
        'not in' => 20,
        'in'     => 20,
        'like'   => 20,
        '+'      => 30,
        '-'      => 30,
        '*'      => 60,
        '/'      => 60,
    ];

    public function parse(TokenStream $stream): Node
    {
        $node = $this->parseExpression($stream);

        if (!$stream->isEof()) {
            throw new SyntaxException(\sprintf(
                'Unexpected token "%s" of value "%s".',
                $stream->current()->type,
                $stream->current()->value
            ), $stream->current()->cursor, $stream->expression);
        }

        return $node;
    }

    private function parseExpression(TokenStream $stream, int $precedence = 0): Node
    {
        $expr = $this->parsePrimaryExpression($stream);
        $token = $stream->current();

        while ($token->test(Token::TYPE_OPERATOR) && isset(self::PRECEDENCES[$token->value]) && self::PRECEDENCES[$token->value] >= $precedence) {
            $stream->next();

            $expr1 = $this->parseExpression($stream, self::PRECEDENCES[$token->value] + 1);
            $expr = new BinaryNode($token->value, $expr, $expr1);

            $token = $stream->current();
        }

        return $expr;
    }

    private function parsePrimaryExpression(TokenStream $stream): Node
    {
        $token = $stream->current();

        switch ($token->type) {
            case Token::TYPE_PROPERTY:
                $stream->next();

                switch (\strtolower($token->value)) {
                    case 'null':
                        return new ConstantNode(null);

                    case 'true':
                        return new ConstantNode(true);

                    case 'false':
                        return new ConstantNode(false);
                }

                return new NameNode($token->value);

            case Token::TYPE_NUMBER:
                $stream->next();

                return new ConstantNode($token->value);

            case Token::TYPE_PARAMETER:
                $stream->next();

                return new ParameterNode($token->value);

            // phpcs:ignore
            case Token::TYPE_PUNCTUATION:
                if ($token->value === '(') {
                    $stream->next();

                    $expr = $this->parseExpression($stream);

                    $stream->next();

                    return $expr;
                }

            default:
                throw new SyntaxException(\sprintf(
                    'Unexpected token "%s" of value "%s".',
                    $token->type,
                    $token->value
                ), $token->cursor, $stream->expression);
        }
    }
}
