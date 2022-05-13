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
class Parser
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

    /**
     * Parse token stream and return nodes
     *
     * @param TokenStream $stream
     *
     * @return Node
     */
    public function parse(TokenStream $stream): Node
    {
        $node = $this->parseExpression($stream);

        if (!$stream->isEof()) {
            throw new SyntaxException(\sprintf(
                'Unexpected token "%s" of value "%s".',
                $stream->current()->getType(),
                $stream->current()->getValue()
            ), $stream->current()->getCursor(), $stream->getExpression());
        }

        return $node;
    }

    /**
     * Parse expression
     *
     * @param TokenStream $stream
     * @param int         $precedence
     *
     * @return Node
     */
    private function parseExpression(TokenStream $stream, int $precedence = 0): Node
    {
        $expr = $this->parsePrimaryExpression($stream);
        $token = $stream->current();

        while ($token->test(Token::TYPE_OPERATOR) && isset(self::PRECEDENCES[$token->getValue()]) && self::PRECEDENCES[$token->getValue()] >= $precedence) {
            $stream->next();

            $expr1 = $this->parseExpression($stream, self::PRECEDENCES[$token->getValue()] + 1);
            $expr = new BinaryNode($token->getValue(), $expr, $expr1);

            $token = $stream->current();
        }

        return $expr;
    }

    /**
     * Parse primary expression
     *
     * @param TokenStream $stream
     *
     * @return Node
     */
    private function parsePrimaryExpression(TokenStream $stream): Node
    {
        $token = $stream->current();

        switch ($token->getType()) {
            case Token::TYPE_PROPERTY:
                $stream->next();

                switch (\strtolower($token->getValue())) {
                    case 'null':
                        return new ConstantNode(null);

                    case 'true':
                        return new ConstantNode(true);

                    case 'false':
                        return new ConstantNode(false);
                }

                return new NameNode($token->getValue());

            case Token::TYPE_NUMBER:
                $stream->next();

                return new ConstantNode($token->getValue());

            case Token::TYPE_PARAMETER:
                $stream->next();

                return new ParameterNode($token->getValue());

            // phpcs:ignore
            case Token::TYPE_PUNCTUATION:
                if ($token->getValue() === '(') {
                    $stream->next();

                    $expr = $this->parseExpression($stream);

                    $stream->next();

                    return $expr;
                }

            default:
                throw new SyntaxException(\sprintf(
                    'Unexpected token "%s" of value "%s".',
                    $token->getType(),
                    $token->getValue()
                ), $token->getCursor(), $stream->getExpression());
        }
    }
}
