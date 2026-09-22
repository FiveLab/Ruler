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

#[ORM\Entity]
class ProductGroup
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(name: 'name', type: 'string')]
    private string $name;
}
