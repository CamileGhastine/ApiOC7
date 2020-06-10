<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use App\Service\ParametersRepositoryPreparator;
use Doctrine\ORM\EntityManagerInterface;
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
 * Class PhoneController
 * @Route("/api")
 * @package App\Controller
 */
class Controller extends AbstractController
{
    private $serializer;


    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @Route("/phones", name="list_phone", methods={"GET"})
     * @Route("/customers", name="list_customer", methods={"GET"})
     *
     * @param Request $request
     * @param ParametersRepositoryPreparator $preparator
     *
     * @return Response
     *
     */
    public function index(Request $request, ParametersRepositoryPreparator $preparator)
    {
        $entity = ucfirst(str_replace('list_', '',$request->attributes->get('_route')));
        $actionPrepareParameters = 'prepareParameters'.$entity;
        $repository = $this->getDoctrine()->getRepository('App\Entity\\'.$entity);
        $actionFind = 'find'.$entity.'Paginated';

        $parameters = $preparator->$actionPrepareParameters($request, $this->getParameter('paginator.maxResult'));

        // if $parameters have message errors
        if (isset($parameters['error'])) {
            $data = [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => $parameters['error']
            ];

            return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
        }

        $entity = $repository->$actionFind($parameters);

        $data = $this->serializer->serialize($entity->getIterator(), 'json', SerializationContext::create()->setGroups(['list']));

        return new Response($data, Response::HTTP_OK, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("/phones/{id<\d+>}", name="show_phone", methods={"GET"})
     * @Route("/customers/{id<\d+>}", name="show_customer", methods={"GET"})
     *
     * @param $id
     * @param Request $request
     *
     * @return Response
     */
    public function show($id, Request $request)
    {
        $class = 'App\Entity\\'.ucfirst(str_replace('show_', '',$request->attributes->get('_route')));

        $repository = $this->getDoctrine()->getRepository($class);

        $entity = $repository->find($id);

        $data = $this->serializer->serialize($entity, 'json', SerializationContext::create()->setGroups(['detail']));

        return new Response($data, Response::HTTP_OK, [
            'Content-Type' => 'application/json'
        ]);
    }
}
