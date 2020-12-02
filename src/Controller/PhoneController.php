<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Service\Encacher;
use App\Service\ParametersRepositoryPreparator;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use JMS\Serializer\SerializerInterface;
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

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
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
     * @param Encacher $encacher
     * @return Response
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function index(Request $request, ParametersRepositoryPreparator $preparator, Encacher $encacher)
    {
        $parameters = $preparator->prepareParametersPhone($request, $this->getParameter('paginator.maxResult'));

        // if $parameters have message errors
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
                'message' => "Aucun téléphone trouvé pour ces critères de recherche."
            ];

            return new JsonResponse($data, Response::HTTP_OK);
        }


        $data = $encacher->cacheIndex($request, $parameters);

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
     * @param Encacher $encacher
     * @return Response
     *
     */
    public function show(Phone $phone, Encacher $encacher)
    {
        $data = $encacher->cacheShow($phone);

        return new Response($data, Response::HTTP_OK, ['Content-TYpe' => 'application/json']);
    }
}
