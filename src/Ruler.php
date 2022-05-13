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

namespace FiveLab\Component\Ruler;

use FiveLab\Component\Ruler\Parser\Lexer;
use FiveLab\Component\Ruler\Parser\Parser;
use FiveLab\Component\Ruler\Specification\SpecificationInterface;
use FiveLab\Component\Ruler\Target\TargetInterface;

/**
 * Base ruler.
 */
class Ruler implements RulerInterface
{
    /**
     * @var TargetInterface
     */
    private TargetInterface $target;

    /**
     * @var Lexer
     */
    private Lexer $lexer;

    /**
     * @var Parser
     */
    private Parser $parser;

    /**
     * Constructor.
     *
     * @param TargetInterface $target
     * @param Lexer|null      $lexer
     * @param Parser|null     $parser
     */
    public function __construct(TargetInterface $target, Lexer $lexer = null, Parser $parser = null)
    {
        $this->target = $target;
        $this->lexer = $lexer ?: new Lexer();
        $this->parser = $parser ?: new Parser();
    }

    /**
     * {@inheritdoc}
     */
    public function apply(object $target, string $rule, array $parameters): void
    {
        $tokens = $this->lexer->tokenize($rule);
        $node = $this->parser->parse($tokens);

        $executor = $this->target->createExecutor($target);

        $executor->execute($target, $node, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function applySpec(object $target, SpecificationInterface $specification): void
    {
        if ($specification->getRule()) {
            $this->apply($target, $specification->getRule(), $specification->getParameters());
        }
    }
}
