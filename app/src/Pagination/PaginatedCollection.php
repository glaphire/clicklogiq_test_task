<?php

namespace App\Pagination;

class PaginatedCollection
{
    private $items;

    private $total;

    private $count;

    private $_links = [];

    public function __construct(array $items, $totalItems)
    {
        $this->items = $items;
        $this->total = $totalItems;
        $this->count = count($items);
    }

    public function addLink($ref, $url)
    {
        $this->_links[$ref] = $url;
    }
}
