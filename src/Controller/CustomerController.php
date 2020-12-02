<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use App\Service\Encacher;
use App\Service\MessageGenerator;
use App\Service\ParametersRepositoryPreparator;
use App\Service\SetCustomer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use JMS\Serializer\SerializerInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Annotations as OA;

/**
 * Class CustomerController
 *
 * @Route("/api/v1")
 *
 * @package App\Controller
 */
class CustomerController extends AbstractController
{
    private $serializer;
    private $validator;
    private $em;
    private $encacher;
    private $messageGenerator;

    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $em, Encacher $encacher, MessageGenerator $messageGenerator)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->em = $em;
        $this->encacher = $encacher;
        $this->messageGenerator = $messageGenerator;
    }

    /**
     * @Route("/customers", name="list_customer", methods={"GET"})
     *
     * @OA\Get(
     *     path="/customers",
     *     security={"bearer"},
     *     tags={"Customer"},
     *     @OA\Parameter(
     *          name="page",
     *          in="query",
     *          example="/customers?page=2",
     *          required = false,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Liste de clients",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/CustomersList"))
     *     )
     * )
     *
     * @param Request $request
     * @param ParametersRepositoryPreparator $preparator
     *
     * @return JsonResponse|Response
     *
     * @throws InvalidArgumentException
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function index(Request $request, ParametersRepositoryPreparator $preparator)
    {
        $parameters = $preparator->prepareParametersCustomer($request, $this->getUser()->getId(), $this->getParameter('paginator.maxResult'));

        $message = $this->messageGenerator->generateForIndex($parameters);
        if ($message) {
            return new JsonResponse($message['message'], $message['http_response']);
        }

        $data = $this->encacher->cacheIndex($request, $parameters, $this->getUser()->getId());

        return new Response($data, Response::HTTP_OK, [
            'Content-Type' => 'application/json'
        ]);
    }


    /**
     * @Route("/customers/{id<\d+>}", name="show_customer", methods={"GET"})
     *
     * @OA\Get(
     *     path="/customers/{id}",
     *     security={"bearer"},
     *     tags={"Customer"},
     *     @OA\Parameter(ref="#/components/parameters/id"),
     *     @OA\Response(
     *          response="200",
     *          description="Informations client",
     *          @OA\JsonContent(ref="#/components/schemas/Customer")
     *     ),
     *     @OA\Response(response="404", ref="#/components/responses/NotFound")
     * )
     *
     * @param int $id
     * @param CustomerRepository $customerRepository
     *
     * @return Response
     * @throws InvalidArgumentException
     */
    public function show(int $id, CustomerRepository $customerRepository)
    {
        $customer = $customerRepository->findCustomerByUser($id, $this->getUser()->getId());

        if ($customer === []) {
            $data = [
                'status' => Response::HTTP_NOT_FOUND,
                'message' => "Ce client n'existe pas."
            ];

            return new JsonResponse($data, Response::HTTP_NOT_FOUND);
        }

        $data = $this->encacher->cacheShow($customer);

        return new Response($data, Response::HTTP_OK, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("/customers", name="add_customer", methods={"POST"})
     *
     * @OA\Post(
     *     path="/customers",
     *     security={"bearer"},
     *     tags={"Customer"},
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *               required={"email", "firstname", "lastName", "adress", "postCode", "city"},
     *               ref="#/components/schemas/CustomerEdit"
     *          )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Création d'un client",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Le client a été ajouté avec succès !")
     *          )
     *     ),
     *     @OA\Response(response="404", ref="#/components/responses/NotFound")
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function new(Request $request)
    {
        $message = $this->messageGenerator->generateForEdit($request);
        if($message) {
            return new JsonResponse($message['message'], $message['http_response']);
        }

        $customer = $this->serializer->deserialize($request->getContent(), Customer::class, 'json');
        $customer->setUser($this->getUser());

        $errors = $this->validator->validate($customer);
        if (count($errors)) {
            $data = $this->serializer->serialize($errors, 'json');

            return new Response($data, Response::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE, [
                'Content-Type' => 'application/json'
            ]);
        }

        $this->em->persist($customer);
        $this->em->flush();

        return new JsonResponse('Le client a été ajouté avec succès !', Response::HTTP_CREATED);
    }

    /**
     * @Route("/customers/{id<\d+>}", name="update_customer", methods={"PUT"})
     *
     * @OA\Put(
     *     path="/customers/{id}",
     *     security={"bearer"},
     *     tags={"Customer"},
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/CustomerEdit")
     *     ),
     *     @OA\Parameter(ref="#/components/parameters/id"),
     *     @OA\Response(
     *          response="200",
     *          description="Modification des informations client",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Le client a été modifié avec succès !")
     *          )
     *     ),
     *     @OA\Response(response="404", ref="#/components/responses/NotFound")
     * )
     *
     * @param Request $request
     * @param Customer $customer
     * @param SetCustomer $setCustomer
     *
     * @return Response
     */
    public function update(Request $request,Customer $customer, SetCustomer $setCustomer)
    {
        $message = $this->messageGenerator->generateForEdit($request, $customer, $this->getUser());
        if($message) {
            return new JsonResponse($message['message'], $message['http_response']);
        }

        $setCustomer->set($request, $customer);

        $errors = $this->validator->validate($customer);
        if (count($errors)) {
            $data = $this->serializer->serialize($errors, 'json');

            return new Response($data, Response::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE, [
                'Content-Type' => 'application/json'
            ]);
        }

        $this->em->flush();

        return new JsonResponse('Le client a été modifié avec succès !', Response::HTTP_CREATED);
    }

    /**
     * @Route("/customers/{id<\d+>}", name="delete_customer", methods={"DELETE"})
     *
     * @OA\Delete(
     *     path="/customers/{id}",
     *     security={"bearer"},
     *     tags={"Customer"},
     *     @OA\Parameter(ref="#/components/parameters/id"),
     *     @OA\Response(
     *          response="200",
     *          description="Supression d'un client",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Le client a été supprimé avec succès !")
     *          )
     *     ),
     *     @OA\Response(response="404", ref="#/components/responses/NotFound")
     * )
     *
     * @param Customer $customer
     *
     * @return Response
     */
    public function delete(Customer $customer)
    {
        if ($customer->getUser() !== $this->getUser()) {
            $data = [
                'status' => Response::HTTP_OK,
                'message' => "Ce client n'existe pas pour cet utilisateur."
            ];

            return new JsonResponse($data, Response::HTTP_OK);
        }

        $this->em->remove($customer);

        $this->em->flush();

        return new JsonResponse('Le client a été supprimé avec succès !', Response::HTTP_RESET_CONTENT);
    }
}
