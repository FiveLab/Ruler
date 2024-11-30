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

namespace FiveLab\Component\Ruler\Tests\Unit\Specification;

use FiveLab\Component\Ruler\Specification\AndX;
use FiveLab\Component\Ruler\Specification\SimpleSpecification;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AndXTest extends TestCase
{
    #[Test]
    public function shouldSuccessCreate(): void
    {
        $spec = new AndX(
            new SimpleSpecification('id = :id', ['id' => 1]),
            new SimpleSpecification('status = :status', ['status' => true])
        );

        self::assertEquals('(id = :id AND status = :status)', $spec->getRule());
        self::assertEquals([
            'id'     => 1,
            'status' => true,
        ], $spec->getParameters());
    }
}
