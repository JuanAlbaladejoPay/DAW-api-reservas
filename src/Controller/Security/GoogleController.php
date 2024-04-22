<?php

namespace App\Controller\Security;
// He añadio \Security

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Google_Client;

// composer require google/apiclient:"^2.0"

#[Route('/api', name: 'api_')]
class GoogleController extends AbstractController {
  private $JWTManager;

  public function __construct(JWTTokenManagerInterface $JWTManager) {
    $this->JWTManager = $JWTManager;
  }

  #[Route('/login-google', name: 'login-google', methods: 'POST')]
  public function loginGoogle(Request $request, EntityManagerInterface $entityManager): JsonResponse {
    $data = json_decode($request->getContent(), true);
    $token = $data['token'];

    /*    $client = new Google_Client(['client_id' => '670850869047-ctj9q23rejpe18q69nlg1afjn28elbpu.apps.googleusercontent.com']); // Aquí habría que guardar una variable de entorno con CLIENT_ID, ese client_id lo he sacado de Google Cloud (siguiendo el enlace del drive sale todo)
        $isVerified = $client->verifyIdToken($token);

        if ($isVerified) {*/
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
      return $this->json(['ok' => 'Has iniciado sesión correctamente', 'results' => ['email' => $email, 'token' => $tokenAPI, 'name' => $name, 'picture' => $picture]]);
    }

    // En caso de que no exista, lo insertamos en la BD y le generamos un token para que pueda hacer ya peticiones
    if (!$user) {
      $user = new User();
      $user->setEmail($email);
      $user->setRoles(['ROLE_USER']);
      $user->setNombre($name);
      $user->setApellidos($surname);
//      $user->setTelefono($phone); // Lo mismo con teléfono
//      $user->setPassword("") // Hay que ver cómo tratar lo de contraseñas cuando te registras con google

      $entityManager->persist($user);
      $entityManager->flush();

      $token = $this->JWTManager->create($user);
      return $this->json(['ok' => 'Te has registrado correctamente', 'results' => ['email' => $email, 'token' => $token, 'name' => $name, 'picture' => $googleResponse['picture']]]);
    }
    return $this->json(['error' => 'Ha ocurrido un error']);
  }
}
/* TODO:
- Intentar verificar que el token recibido del cliente es correcto (lo que ahora mismo está comentado (el if isVerfied englobaba el resto de código pero el método verifyIdToken siempre devuelve false)
- Añadir el campo de teléfono a la entidad User cuando quiera registrarse con Google (hay que ver cómo hacerlo)
- Añadir el campo de password a la entidad User cuando quiera registrarse con Google (hay que ver cómo hacerlo)

*/