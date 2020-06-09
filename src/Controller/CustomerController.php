<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use App\Service\ParametersRepositoryPreparator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CustomerController
 * @Route("/api")
 * @package App\Controller
 */
class CustomerController extends AbstractController
{
    /**
     * @Route("/customers", name="list_customer", methods={"GET"})
     * @param Request $request
     * @param CustomerRepository $customerRepository
     * @param SerializerInterface $serializer
     * @param ParametersRepositoryPreparator $preparator
     *
     * @return JsonResponse|Response
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws Exception
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
     *
     * @param Customer $customer
     * @param SerializerInterface $serializer
     *
     * @return Response
     */
    public function show(Customer $customer, SerializerInterface $serializer)
    {
        $data = $serializer->serialize($customer, 'json', SerializationContext::create()->setGroups(['detail']));

        return new Response($data, Response::HTTP_OK, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("/customers", name="add_customers", methods={"POST"})
     */
    public function new(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator)
    {
        $customer = $serializer->deserialize($request->getContent(), Customer::class, 'json');

        $errors = $validator->validate($customer);

        if (count($errors)) {
            $data = $serializer->serialize($errors, 'json');

            return new Response($data, Response::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE, [
                'Content-Type' => 'application/json'
            ]);
        }

        $em->persist($customer);
        $em->flush();

        return new Response('Le client a été ajouté avec succès !', Response::HTTP_CREATED, [
            'Content-Type' => 'application/json'
        ]);
    }
}
