<?php

namespace App\Helpers;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;

trait PaginationTrait
{

    /**
     * @param $query
     * @param Request $request
     * @return PaginatedResult
     */
    public function paginate($query, Request $request)
    {
        $page = $request->query->getInt('page') != 0 ? $request->query->getInt('page') : 1;
        $limit = $request->query->getInt('limit') != 0 ? $request->query->getInt('limit') : 15;

        $paginator = new Paginator($query);
        $firstResult = ($page - 1) * $limit;

        $paginator->getQuery()
            ->setFirstResult($firstResult)
            ->setMaxResults($limit)
        ;

        return new PaginatedResult($paginator);
    }

}