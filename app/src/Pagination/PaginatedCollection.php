<?php

declare(strict_types=1);

namespace App\Pagination;

use Countable;

class PaginatedCollection
{
    private Countable $items;

    private int $total;

    private int $count;

    private array $links = [];

    /**
     * @param Countable $items
     * @param int $totalItems
     */
    public function __construct(Countable $items, int $totalItems)
    {
        $this->items = $items;
        $this->total = $totalItems;
        $this->count = count($items);
    }

    public function addLink(string $ref, string $url): void
    {
        $this->links[$ref] = $url;
    }

    public function getItems(): Countable
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
