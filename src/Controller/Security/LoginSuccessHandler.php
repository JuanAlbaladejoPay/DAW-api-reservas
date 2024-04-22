<?php

namespace App\Controller\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface {
  private $JWTManager;

  public function __construct(JWTTokenManagerInterface $JWTManager) {
    $this->JWTManager = $JWTManager;
  }

  public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse {
    /** @var \App\Entity\User $user */
    $user = $token->getUser();
    $jwt = $this->JWTManager->create($user);

    return new JsonResponse([
      'ok' => 'Has iniciado sesión correctamente',
      'results' => [
        'email' => $user->getEmail(),
        'token' => $jwt,
        'name' => $user->getNombre(),
        'picture' => null
      ]
    ]);
  }
}
