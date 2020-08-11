<?php

namespace App\Pagination;

use JsonSerializable;

class PaginatedCollection implements JsonSerializable
{
    private $items;

    private $total;

    private $count;

    private $links = [];

    public function __construct($items, $totalItems)
    {
        $this->items = $items;
        $this->total = $totalItems;
        $this->count = count($items);
    }

    public function addLink($ref, $url)
    {
        $this->links[$ref] = $url;
    }

    public function jsonSerialize()
    {
        return [
            'items' => $this->items,
            'total' => $this->total,
            'count' => $this->count,
            'links' => $this->links,
        ];
    }
}
