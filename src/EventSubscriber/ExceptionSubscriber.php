<?php

namespace App\EventSubscriber;

use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if (!($exception instanceof NotFoundHttpException) && !($exception instanceof RuntimeException)) {
            return ;
        }

        $data =[
            'status' => Response::HTTP_NOT_FOUND,
            'message' => 'Erreur !!!'
        ];

        if ($exception instanceof NotFoundHttpException) {
            $data = $this->getDataForNotFoundHttpException($exception);
        }

        if ($exception instanceof RuntimeException && stripos($exception->getMessage(), 'Could not decode JSON') !== false) {
            $data =[
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Le format saisi n\'est pas un format json valide !'
            ];
        }

        $response = new JsonResponse($data, Response::HTTP_NOT_FOUND);

        $event->setResponse($response);
    }

    /**
     * @param $exception
     *
     * @return array
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
