<?php


namespace App\Service;

/**
 * Class DataPreparator
 * @package App\Service
 */
class DataPaginator
{
    /**
     * @param $entities
     * @param $parameters
     *
     * @return mixed
     */
    public function paginate($entities, $parameters)
    {
        $prev = $parameters['page'] === 1 ? 1 : $parameters['page']-1;
        $last = (int)ceil($parameters['count'] / $parameters['maxResult']);
        $next = $parameters['page'] === $last ? $last : $parameters['page']+1;
        $class = lcfirst(str_replace("App\\Entity\\", "", get_class($entities[0]))).'s';

        $pagination = [
            "pagination" => [
                "self" => ["href" => "/api/v1/".$class."?page=".$parameters['page']],
                "first" => ["href" => "/api/v1/".$class],
                "prev" =>["href" => "/api/v1/".$class."?page=" . $prev],
                "next" =>["href" => "/api/v1/".$class."?page=" . $next],
                "last" =>["href" => "/api/v1/".$class."?page=" . $last],
                ],
            "maxResultPerPage" => $parameters['maxResult'],
            "totalResult" => $parameters['count'],
        ];

        $entities[] = $pagination;

        return $entities;
    }
}
