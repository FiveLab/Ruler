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

namespace FiveLab\Component\Ruler\Query;

/**
 * The query class for elasticsearch/elasticsearch and opensearch-project/opensearch-php libraries.
 */
class RawSearchQuery
{
    /**
     * @var array<string, mixed>
     */
    private array $rawQuery;

    /**
     * Sets raw query
     *
     * @param array<string, mixed> $rawQuery
     *
     * @return self
     */
    public function setRawQuery(array $rawQuery): self
    {
        $this->rawQuery = $rawQuery;

        return $this;
    }

    /**
     * Converts to array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->rawQuery;
    }
}
