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
 * @ORM\Entity()
 */
class Category
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="int")
     */
    private int $id;

    /**
     * @var string
     *
     * @ORM\Column(name="keyword", type="string")
     */
    private string $key;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private bool $enabled;
}
