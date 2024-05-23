<?php

namespace App\Controller\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

class AuthenticationFailureHandler implements AuthenticationFailureHandlerInterface {
  public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse {
    return new JsonResponse([
      'error' => 'Error de autenticación: ' . $exception->getMessageKey()
    ], 401);
  }
}