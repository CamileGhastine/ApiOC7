<?php

namespace App\Controller;

use App\Entity\Phone;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function index(SerializerInterface $serializer)
    {
        $phone = new Phone();
        $phone->setBrand('Samsung')
            ->setModel($phone->getBrand().' '.rand(1,9))
            ->setPrice(rand(100,999))
            ->setDescription("Un super téléphone pour téléphoner");

        $data = $serializer->serialize($phone, 'json');

        return new Response($data, Response::HTTP_OK, [
            'Content-Type' => 'application/json'
        ]);
    }
}
