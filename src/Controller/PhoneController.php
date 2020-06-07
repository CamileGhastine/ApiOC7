<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
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
     */
    public function index(Request $request, PhoneRepository $phoneRepository, SerializerInterface $serializer)
    {
        $page = (int)$request->query->get('page') > 1 ? (int)$request->query->get('page') : 1 ;
        $maxResult = strtolower($request->query->get('page')) === 'all' ? null : $this->getParameter('paginator.maxResult');
        $brand = $request->query->get('brand') ? $request->query->get('brand') : null;


        $phone = ($phoneRepository->findPhonePaginated($page, $maxResult, $brand));


        $data = $serializer->serialize($phone->getIterator(), 'json');

        return new Response($data, Response::HTTP_OK, [
            'Content-Type' => 'application/json'
        ]);
    }
}
