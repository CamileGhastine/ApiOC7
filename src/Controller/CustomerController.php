<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use JMS\Serializer\SerializationContext;
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
    public function index(Request $request, CustomerRepository $customerRepository, SerializerInterface $serializer)
    {
        $page = $request->query->get('page');
        $maxResult = $request->query->get('page') ? $this->getParameter('paginator.maxResult') : null;

        $customer = $customerRepository->findCustomerPaginated($page, $maxResult);

        $data = $serializer->serialize($customer->getIterator(), 'json', SerializationContext::create()->setGroups(['list']));

        return new Response($data, Response::HTTP_OK, [
            'Content-Type' => 'application/json'
        ]);
    }
}
