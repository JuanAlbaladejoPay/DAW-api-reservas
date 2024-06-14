<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionController implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $response = new JsonResponse();

        
        if ($exception instanceof NotFoundHttpException) {
            $response->setData(['error' => 'Not Found']);
            $response->setStatusCode(404);
        }
        
        elseif ($exception instanceof AccessDeniedHttpException) {
            $response->setData(['error' => 'Access Denied']);
            $response->setStatusCode(403);
        }
        
        elseif ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            if ($statusCode === 406) {
                $response->setData(['error' => 'Not Found']);
                $response->setStatusCode(404);
            } else {
                $response->setData(['error' => $exception->getMessage()]);
                $response->setStatusCode($statusCode);
            }
        }
        else {
            $response->setData(['error' => 'Internal Server Error']);
            $response->setStatusCode(500);
        }

        $event->setResponse($response);
    }
}


