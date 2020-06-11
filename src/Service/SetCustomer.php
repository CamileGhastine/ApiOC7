<?php


namespace App\Service;

use App\Entity\Customer;
use App\Repository\PhoneRepository;
use Symfony\Component\HttpFoundation\Request;

class SetCustomer
{
    private $phoneRepository;

    public function __construct(PhoneRepository $phoneRepository)
    {
        $this->phoneRepository = $phoneRepository;
    }

    public function set($request, $customer)
    {
        $data = json_decode($request->getContent());

        foreach ($data as $key => $value) {
            $setter = 'set'.ucfirst($key);

            if (method_exists($customer, $setter)) {
                $customer->$setter($value);
            }
        }

        foreach ($data->phones as $phoneId) {
            $customer->addPhone($this->phoneRepository->find($phoneId));
        }
    }

    /**
     * @param Request $request
     *
     * @return Customer
     */
    public function setNew(Request $request)
    {
        $data = json_decode($request->getContent());

        $customer = new Customer();
        $customer->setEmail($data->email)
            ->setFirstName($data->firstName)
            ->setLastName($data->lastName)
            ->setAddress($data->address)
            ->setPostCode($data->postCode)
            ->setCity($data->city);

        foreach ($data->phones as $phoneId) {
            $customer->addPhone($this->phoneRepository->find($phoneId));
        }

        return $customer;
    }
}
