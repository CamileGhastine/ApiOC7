<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Annotations as OA;

class Controller extends AbstractController
{
    /**
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID de la ressource",
     *     required = true,
     *     @OA\Schema(type="integer")
     * )
     * @OA\Response(
     *     response="NotFound",
     *     description="La ressource n'existe pas.",
     *     @OA\JsonContent(
     *          @OA\Property(property="message", type="string", example="La ressource n'existe pas.")
     *     )
     * )
     * @OA\SecurityScheme(bearerFormat="JWT", type="apiKey", securityScheme="bearer")
     */
    public function index()
    {
        return $this->render('/index.html.twig', [
            'controller_name' => 'Controller',
        ]);
    }
}
