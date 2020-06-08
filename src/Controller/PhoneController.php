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
     *
     * @param Request $request
     * @param PhoneRepository $phoneRepository
     * @param SerializerInterface $serializer
     * @return Response
     * @throws \Exception
     */
    public function index(Request $request, PhoneRepository $phoneRepository, SerializerInterface $serializer)
    {
        $page = (int)$request->query->get('page') > 1 ? (int)$request->query->get('page') : 1 ;
        $maxResult = strtolower($request->query->get('page')) === 'all' ? null : $this->getParameter('paginator.maxResult');
        $brand = $request->query->get('brand') ? $request->query->get('brand') : null;
        $price = [0, 10000];

        // regex should be of type (minPrice) or (minPrice, maxPrice)
        // $price =array (minPrice, maxPrice)
        if ($request->query->get('price') && preg_match('#(^\(\d+( )?(,( )?\d+)?\))$#', $request->query->get('price'))) {
            $price = preg_split('/[\s,]+/', substr($request->query->get('price'), 1, -1));
            if (count($price) == 1) $price[1] = 10000;
        }

        $phone = ($phoneRepository->findPhonePaginated($page, $maxResult, $brand, $price));

        $data = $serializer->serialize($phone->getIterator(), 'json');

        return new Response($data, Response::HTTP_OK, [
            'Content-Type' => 'application/json'
        ]);
    }
}
