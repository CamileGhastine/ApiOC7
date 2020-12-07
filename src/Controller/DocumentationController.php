<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DocumentationController extends AbstractController
{
    /**
     * @Route("/api/v1/doc", name="api_doc", methods={"GET"})
     */
    public function index()
    {
        return $this->render('documentation/index.html.twig', [
            'controller_name' => 'DocumentationController',
        ]);
    }
}
