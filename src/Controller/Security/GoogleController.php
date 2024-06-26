<?php

namespace App\Controller\Security;

use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;


#[Route('/api', name: 'api_')]
class GoogleController extends AbstractController {
  private $JWTManager;
  private $userService;

  public function __construct(JWTTokenManagerInterface $JWTManager, UserService $userService) {
    $this->JWTManager = $JWTManager;
    $this->userService = $userService;
  }

  #[Route('/login-google', name: 'login-google', methods: 'POST')]
  public function loginGoogle(Request $request, EntityManagerInterface $entityManager): JsonResponse {
    $data = json_decode($request->getContent(), true);
    $token = $data['token'];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/oauth2/v1/userinfo?access_token={$token}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $server_output = curl_exec($ch);
    curl_close($ch);
    $googleResponse = json_decode($server_output, true);

    // Comprobamos si el usuario YA EXISTE en la BD, cogiendo el email de la petición de google
    $email = $googleResponse['email'];
    $name = $googleResponse['given_name'];
    $surname = $googleResponse['family_name'];
    $picture = $googleResponse['picture'];
//    $phone = $googleResponse['phone'];
    $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

    // Si existe, creamos el token para ese usuario
    if ($user) {
      // El token JWT se genera a partir de la información del usuario, no de su contraseña. Por lo tanto, podemos generar un token JWT para un usuario incluso si no tiene una contraseña
      $tokenAPI = $this->JWTManager->create($user);
      return $this->json(['ok' => 'Has iniciado sesión correctamente',
        'results' => [
          'email' => $email,
          'token' => $tokenAPI,
          'name' => $name,
          'surname' => $surname,
          'phone' => null,
          'id' => $user->getId(),
          'picture' => $picture,
          'isAdmin' => $this->userService->isAdmin()
        ]
      ]);
    }

    // En caso de que no exista, lo insertamos en la BD y le generamos un token para que pueda hacer ya peticiones

    $user = new User();
    $user->setEmail($email);
    $user->setRoles(['ROLE_USER']);
    $user->setNombre($name);
    $user->setApellidos($surname);
    $user->setVerified(true);

    $entityManager->persist($user);
    $entityManager->flush();

    $token = $this->JWTManager->create($user);
    return $this->json(['ok' => 'Te has registrado correctamente',
      'results' => [
        'email' => $email,
        'token' => $token,
        'name' => $name,
        'picture' => $googleResponse['picture'],
        'surname' => $surname,
        'phone' => null,
        'id' => $user->getId(),
        'isAdmin' => $this->userService->isAdmin()
      ]
    ]);
  }
}