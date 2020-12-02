<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use App\Service\DataPaginator;
use App\Service\ParametersRepositoryPreparator;
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
use OpenApi\Annotations as OA;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

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
     * @param PhoneRepository $phoneRepository
     * @param ParametersRepositoryPreparator $preparator
     * @param DataPaginator $dataPaginator
     *
     * @return Response
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function index(Request $request, PhoneRepository $phoneRepository, ParametersRepositoryPreparator $preparator, DataPaginator $dataPaginator, CacheInterface $cache)
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

        $cacheName = 'cachePhonesList'.$request->query->get('page').$request->query->get('brand').$request->query->get('price');

        $data = $cache->get($cacheName, function (ItemInterface $item) use ($parameters, $dataPaginator, $phoneRepository){
            $item->expiresAfter(3600);
            $data = $dataPaginator->paginate($phoneRepository->findPhonePaginated($parameters)->getIterator(), $parameters);

            return $this->serializer->serialize($data, 'json', SerializationContext::create()->setGroups(['list']));
        });



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
     *
     * @return Response
     */
    public function show(Phone $phone, CacheInterface $cache)
    {
        $data = $cache->get('cachePhone', function(ItemInterface $item) use ($phone) {
            $item->expiresAfter(3600);

            return $this->serializer->serialize($phone, 'json', SerializationContext::create()->setGroups(['detail']));
        });

        return new Response($data, Response::HTTP_OK, ['Content-TYpe' => 'application/json']);
    }
}
