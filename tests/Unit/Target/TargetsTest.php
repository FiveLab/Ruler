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

namespace FiveLab\Component\Ruler\Tests\Unit\Target;

use FiveLab\Component\Ruler\Executor\ExecutorInterface;
use FiveLab\Component\Ruler\Target\TargetInterface;
use FiveLab\Component\Ruler\Target\Targets;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TargetsTest extends TestCase
{
    #[Test]
    public function shouldSuccessSupports(): void
    {
        $targetFactory1 = $this->makeTargetFactory();
        $targetFactory2 = $this->makeTargetFactory();
        $targetFactory3 = $this->makeTargetFactory();

        $target = (object) ['foo' => 'bar'];

        $targetFactory1->expects(self::once())
            ->method('supports')
            ->with($target)
            ->willReturn(false);

        $targetFactory2->expects(self::once())
            ->method('supports')
            ->with($target)
            ->willReturn(true);

        $targetFactory3->expects(self::never())
            ->method('supports');

        $targetFactories = new Targets($targetFactory1, $targetFactory2, $targetFactory3);

        self::assertTrue($targetFactories->supports($target));
    }

    #[Test]
    public function shouldNotSupports(): void
    {
        $targetFactory = $this->makeTargetFactory();
        $target = (object) ['foo' => 'bar'];

        $targetFactory->expects(self::once())
            ->method('supports')
            ->with($target)
            ->willReturn(false);

        $targetFactories = new Targets($targetFactory);

        self::assertFalse($targetFactories->supports($target));
    }

    #[Test]
    public function shouldSuccessCreateExecutor(): void
    {
        $targetFactory1 = $this->makeTargetFactory();
        $targetFactory2 = $this->makeTargetFactory();
        $targetFactory3 = $this->makeTargetFactory();

        $target = (object) ['bar' => 'foo'];

        $targetFactory1->expects(self::once())
            ->method('supports')
            ->with($target)
            ->willReturn(false);

        $targetFactory2->expects(self::once())
            ->method('supports')
            ->with($target)
            ->willReturn(true);

        $targetFactory3->expects(self::never())
            ->method('createExecutor');

        $executor = $this->createMock(ExecutorInterface::class);

        $targetFactory2->expects(self::once())
            ->method('createExecutor')
            ->with($target)
            ->willReturn($executor);

        $targetFactories = new Targets($targetFactory1, $targetFactory2, $targetFactory3);

        $result = $targetFactories->createExecutor($target);

        self::assertEquals($executor, $result);
    }

    #[Test]
    public function shouldCachedExecutors(): void
    {
        $targetFactory = $this->makeTargetFactory();
        $target = (object) ['some' => 'foo bar'];

        $targetFactory->expects(self::exactly(2))
            ->method('supports')
            ->with($target)
            ->willReturn(true);

        $executor = $this->createMock(ExecutorInterface::class);

        $targetFactory->expects(self::once())
            ->method('createExecutor')
            ->with($target)
            ->willReturn($executor);

        $targetFactories = new Targets($targetFactory);

        // First create
        $result = $targetFactories->createExecutor($target);
        self::assertEquals($executor, $result);

        // Secondary create
        $result = $targetFactories->createExecutor($target);
        self::assertEquals($executor, $result);
    }

    #[Test]
    public function shouldThrowErrorIfAnyFactorySupported(): void
    {
        $targetFactory = $this->makeTargetFactory();
        $target = (object) ['some' => 'foo bar'];

        $targetFactory->expects(self::once())
            ->method('supports')
            ->with($target)
            ->willReturn(false);

        $targetFactories = new Targets($targetFactory);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Any target support "stdClass".');

        $targetFactories->createExecutor($target);
    }

    private function makeTargetFactory(): TargetInterface&MockObject
    {
        return $this->getMockBuilder(TargetInterface::class)
            ->setMockClassName('TargetInterface_'.\md5(\uniqid((string) \random_int(PHP_INT_MIN, PHP_INT_MAX), true)))
            ->getMock();
    }
}
