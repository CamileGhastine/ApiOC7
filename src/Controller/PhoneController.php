<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Service\Encacher;
use App\Service\MessageGenerator;
use App\Service\ParametersRepositoryPreparator;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use JMS\Serializer\SerializerInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

/**
 * Class PhoneController
 * @Route("/api/v1")
 * @package App\Controller
 */
class PhoneController extends AbstractController
{
    private $serializer;
    private $encacher;

    public function __construct(SerializerInterface $serializer, Encacher $encacher)
    {
        $this->serializer = $serializer;
        $this->encacher = $encacher;
    }

    /**
     * @Route("/phones", name="list_phone", methods={"GET"})
     *
     * @OA\Get(
     *     path="/phones",
     *     security={"bearer"},
     *     tags={"Phone"},
     *     @OA\Parameter(
     *          name="page",
     *          in="query",
     *          example="/phones?page=2",
     *          required = false,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *          name="brand",
     *          in="query",
     *          example="/phones?brand=samsung",
     *          required = false,
     *          @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *          name="price",
     *          in="query",
     *          description="Syntaxe : /phones?price=[Xmin, Xmax] ou ?price=[Xmin]",
     *     example="/phones?price=[500,1000]",
     *          required = false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Liste de téléphones mobiles",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/PhonesList"))
     *     )
     * )
     *
     * @param Request $request
     * @param ParametersRepositoryPreparator $preparator
     * @param MessageGenerator $messageGenerator
     *
     * @return Response
     *
     * @throws InvalidArgumentException
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function index(Request $request, ParametersRepositoryPreparator $preparator, MessageGenerator $messageGenerator)
    {
        $parameters = $preparator->prepareParametersPhone($request, $this->getParameter('paginator.maxResult'));

        $message = $messageGenerator->generateForIndex($parameters);
        if ($message) {
            return new JsonResponse($message['message'], $message['http_response']);
        }

        $data = $this->encacher->cacheIndex($request, $parameters);

        return new Response($data, Response::HTTP_OK, ['Content-TYpe' => 'application/json']);
    }

    /**
     * @Route("/phones/{id<\d+>}", name="show_phone", methods={"GET"})
     *
     * @OA\Get(
     *     path="/phones/{id}",
     *     security={"bearer"},
     *     tags={"Phone"},
     *     @OA\Parameter(ref="#/components/parameters/id"),
     *     @OA\Response(
     *          response="200",
     *          description="Détails du téléphone",
     *          @OA\JsonContent(ref="#/components/schemas/Phone")
     *     ),
     *     @OA\Response(response="404", ref="#/components/responses/NotFound")
     * )
     *
     * @param Phone $phone
     * @return Response
     *
     * @throws InvalidArgumentException
     */
    public function show(Phone $phone)
    {
        $data = $this->encacher->cacheShow($phone);

        return new Response($data, Response::HTTP_OK, ['Content-TYpe' => 'application/json']);
    }
}
