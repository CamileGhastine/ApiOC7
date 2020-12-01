<?php


namespace App\Service;

use App\Repository\PhoneRepository;

class SetCustomer
{
    private $phoneRepository;

    public function __construct(PhoneRepository $phoneRepository)
    {
        $this->phoneRepository = $phoneRepository;
    }

    /**
     * @param $request
     *
     * @param $customer
     */
    public function set($request, $customer)
    {
        $data = json_decode($request->getContent());

        foreach ($data as $key => $value) {
            $setter = 'set'.ucfirst($key);

            if (method_exists($customer, $setter)) {
                $customer->$setter($value);
            }
        }
    }
}
