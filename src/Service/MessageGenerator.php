<?php


namespace App\Service;


use App\Entity\Customer;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MessageGenerator
 * @package App\Service
 */
class MessageGenerator
{

    /**
     * @param $parameters
     *
     * @return bool
     */
    public function generateForIndex($parameters)
    {
        // if $parameters have message errors
        if (isset($parameters['error'])) {
            $message['message'] = [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => $parameters['error']
            ];
            $message['http_response'] = Response::HTTP_BAD_REQUEST;

            return $message;
        }

        // No resource found
        if ((int)$parameters['count'] === 0) {
            $message['message'] = [
                'status' => Response::HTTP_NOT_FOUND,
                'message' => isset($parameters['price'])
                    ? "Aucun téléphone trouvé pour ces critères de recherche."
                    : "Aucun client pour cet utilisateur."
            ];
            $message['http_response'] = Response::HTTP_NOT_FOUND;

            return $message;
        }
        return false;
    }

    /**
     * @param $request
     * @param $customer
     * @param $User
     *
     * @return bool
     */
    public function generateForEdit(Request $request, ?Customer $customer = null, ?User $User = null)
    {
        if ($request->getContent() === "") {
            $message['message'] = [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => $customer
                    ? "Aucune information entrée pour la modification."
                    : "Le courriel, le nom, le prénom, l'adresse, le code postal et la ville au format json sont obligatoires !"
            ];
            $message['http_response'] = Response::HTTP_BAD_REQUEST;

            return $message;
        }

        if ($customer && $customer->getUser() !== $User) {
            $message['message'] = [
                'status' => Response::HTTP_NOT_FOUND,
                'message' => "Ce client n'existe pas pour cet utilisateur."
            ];
            $message['http_response'] = Response::HTTP_NOT_FOUND;

            return $message;
        }
        return false;
    }

    /**
     * @param Request $request
     * @param User $user
     *
     * @return bool
     */
    public function generateForRegister(Request $request, User $user)
    {
        if ($request->getContent() === "") {
            $message['message'] = [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => "Les clefs username et password au format json sont obligatoires !"
            ];
            $message['http_response'] = Response::HTTP_BAD_REQUEST;

            return $message;
        }

        if (!$user->getUsername() || !$user->getPassword()) {
            $message['message'] = [
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => "Les clefs username et password au format json sont obligatoires !"
            ];
            $message['http_response'] = Response::HTTP_UNPROCESSABLE_ENTITY;

            return $message;
        }

        return false;
    }
}