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

namespace FiveLab\Component\Ruler\Tests\Unit\Operator;

use FiveLab\Component\Ruler\Operator\Operators;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OperatorsTest extends TestCase
{
    #[Test]
    public function shouldSuccessGetOperator(): void
    {
        $operators = new Operators([]);

        $operators->add('=', static function () {
            return 'handled';
        });

        $handler = $operators->get('=');

        self::assertIsCallable($handler);
    }

    #[Test]
    public function shouldPassAllDataToOperator(): void
    {
        $operators = new Operators([]);

        $operators->add('foo', static function ($a, $b) {
            self::assertEquals('111', $a);
            self::assertEquals('222', $b);

            return 'handled';
        });

        $result = $operators->get('foo')('111', '222');

        self::assertEquals('handled', $result);
    }

    #[Test]
    public function shouldThrowErrorIfOperatorMissed(): void
    {
        $operators = new Operators([]);

        $operators->add('=', static function () {
        });

        $operators->add('!=', static function () {
        });

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The operator "and" was not found. Possible operators are "=", "!=".');

        $operators->get('and');
    }

    #[Test]
    public function shouldSuccessOverridesHandler(): void
    {
        $operator = new Operators([
            'and' => static function ($a, $b) {
                return $a.' and '.$b;
            },
        ]);

        $operator->add('and', static function ($a, $b) {
            return $a.' like '.$b;
        });

        $result = $operator->get('and')('1', '2');

        self::assertEquals('1 like 2', $result);
    }

    #[Test]
    public function shouldSuccessOverridesHandledWithReturnNull(): void
    {
        $operator = new Operators([
            'and' => static function ($a, $b) {
                return $a.' and '.$b;
            },
        ]);

        $operator->add('and', static function () {
            return null;
        });

        $result = $operator->get('and')('1', '2');

        self::assertEquals('1 and 2', $result);
    }

    #[Test]
    public function shouldThrowErrorIfReturnNull(): void
    {
        $operator = new Operators([
            'and' => static function () {
                return null;
            },
        ]);

        $operator->add('and', static function () {
            return null;
        });

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Any handlers for operator "and" returns correct value.');

        $operator->get('and')('1', '2');
    }
}
