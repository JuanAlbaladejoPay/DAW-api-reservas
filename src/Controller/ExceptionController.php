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

        // Manejar específicamente NotFoundHttpException
        if ($exception instanceof NotFoundHttpException) {
            $response->setData(['error' => 'Not Found']);
            $response->setStatusCode(404);
        }
        // Manejar específicamente AccessDeniedHttpException
        elseif ($exception instanceof AccessDeniedHttpException) {
            $response->setData(['error' => 'Access Denied']);
            $response->setStatusCode(403);
        }
        // Manejar otros HttpExceptionInterface y forzar 404 para rutas no encontradas
        elseif ($exception instanceof HttpExceptionInterface) {
            // Si es una HttpExceptionInterface pero no es un NotFoundHttpException ni un AccessDeniedHttpException
            $statusCode = $exception->getStatusCode();
            if ($statusCode === 406) {
                $response->setData(['error' => 'Not Found']);
                $response->setStatusCode(404);
            } else {
                $response->setData(['error' => $exception->getMessage()]);
                $response->setStatusCode($statusCode);
            }
        }
        // Manejar excepciones generales
        else {
            $response->setData(['error' => 'Internal Server Error']);
            $response->setStatusCode(500);
        }

        $event->setResponse($response);
    }
}


