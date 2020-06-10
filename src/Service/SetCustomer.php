<?php


namespace App\Service;

class SetCustomer
{
    public function set($request, $customer)
    {
        $data = json_decode($request->getContent());

        foreach ($data as $key => $value) {
            $setter = 'set'.ucfirst($key);

            if (method_exists($customer, $setter)) {
                $customer->$setter($value);
            }
        }

        return $customer;
    }
}
