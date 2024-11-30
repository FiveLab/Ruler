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

namespace FiveLab\Component\Ruler\Tests\Unit;

use FiveLab\Component\Ruler\Executor\ExecutorInterface;
use FiveLab\Component\Ruler\Node\Node;
use FiveLab\Component\Ruler\Parser\Lexer;
use FiveLab\Component\Ruler\Parser\Parser;
use FiveLab\Component\Ruler\Parser\TokenStream;
use FiveLab\Component\Ruler\Ruler;
use FiveLab\Component\Ruler\Specification\SimpleSpecification;
use FiveLab\Component\Ruler\Target\TargetInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RulerTest extends TestCase
{
    private Ruler $ruler;
    private object $targetObject;

    protected function setUp(): void
    {
        $this->targetObject = (object) ['foo' => 'bar'];

        $target = $this->createMock(TargetInterface::class);
        $lexer = $this->createMock(Lexer::class);
        $parser = $this->createMock(Parser::class);
        $executor = $this->createMock(ExecutorInterface::class);

        $target->expects(self::any())
            ->method('createExecutor')
            ->with($this->targetObject)
            ->willReturn($executor);

        $this->ruler = new Ruler($target, $lexer, $parser);

        $tokens = $this->createMock(TokenStream::class);
        $node = $this->createMock(Node::class);

        $lexer->expects(self::once())
            ->method('tokenize')
            ->with('some rule foo bar')
            ->willReturn($tokens);

        $parser->expects(self::once())
            ->method('parse')
            ->with($tokens)
            ->willReturn($node);

        $executor->expects(self::once())
            ->method('execute')
            ->with($this->targetObject, $node, ['p1' => 'v1', 'p2' => 'v2']);
    }

    #[Test]
    public function shouldSuccessApply(): void
    {
        $this->ruler->apply($this->targetObject, 'some rule foo bar', ['p1' => 'v1', 'p2' => 'v2']);
    }

    #[Test]
    public function shouldSuccessApplySpec(): void
    {
        $spec = new SimpleSpecification('some rule foo bar', ['p1' => 'v1', 'p2' => 'v2']);

        $this->ruler->applySpec($this->targetObject, $spec);
    }
}
