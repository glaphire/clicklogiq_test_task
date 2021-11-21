<?php

declare(strict_types=1);

namespace App\Pagination;

use App\Entity\EntityInterface;

class PaginatedCollection
{
    /**
     * @var EntityInterface[]
     */
    private $items;

    private int $total;

    private int $count;

    private array $links = [];

    /**
     * @param EntityInterface[] $items
     * @param int $totalItems
     */
    public function __construct($items, int $totalItems)
    {
        $this->items = $items;
        $this->total = $totalItems;
        $this->count = count($items);
    }

    public function addLink(string $ref, string $url)
    {
        $this->links[$ref] = $url;
    }

    /**
     * @return EntityInterface[]
     */
    public function getItems()
    {
        return $this->items;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getLinks(): array
    {
        return $this->links;
    }
}
