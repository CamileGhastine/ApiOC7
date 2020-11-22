<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\SetUser;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class SecurityController
 *
 * @Route("/api")
 *
 * @package App\Controller
 */
class SecurityController extends AbstractController
{
    private $serializer;
    private $validator;
    private $em;
    private $passwordEncoder;

    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/register", name="register", methods={"post"})
     *
     * @param Request $request
     * @param SetUser $setUser
     *
     * @return JsonResponse|Response
     */
    public function register(Request $request, SetUser $setUser)
    {
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');

        if (!$user->getUsername() || !$user->getPassword()) {
            $data = [
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'Les clefs username et password sont obligatoires !'
            ];

            return new JsonResponse($data, Response::HTTP_UNPROCESSABLE_ENTITY, ['Content-Type' => 'application/json'] );
        }

        $errors = $setUser->set($user);

        if(count($errors)) {
            $data = $this->serializer->serialize($errors, 'json');

            return new Response($data, Response::HTTP_UNPROCESSABLE_ENTITY, ['Content-Type' => 'application/json'] );
        }

        $this->em->persist($user);
        $this->em->flush();

        $data =[
            'status' => Response::HTTP_CREATED,
            'message' => 'L\'utilisateur a été enregistré avec succès !'
        ];

        return new JsonResponse($data, Response::HTTP_CREATED, ['Content-Type' => 'application/json'] );
    }
}
