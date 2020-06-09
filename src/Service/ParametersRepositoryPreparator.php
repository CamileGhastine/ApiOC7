<?php


namespace App\Service;

use App\Repository\CustomerRepository;
use App\Repository\PhoneRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\Request;

class ParametersRepositoryPreparator
{
    private $phoneRepository;
    private $customerRepository;
    private $queryPage;

    public function __construct(PhoneRepository $phoneRepository, CustomerRepository $customerRepository)
    {
        $this->phoneRepository = $phoneRepository;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param Request $request
     * @param int $parameterMaxResult
     *
     * @return array|array[]|string[]
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function prepareParametersCustomer(Request $request, int $parameterMaxResult) : array
    {
        $this->queryPage = $request->query->get('page');
        $page = 1;
        $maxResult = null;

        //Page
        if ($this->queryPage !== null) {
            $page = $this->preparePage($parameterMaxResult, null, null, true);
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
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function prepareParametersPhone(Request $request, int $parameterMaxResult) :array
    {
        $this->queryPage = $request->query->get('page');
        $page = 1;
        $maxResult = null;
        $brand = empty($request->query->get('brand')) ? null : $request->query->get('brand');
        $price = [0, 10000];

        // Price
        if (!empty($request->query->get('price'))) {
            $price = $this->preparePrice($request->query->get('price'));
        }

        //Page
        if ($this->queryPage !== null && count($price) === 2) {
            $page = $this->preparePage($parameterMaxResult, $brand, $price);
            $maxResult = $parameterMaxResult;
        }

        // Errors
        if (is_array($page) || count($price) === 1) {
            return $this->getErrors($page, $price);
        }

        return compact('page', 'maxResult', 'brand', 'price');
    }

    /**
     * @param int|null $maxResult
     * @param string|null $brand
     * @param array|null $price
     * @param bool $customer
     *
     * @return false|float|int|string[]
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    private function preparePage(?int $maxResult, ?string $brand, ?array $price, bool $customer = false)
    {
        if (!empty($this->queryPage) && !preg_match('#(^-?(\d+))$#', $this->queryPage)) {
            return $page = [
                'error' => 'La page demandée doit être un nombre !'
            ];
        }

        $page = (int)$this->queryPage > 1 ? (int)$this->queryPage : 1 ;

        if ($this->queryPage > 0 && preg_match('#(^-?(\d+))$#', $this->queryPage)) {
            $count =  $this->countAll($brand, $price, $customer)/$maxResult;
            if ((int)$this->queryPage > $count) {
                $page = (int)ceil($count);
            }
        }

        if ($page === 0) {
            $page = 1;
        }

        return $page;
    }

    /**
     * @param string|null $brand
     * @param array|null $price
     * @param bool $customer
     *
     * @return int|mixed|string
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    private function countAll(?string $brand, ?array $price, bool $customer)
    {
        if ($customer) {
            return $this->customerRepository->countAll();
        }

        return $this->phoneRepository->countAll($brand, $price);
    }

    /**
     * @param string $price
     * @return array|string[]
     */
    private function preparePrice(string $price) : array
    {
        // regex should be of type [minPrice] or [inPrice, maxPrice]
        if (!preg_match('#(^\[\d+( )?(,( )?\d+)?\])$#', $price)) {
            return [
                'error' => 'L\'intervalle de prix n\'a pas le bon format : [prixMin] ou [prixMin, prixMax]'
            ];
        }

        $price = preg_split('/[\s,]+/', substr($price, 1, -1));

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
