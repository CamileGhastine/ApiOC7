<?php

namespace App\EventSubscriber;

use ErrorException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use JMS\Serializer\Exception\RuntimeException;

class ExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if (!($exception instanceof NotFoundHttpException) &&
            !($exception instanceof ErrorException) &&
            !($exception instanceof MethodNotAllowedHttpException) &&
            !($exception instanceof BadRequestHttpException) &&
            !($exception instanceof RuntimeException)) {
            return ;
        }
        if ($exception instanceof NotFoundHttpException) {
            $data = $this->getDataForNotFoundHttpException($exception);
        }

        if ($exception instanceof ErrorException  || $exception instanceof MethodNotAllowedHttpException || $exception instanceof RuntimeException) {
            $data = $this->getDataOtherException($exception);
        }

        if ($exception instanceof BadRequestHttpException) {
            $data = [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => 'Le format saisi n\'est pas un format json valide !'
            ];
        }

        $response = new JsonResponse($data, $data['status']);

        $event->setResponse($response);
    }

    /**
     * @param $exception
     * @return array|void
     */
    private function getDataForNotFoundHttpException($exception)
    {
        if (stripos($exception->getMessage(), 'No route found') !== false) {
            return [
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'La route demandée n\'existe pas.'
            ];
        }

        if (stripos($exception->getMessage(), 'object not found by the @ParamConverter annotation') !== false) {
            return [
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'La ressource n\'existe pas.'
            ];
        }

        return;
    }

    /**
     * @param $exception
     *
     * @return array
     */
    private function getDataOtherException($exception)
    {
        if ($exception instanceof ErrorException || $exception instanceof RuntimeException) {
            return [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => 'Le format saisi n\'est pas un format json valide !'
            ];
        }

        if ($exception instanceof MethodNotAllowedHttpException  && stripos($exception->getMessage(), 'Method Not Allowed') !== false) {
            return [
                'status' => Response::HTTP_METHOD_NOT_ALLOWED,
                'message' => 'Pas de route trouvée pour cette méthode HTTP !'
            ];
        }

        return $data =[
            'status' => Response::HTTP_NOT_FOUND,
            'message' => 'Erreur !!!'
        ];
    }

    /**
     * @return array|string[]
     */
    public static function getSubscribedEvents()
    {
        return [
            'kernel.exception' => 'onKernelException',
        ];
    }
}
