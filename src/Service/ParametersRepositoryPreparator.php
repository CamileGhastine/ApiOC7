<?php


namespace App\Service;

use App\Repository\PhoneRepository;
use Symfony\Component\HttpFoundation\Request;

class ParametersRepositoryPreparator
{
    private $phoneRepository;

    public function __construct(PhoneRepository $phoneRepository)
    {
        $this->phoneRepository = $phoneRepository;
    }

    /**
     * @param Request $request
     * @param int|null $parameterMaxResult
     *
     * @return array
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function prepareParametersPhone(Request $request, ?int $parameterMaxResult)
    {
        $page = 1;
        $maxResult = null;
        $brand = empty($request->query->get('brand')) ? null : $request->query->get('brand');
        $price = [0, 10000];

        //Page
        if ($request->query->get('page') !== null) {
            $page = $this->preparePage($request, $parameterMaxResult, $brand);
            $maxResult = $parameterMaxResult;
        }

        // Price
        if ($request->query->get('price') !== null) {
            $price = $this->preparePrice($request);
        }

        return compact('page', 'maxResult', 'brand', 'price');
    }

    /**
     * @param Request $request
     * @param int|null $maxResult
     * @param string|null $brand
     *
     * @return false|float|int|string[]
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function preparePage(Request $request, ?int $maxResult, ?string $brand)
    {
        if (!empty($request->query->get('page')) && !preg_match('#(^-?(\d+))$#', $request->query->get('page'))) {
            return $page = [
                'error' => 'La page demandée doit être un nombre !'
            ];
        }

        $page = (int)$request->query->get('page') > 1 ? (int)$request->query->get('page') : 1 ;
        if ($request->query->get('page') > 0 && preg_match('#(^-?(\d+))$#', $request->query->get('page'))) {
            $count =  $this->phoneRepository->countAll($brand)/$maxResult;

            if ((int)$request->query->get('page') > $count) {
                $page = ceil($count);
            }
        }

        return $page;
    }

    /**
     * @param Request $request
     *
     * @return array|false|string[]
     */
    private function preparePrice(Request $request)
    {
        // regex should be of type (minPrice) or (minPrice, maxPrice)
        if (!preg_match('#(^\[\d+( )?(,( )?\d+)?\])$#', $request->query->get('price'))) {
            return $price = [
                'error' => 'L\'intervalle de prix n\'a pas le bon format : [prixMin] ou [prixMin, prixMax]'
            ];
        }

        $price = preg_split('/[\s,]+/', substr($request->query->get('price'), 1, -1));

        if (count($price) == 1) {
            $price[1] = 10000;
        }

        // $price = array (minPrice, maxPrice)
        return $price;
    }
}
