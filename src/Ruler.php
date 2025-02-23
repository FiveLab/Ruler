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

readonly class Ruler implements RulerInterface
{
    private Lexer $lexer;
    private Parser $parser;

    public function __construct(private TargetInterface $target, ?Lexer $lexer = null, ?Parser $parser = null)
    {
        $this->lexer = $lexer ?: new Lexer();
        $this->parser = $parser ?: new Parser();
    }

    public function apply(object $target, string $rule, array $parameters): void
    {
        $tokens = $this->lexer->tokenize($rule);
        $node = $this->parser->parse($tokens);

        $executor = $this->target->createExecutor($target);

        $executor->execute($target, $node, $parameters);
    }

    public function applySpec(object $target, SpecificationInterface $specification): void
    {
        if ($specification->getRule()) {
            $this->apply($target, $specification->getRule(), $specification->getParameters());
        }
    }
}
