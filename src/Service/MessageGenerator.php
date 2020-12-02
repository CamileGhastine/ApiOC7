<?php


namespace App\Service;


use Symfony\Component\HttpFoundation\Response;

class MessageGenerator
{
    public function generate($parameters)
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
                'status' => Response::HTTP_OK,
                'mesnnsage' => "Aucun téléphone trouvé pour ces critères de recherche."
            ];
            $message['http_response'] = Response::HTTP_OK;

            return $message;
        }
        return false;
    }
}