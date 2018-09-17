<?php

namespace App\Helpers;

use Doctrine\ORM\Tools\Pagination\Paginator;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use App\Helpers\PaginatedResult;

trait FractableTrait
{
    /**
     * Applies transformation to the entity, array or paginator
     * @param $resource
     * @param $transformerClass
     * @return array
     */
    public function transform($resource, $transformerClass)
    {
        //if paginated result like a list
        if ($resource instanceof PaginatedResult) {
            return $this->transformPaginatedResult($resource, $transformerClass);
        }

        //probably a findAll()
        if (is_array($resource)) {
            return $this->transformArray($resource, $transformerClass);
        }

        return $this->transformItem($resource, $transformerClass);
    }

    /**
     * @param PaginatedResult $resource
     * @param $transformerClass
     * @return array
     */
    public function transformPaginatedResult(PaginatedResult $resource, $transformerClass)
    {
        $fractal = new Manager();

        $data = new Collection($resource->getResults(), $transformerClass);
        $data = $fractal->createData($data)->toArray();

        $data = $this->addMeta($data, $resource->getPaginator());

        return $data;
    }

    /**
     * Transforms a raw array into a fractal array
     * @param array $resource
     * @param $transformerClass
     * @return array
     */
    public function transformArray(array $resource, $transformerClass)
    {
        $fractal = new Manager();

        $data = new Collection($resource, $transformerClass);
        $data = $fractal->createData($data);

        return $data->toArray();
    }

    /**
     * Transforms an entity into an array using the transformer specified
     * @param $item
     * @param $transformerClass
     * @return array
     */
    public function transformItem($item, $transformerClass)
    {
        $fractal = new Manager();

        $data = new Item($item, $transformerClass);
        $data = $fractal->createData($data);

        return $data->toArray();
    }

    private function addMeta($data, Paginator $paginator)
    {
        //TODO add next links
//        "pagination": {
//            "links": {
//                "next": "http://jtalent.local/api/candidates?page=2"
//            }

        $limit = $paginator->getQuery()->getMaxResults();
        $start = $paginator->getQuery()->getFirstResult();
        $count = $paginator->count();

        $data['meta'] = [
            'pagination' => [
                'total' => $count,
                'count' => count($data['data']),
                'limit' => $limit,
                'page' => ($start + $limit) / $limit,
                'total_pages' => ceil($count / $limit),
            ]
        ];

        return $data;
    }
}