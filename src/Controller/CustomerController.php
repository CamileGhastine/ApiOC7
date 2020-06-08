<?php

namespace App\Controller;

use App\Entity\Customer;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CustomerController
 * @Route("/api")
 * @package App\Controller
 */
class CustomerController extends AbstractController
{
    /**
     * @Route("/customers", name="list_customer", methods={"GET"})
     */
    public function index(SerializerInterface $serializer)
    {
        $customer = new Customer();
        $customer->setEmail('camile@camile.fr')
            ->setFirstName('camile')
            ->setLastName('ghastine')
            ->setAdress('rue de bellevue')
            ->setPostalCode(77176)
            ->setCity('Paris');

        $data = $serializer->serialize($customer, 'json');

        return new Response($data, Response::HTTP_OK, [
            'Content-Type' => 'application/json'
        ]);

    }
}
