<?php

namespace App\Pagination;

class PaginatedCollection
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

    public function getItems()
    {
        return $this->items;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function getLinks()
    {
        return $this->links;
    }
}
