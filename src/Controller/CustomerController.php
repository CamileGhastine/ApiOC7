<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use App\Service\ParametersRepositoryPreparator;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    public function index(Request $request, CustomerRepository $customerRepository, SerializerInterface $serializer, ParametersRepositoryPreparator $preparator)
    {
        $parameters = $preparator->prepareParametersCustomer($request, $this->getParameter('paginator.maxResult'));

        // if $page have message error
        if (isset($parameters['error'])) {
            $data = [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => $parameters['error']
            ];

            return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
        }

        $customer = $customerRepository->findCustomerPaginated($parameters);

        $data = $serializer->serialize($customer->getIterator(), 'json', SerializationContext::create()->setGroups(['list']));

        return new Response($data, Response::HTTP_OK, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("/customers/{id<\d+>}", name="show_customer", methods={"GET"})
     */
    public function show(Customer $customer, SerializerInterface $serializer)
    {
        $data = $serializer->serialize($customer, 'json', SerializationContext::create()->setGroups(['detail']));

        return new Response($data, Response::HTTP_OK, [
            'Content-Type' => 'application/json'
        ]);
    }
}
