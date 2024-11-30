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

namespace FiveLab\Component\Ruler\Tests\Unit\Executor;

use FiveLab\Component\Ruler\Executor\ExecutionContext;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ExecutionContextTest extends TestCase
{
    #[Test]
    public function shouldSuccessCreate(): void
    {
        $context = new ExecutionContext([
            'foo' => 'bar',
        ]);

        self::assertEquals('bar', $context->get('foo'));
    }

    #[Test]
    public function shouldFailGetIfMissedKey(): void
    {
        $context = new ExecutionContext([
            'foo' => 'bar',
            'bar' => 'foo',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The data "some" missed in context. Possible keys are "foo", "bar".');

        $context->get('some');
    }

    #[Test]
    public function shouldSuccessSet(): void
    {
        $context = new ExecutionContext([
            'foo' => 'some',
        ]);

        $context->set('foo', 'bar');

        self::assertEquals('bar', $context->get('foo'));
    }

    #[Test]
    public function shouldSuccessAdd(): void
    {
        $context = new ExecutionContext([
            'foo' => [],
        ]);

        $context->add('foo', null, 'bar');

        self::assertEquals(['bar'], $context->get('foo'));
    }

    #[Test]
    public function shouldSuccessAddWithInnerKey(): void
    {
        $context = new ExecutionContext([
            'some' => [
                'bar' => 'foo',
            ],
        ]);

        $context->add('some', 'foo', 'bar');

        self::assertEquals([
            'bar' => 'foo',
            'foo' => 'bar',
        ], $context->get('some'));
    }

    #[Test]
    public function shouldFailAddIfMissedKey(): void
    {
        $context = new ExecutionContext([]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Can\'t add inner element to data, "bar" missed in context.');

        $context->add('bar', null, 1);
    }

    #[Test]
    public function shouldFailAddIfKeyIsNotAnArray(): void
    {
        $context = new ExecutionContext([
            'bar' => 'foo',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Can\'t add inner element to data, "bar" is not an array.');

        $context->add('bar', null, 'some');
    }
}
