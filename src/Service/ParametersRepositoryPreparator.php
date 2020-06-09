<?php


namespace App\Service;

use App\Repository\CustomerRepository;
use App\Repository\PhoneRepository;
use Symfony\Component\HttpFoundation\Request;

class ParametersRepositoryPreparator
{
    private $phoneRepository;
    private $customerRepository;

    public function __construct(PhoneRepository $phoneRepository, CustomerRepository $customerRepository)
    {
        $this->phoneRepository = $phoneRepository;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param Request $request
     * @param int $parameterMaxResult
     *
     * @return array|array[]|\string[][]
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function prepareParametersCustomer(Request $request, int $parameterMaxResult) : array
    {
        $page = 1;
        $maxResult = null;

        //Page
        if ($request->query->get('page') !== null) {
            $page = $this->preparePage($request, $parameterMaxResult, null, null, true);
            $maxResult = $parameterMaxResult;
        }
        // Errors
        if (is_array($page)) {
            return ['error' => [
                'pageError' => $page['error'],
            ]];
        }

        return compact('page', 'maxResult');
    }

    /**
     * @param Request $request
     * @param int $parameterMaxResult
     *
     * @return array|array[]
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function prepareParametersPhone(Request $request, int $parameterMaxResult) :array
    {
        $page = 1;
        $maxResult = null;
        $brand = empty($request->query->get('brand')) ? null : $request->query->get('brand');
        $price = [0, 10000];

        // Price
        if ($request->query->get('price') !== null) {
            $price = $this->preparePrice($request);
        }

        //Page
        if ($request->query->get('page') !== null) {
            $page = $this->preparePage($request, $parameterMaxResult, $brand, $price);
            $maxResult = $parameterMaxResult;
        }

        // Errors
        if (is_array($page) || count($price) === 1) {
            return $this->getErrors($page, $price);
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
    private function preparePage(Request $request, ?int $maxResult, ?string $brand, ?array $price, bool $customer = false)
    {
        if (!empty($request->query->get('page')) && !preg_match('#(^-?(\d+))$#', $request->query->get('page'))) {
            return $page = [
                'error' => 'La page demandée doit être un nombre !'
            ];
        }

        $page = (int)$request->query->get('page') > 1 ? (int)$request->query->get('page') : 1 ;

        if ($request->query->get('page') > 0 && preg_match('#(^-?(\d+))$#', $request->query->get('page'))) {
            $count =  $this->countAll($brand, $price, $customer)/$maxResult;

            if ((int)$request->query->get('page') > $count) {
                $page = ceil($count);
            }
        }

        return $page;
    }

    /**
     * @param string|null $brand
     * @param bool $customer
     *
     * @return float|int
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function countAll(?string $brand, ?array $price, bool $customer)
    {
        if ($customer) {
            return $this->customerRepository->countAll();
        }

        return $this->phoneRepository->countAll($brand, $price);
    }

    /**
     * @param Request $request
     * @param int $maxResult
     *
     * @return false|float|int|string[]
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function preparePageCustomer(Request $request, int $maxResult)
    {
        if (!empty($request->query->get('page')) && !preg_match('#(^-?(\d+))$#', $request->query->get('page'))) {
            return $page = [
                'error' => 'La page demandée doit être un nombre !'
            ];
        }

        $page = (int)$request->query->get('page') > 1 ? (int)$request->query->get('page') : 1 ;

        if ($request->query->get('page') > 0 && preg_match('#(^-?(\d+))$#', $request->query->get('page'))) {
            $count =  $this->customerRepository->countAll(null)/$maxResult;

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
    private function preparePrice(Request $request) : array
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

    /**
     * @param $page
     * @param array|null $price
     *
     * @return array[]
     */
    private function getErrors($page, array $price)
    {
        if (!is_array($page)) {
            return ['error' => [
                'priceError' => $price['error']
            ]];
        }
        if (count($price) !== 1) {
            return ['error' => [
                'pageError' => $page['error'],
            ]];
        }

        return ['error' => [
            'pageError' => $page['error'],
            'priceError' => $price['error']
        ]];
    }
}
