<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use App\Service\Encacher;
use App\Service\ParametersRepositoryPreparator;
use App\Service\SetCustomer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use JMS\Serializer\SerializerInterface;
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

    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $em)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->em = $em;
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
     * @param Encacher $encacher
     * @return JsonResponse|Response
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function index(Request $request, ParametersRepositoryPreparator $preparator, Encacher $encacher)
    {
        $parameters = $preparator->prepareParametersCustomer($request, $this->getUser()->getId(), $this->getParameter('paginator.maxResult'));

        // if $page have message error
        if (isset($parameters['error'])) {
            $data = [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => $parameters['error']
            ];

            return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
        }

        if ((int)$parameters['count'] === 0) {
            $data = [
                'status' => Response::HTTP_OK,
                'message' => "Aucun client pour cet utilisateur."
            ];

            return new JsonResponse($data, Response::HTTP_OK);
        }

        $data = $encacher->cacheIndex($request, $parameters, $this->getUser()->getId());

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
     * @param Encacher $encacher
     * @return Response
     */
    public function show(int $id, CustomerRepository $customerRepository, Encacher $encacher)
    {
        $customer = $customerRepository->findCustomerByUser($id, $this->getUser()->getId());

        $data = $encacher->cacheShow($customer);

        if ($data === "[]") {
            $data = [
                'status' => Response::HTTP_OK,
                'message' => "Ce client n'existe pas pour cet utilisateur."
            ];

            return new JsonResponse($data, Response::HTTP_OK);
        }

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
     *
     * @return Response
     */
    public function new(Request $request)
    {
        if ($request->getContent() === "") {
            $data = [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => "Le courriel, le nom, le prénom, l'adresse, le code postal et la ville au format json sont obligatoires !"
            ];

            return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
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

        return new Response('Le client a été ajouté avec succès !', Response::HTTP_CREATED, [
            'Content-Type' => 'application/json'
        ]);
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
     * @param Customer $customer
     * @param Request $request
     * @param SetCustomer $setCustomer
     *
     * @return Response
     */
    public function update(Customer $customer, Request $request, SetCustomer $setCustomer)
    {
        if ($request->getContent() === "") {
            $data = [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => "Aucune information entrée pour la modification."
            ];

            return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
        }

        if ($customer->getUser() !== $this->getUser()) {
            $data = [
                'status' => Response::HTTP_OK,
                'message' => "Ce client n'existe pas pour cet utilisateur."
            ];

            return new JsonResponse($data, Response::HTTP_OK);
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

        return new Response('Le client a été modifié avec succès !', Response::HTTP_CREATED, [
            'Content-Type' => 'application/json'
        ]);
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

        return new Response('Le client a été supprimé avec succès !', Response::HTTP_RESET_CONTENT, [
            'Content-Type' => 'application/json'
        ]);
    }
}
