<?php

namespace App\Helpers;

use Doctrine\ORM\Tools\Pagination\Paginator;

class PaginatedResult
{
    protected $paginator;

    function __construct(Paginator $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * Get results in an array
     * @return array
     */
    public function getResults()
    {
        $entities = [];

        foreach ($this->paginator as $item) {
            $entities[] = $item;
        }

        return $entities;
    }

    /**
     * @return Paginator
     */
    public function getPaginator()
    {
        return $this->paginator;
    }
}