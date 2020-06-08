<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event)
    {
        $data =[
            'status' => 404,
            'message' => 'La route demandée n\'existe pas.'
        ];

        if (!$event->getThrowable()->getPrevious()) {
            $data =[
                'status' => 404,
                'message' => 'La ressource n\'existe pas.'
            ];
        }

        $response = new JsonResponse($data);

        $event->setResponse($response);
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.exception' => 'onKernelException',
        ];
    }
}
