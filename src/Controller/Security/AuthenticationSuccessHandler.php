<?php

namespace App\Controller\Security;

use App\Service\UserService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface {
  private $JWTManager;
  private $userService;

  public function __construct(JWTTokenManagerInterface $JWTManager, UserService $userService) {
    $this->JWTManager = $JWTManager;
    $this->userService = $userService;
  }

  public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse {
    /** @var \App\Entity\User $user */
    $user = $token->getUser();

    if ($user->isVerified() === false) {
      return new JsonResponse([
        'error' => 'Debes verificar tu cuenta antes de iniciar sesión'
      ], 403);
    }

    $jwt = $this->JWTManager->create($user);

    return new JsonResponse([
      'ok' => 'Has iniciado sesión correctamente',
      'results' => [
        'email' => $user->getEmail(),
        'token' => $jwt,
        'name' => $user->getNombre(),
        'surname' => $user->getApellidos(),
        'phone' => $user->getTelefono(),
        'id' => $user->getId(),
        'picture' => null,
        'isAdmin' => $this->userService->isAdmin()
      ]
    ]);
  }
}
