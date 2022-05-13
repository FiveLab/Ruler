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

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Product
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private int $id;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", name="created_at")
     */
    private \DateTimeInterface $createdAt;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="published")
     */
    private bool $published;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="tag")
     */
    private string $tag;

    /**
     * @var float
     *
     * @ORM\Column(type="float", name="price")
     */
    private float $price;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="\FiveLab\Component\Ruler\Tests\Functional\DoctrineOrm\Entities\Category")
     * @ORM\JoinColumn(name="category", referencedColumnName="id")
     */
    private Category $category;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="\FiveLab\Component\Ruler\Tests\Functional\DoctrineOrm\Entities\Variant", mappedBy="product")
     */
    private Collection $variants;

    /**
     * @var Money
     *
     * @ORM\Embedded(class="\FiveLab\Component\Ruler\Tests\Functional\DoctrineOrm\Entities\Money", columnPrefix="")
     */
    private Money $amount;
}
