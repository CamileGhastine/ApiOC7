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
use OpenApi\Annotations as OA;
/**
 * Class SecurityController
 *
 * @Route("/api/v1")
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
     * @OA\Post(
     *     path="/register",
     *     tags={"Security"},
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *               required={"username", "password"},
     *               ref="#/components/schemas/User"
     *          )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Inscription d'un utilisateur",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="L'utilisateur a été enregistré avec succès !")
     *          )
     *     ),
     * )
     *
     * @param Request $request
     * @param SetUser $setUser
     *
     * @return JsonResponse|Response
     */
    public function register(Request $request, SetUser $setUser)
    {
        if ($request->getContent() === "") {
            $data = [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => "Les clefs username et password au format json sont obligatoires !"
            ];

            return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
        }

        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');

        if (!$user->getUsername() || !$user->getPassword()) {
            $data = [
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'Les clefs username et password sont obligatoires !'
            ];

            return new JsonResponse($data, Response::HTTP_UNPROCESSABLE_ENTITY, ['Content-Type' => 'application/json']);
        }

        $errors = $setUser->set($user);

        if (count($errors)) {
            $data = $this->serializer->serialize($errors, 'json');

            return new Response($data, Response::HTTP_UNPROCESSABLE_ENTITY, ['Content-Type' => 'application/json']);
        }

        $this->em->persist($user);
        $this->em->flush();

        $data =[
            'status' => Response::HTTP_CREATED,
            'message' => 'L\'utilisateur a été enregistré avec succès !'
        ];

        return new JsonResponse($data, Response::HTTP_CREATED, ['Content-Type' => 'application/json']);
    }

    /**
     * @OA\Post(
     *     path="/login_check",
     *     tags={"Security"},
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *               required={"username", "password"},
     *               ref="#/components/schemas/User"
     *          )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Inscription d'un utilisateur",
     *          @OA\JsonContent(
     *              @OA\Property(property="token", type="string",
     *              example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2MDY4NTUyNzEsImV4cCI6MTYwNjg1ODg3MSwicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoiQ2xpZW50LTEifQ.SVGKQhz-r9JLScuS3fzopa-oWKPDbv51ER9x_yILmvnLQcxX8DI2Yr75pR4iBR598GQtqkAYEsz1VO42CZICwXbgpibn9FrVzsxsyMm3AN4gJmlLaLVqoYL7aY1VSjPR5wMVoQQrKr9WqPBnGv4uo_CqzQiXByi8BBUYEtOg4pjcfNTQPnacjRwVa0eW4krgCJHKJfYX8v64oixk5pPUkHuHxJkdcQjqGzXQP5cnuS8jqhrOuEGVIKjqmLPfgXsPiMUXwfVigztzlF5-qpjAtKPFunkak15kh68Hu6Fif73UyXKK-aYqZ2_yMBlRsEpFav_AXtvFinpgZ2ZvmQXffWDe_f1Bm94ht_jYCz4zhr7n3UGRAsyyHAn7CmRopbG6ET4R-DVbGB4HHi9OMUeNTtSS02eUzPOhVNeVqzzh4mClapARvId1lbCcHuFjHuvRr-CMzE0nfkiHiRsXc-JHzHyAx9glnrOUre76-xUFH78NCm585gUv9HebCuI42n0neg_4XExDQ7gsRzaCNr-12JOrZowz66DyGw27QCrHyByRS1XKiVdrI2njwxbc0apkAAcsxBghzk9pDNcxYX69nfMK0TnePnD89kIZlBJz32i4TfSTdKB5tjc3ajiLGVKlukH1Oz1Q9H1gt6CXWcny7wpMXXgHRuumzkAMnnnxj0Q")
     *          )
     *     ),
     * )
     */
    public function login()
    {
//        Manage by JWT
    }
}
