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
        return $this->redirect('https://127.0.0.1:8000/api/v1/doc/index.html');
    }
}
