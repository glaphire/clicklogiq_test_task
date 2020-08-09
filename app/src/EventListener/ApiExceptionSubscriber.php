<?php

namespace App\EventListener;

use Pagerfanta\Exception\PagerfantaException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event)
    {
        $e = $event->getThrowable();
        $code = 500;

        if ($e instanceof HttpException || $e instanceof PagerfantaException) {
            $code = $e->getStatusCode();
        }

        $data = [
            'error' => $e->getMessage(),
            'code' => $code,
        ];

        $response = new JsonResponse(
            $data,
            $code,
        );

        $response->headers->set('Content-Type', 'application/json');

        $event->setResponse($response);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
