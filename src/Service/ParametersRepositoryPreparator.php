<?php


namespace App\Service;


use Symfony\Component\HttpFoundation\Request;

class ParametersRepositoryPreparator
{
    public function preparePhone(Request $request, ?int $maxResult)
    {
        $page = (int)$request->query->get('page') > 1 ? (int)$request->query->get('page') : 1 ;
        $maxResult = strtolower($request->query->get('page')) === 'all' ? null : $maxResult;
        $brand = $request->query->get('brand') ? $request->query->get('brand') : null;
        $price = [0, 10000];

        // regex should be of type (minPrice) or (minPrice, maxPrice)
        // $price =array (minPrice, maxPrice)
        if ($request->query->get('price') && preg_match('#(^\(\d+( )?(,( )?\d+)?\))$#', $request->query->get('price'))) {
            $price = preg_split('/[\s,]+/', substr($request->query->get('price'), 1, -1));
            if (count($price) == 1) $price[1] = 10000;
        }

        return compact('page', 'maxResult', 'brand', 'price');
    }
}