<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use App\Service\ParametersRepositoryPreparator;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PhoneController
 * @package App\Controller
 * @Route("/api")
 */
class PhoneController extends AbstractController
{
    /**
     * @Route("/phones", name="list_phone", methods={"GET"})
     *
     * @param Request $request
     * @param PhoneRepository $phoneRepository
     * @param SerializerInterface $serializer
     * @return Response
     * @throws \Exception
     */
    public function index(Request $request, PhoneRepository $phoneRepository, SerializerInterface $serializer,ParametersRepositoryPreparator $preparator)
    {
        $parameters = $preparator->preparePhone($request, $this->getParameter('paginator.maxResult'));


        $phone = ($phoneRepository->findPhonePaginated($parameters));

        $data = $serializer->serialize($phone->getIterator(), 'json');

//        dd(($data));

        return new Response($data, Response::HTTP_OK, [
            'Content-Type' => 'application/json'
        ]);
    }
}
