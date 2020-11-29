<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
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
     * @param Request $request
     * @param PhoneRepository $phoneRepository
     * @param ParametersRepositoryPreparator $preparator
     *
     * @return Response
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function index(Request $request, PhoneRepository $phoneRepository, ParametersRepositoryPreparator $preparator)
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

        $phone = $phoneRepository->findPhonePaginated($parameters);

        $data = $this->serializer->serialize($phone->getIterator(), 'json', SerializationContext::create()->setGroups(['list']));

        if($data === "[]") {
            $data = [
                'status' => Response::HTTP_NO_CONTENT,
                'message' => "Aucun téléphone trouvé pour ces critères de recherche."
            ];

            return new JsonResponse($data, Response::HTTP_NO_CONTENT);
        }

        return new Response($data, Response::HTTP_OK, ['Content-TYpe' => 'application/json']);

    }

    /**
     * @Route("/phones/{id<\d+>}", name="show_phone", methods={"GET"})
     *
     * @param Phone $phone
     *
     * @return Response
     */
    public function show(Phone $phone)
    {
        $data = $this->serializer->serialize($phone, 'json', SerializationContext::create()->setGroups(['detail']));

        return new Response($data, Response::HTTP_OK, ['Content-TYpe' => 'application/json']);
    }
}
