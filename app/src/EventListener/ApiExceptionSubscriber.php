<?php

namespace App\EventListener;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    private $isDebug;

    private LoggerInterface $logger;

    public function __construct(bool $isDebug, LoggerInterface $logger)
    {
        $this->isDebug = $isDebug;
        $this->logger = $logger;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
        } else {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        if (Response::HTTP_INTERNAL_SERVER_ERROR === $statusCode) {
            $message = 'Internal server error';
            $this->log($exception);
        } else {
            $message = $exception->getMessage();
        }

        $data = [
            'message' => $message,
        ];

        $response = new JsonResponse(
            $data,
            $statusCode
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

    private function log(Throwable $exception)
    {
        $log = [
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'called' => [
                'file' => $exception->getTrace()[0]['file'],
                'line' => $exception->getTrace()[0]['line'],
            ],
            'occurred' => [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ],
        ];

        if ($exception->getPrevious() instanceof Exception) {
            $log += [
                'previous' => [
                    'message' => $exception->getPrevious()->getMessage(),
                    'exception' => get_class($exception->getPrevious()),
                    'file' => $exception->getPrevious()->getFile(),
                    'line' => $exception->getPrevious()->getLine(),
                ],
            ];
        }

        $this->logger->error(json_encode($log));
    }
}
