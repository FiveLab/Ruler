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

namespace FiveLab\Component\Ruler\Tests\Functional\DoctrineOrm\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable()
 */
class Money
{
    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string")
     */
    private string $currency;

    /**
     * @var string
     *
     * @ORM\Column(name="amount", type="string")
     */
    private string $amount;
}
