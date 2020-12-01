<?php


namespace App\Service;

/**
 * Class DataPreparator
 * @package App\Service
 */
class DataPaginator
{
    public function paginate ($phones, $parameters)
    {
        $prev = $parameters['page'] === 1 ? 1 : $parameters['page']-1;
        $last = (int)ceil($parameters['count'] / $parameters['maxResult']);
        $next = $parameters['page'] === $last ? $last : $parameters['page']+1;

        $pagination = [
            "_links" => [
                "self" => ["href" => "/api/v1/phones?page=".$parameters['page']],
                "first" => ["href" => "/api/v1/phones"],
                "prev" =>["href" => "/api/v1/phones?page=" . $prev],
                "next" =>["href" => "/api/v1/phones?page=" . $next],
                "last" =>["href" => "/api/v1/phones?page=" . $last],
                ],
            "maxResultPerPage" => $parameters['maxResult'],
            "totalResult" => $parameters['count'],
        ];

        $phones[] = $pagination;

        return $phones;
    }
}