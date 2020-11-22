<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use App\Service\ParametersRepositoryPreparator;
use App\Service\SetCustomer;
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
 *
 * @Route("/api")
 *
 * @package App\Controller
 */
class CustomerController extends AbstractController
{
    private $serializer;
    private $validator;
    private $em;

    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $em)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->em = $em;
    }

    /**
     * @Route("/customers", name="list_customer", methods={"GET"})
     *
     * @param Request $request
     * @param CustomerRepository $customerRepository
     * @param ParametersRepositoryPreparator $preparator
     *
     * @return JsonResponse|Response
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function index(Request $request, CustomerRepository $customerRepository, ParametersRepositoryPreparator $preparator)
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

        $data = $this->serializer->serialize($customer->getIterator(), 'json', SerializationContext::create()->setGroups(['list']));

        return new Response($data, Response::HTTP_OK, ['Content-TYpe' => 'application/json']);
    }

    /**
     * @Route("/customers/{id<\d+>}", name="show_customer", methods={"GET"})
     *
     * @param Customer $customer
     *
     * @return JsonResponse|Response
     */
    public function show(Customer $customer)
    {
        $data = $this->serializer->serialize($customer, 'json', SerializationContext::create()->setGroups(['detail']));

        return new Response($data, Response::HTTP_OK, ['Content-TYpe' => 'application/json']);
    }

    /**
     * @Route("/customers", name="add_customer", methods={"POST"})
     *
     * @param Request $request
     *
     * @param SetCustomer $setCustomer
     *
     * @return JsonResponse|Response
     */
    public function new(Request $request, SerializerInterface $serializer)
    {
        $customer = $serializer->deserialize($request->getContent(), Customer::class, 'json');

        $errors = $this->validator->validate($customer);

        if (count($errors)) {
            $data = $this->serializer->serialize($errors, 'json');

            return new Response($data, Response::HTTP_UNPROCESSABLE_ENTITY, ['Content-TYpe' => 'application/json']);
        }

        $this->em->persist($customer);
        $this->em->flush();

        return new JsonResponse('Le client a été ajouté avec succès !', Response::HTTP_CREATED);
    }

    /**
     * @Route("/customers/{id<\d+>}", name="update_customer", methods={"PUT"})
     *
     * @param Customer $customer
     * @param Request $request
     * @param SetCustomer $setCustomer
     *
     * @return JsonResponse|Response
     */
    public function update(Customer $customer, Request $request, SetCustomer $setCustomer)
    {
        $setCustomer->set($request, $customer);

        $errors = $this->validator->validate($customer);

        if (count($errors)) {
            $data = $this->serializer->serialize($errors, 'json');

            return new Response($data, Response::HTTP_UNPROCESSABLE_ENTITY, ['Content-TYpe' => 'application/json']);
        }

        $this->em->flush();

        return new JsonResponse('Le client a été modifié avec succès !', Response::HTTP_CREATED);
    }

    /**
     * @Route("/customers/{id<\d+>}", name="delete_customer", methods={"DELETE"})
     *
     * @param Customer $customer
     *
     * @return JsonResponse
     */
    public function delete(Customer $customer)
    {
        $this->em->remove($customer);

        $this->em->flush();

        return new JsonResponse('Le client a été supprimé avec succès !', Response::HTTP_OK);
    }
}
