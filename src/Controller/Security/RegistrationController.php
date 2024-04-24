<?php

namespace App\Controller\Security; // He añadio \Security

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
// --> EMAIL
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;


#[Route('/api', name: 'api_')]
class RegistrationController extends AbstractController {
  private $JWTManager;

  public function __construct(JWTTokenManagerInterface $JWTManager) {
    $this->JWTManager = $JWTManager;
  }

  #[Route('/register', name: 'register', methods: 'POST')]
  public function index(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse {
    $user = new User();
    $dataBody = json_decode($request->getContent(), true); // Obtenemos los datos de la petición en un array asociativo (al pasar TRUE como parámetro)

    $email = $dataBody['email'];
    $hashedPassword = $passwordHasher->hashPassword($user, $dataBody['password']); // Con esto hasheamos la password
    $name = $dataBody['name'];
    $surname = $dataBody['surname'];
    $phone = $dataBody['phone'];

    $user->setEmail($email);
    $user->setRoles(['ROLE_USER']); // Por defecto, todos los usuarios son ROLE_USER (se podría cambiar en el formulario de registro)
    $user->setPassword($hashedPassword);
    $user->setNombre($name);
    $user->setApellidos($surname);
    $user->setTelefono($phone);

    $entityManager->persist($user);
    $entityManager->flush();

    $token = $this->JWTManager->create($user);

    // --> Enviar EMAIL (lo dejo comentado para descomentar solo cuando haga falta enviar un email de verdad)
    /* try {
      $transport = Transport::fromDsn($_ENV['MAILER_DSN']); // Tenemos que poner en .env esta variable: MAILER_DSN=smtp://letsmove.murcia@gmail.com:PASSWORD@smtp.gmail.com:587 (Password está en el drive)
      $mailer = new Mailer($transport);

      // Crear el correo electrónico
      $email = (new Email())
        ->from('letsmove.murcia@gmail.com')
        ->to($user->getEmail())
        ->subject('¡Bienvenido a LetsMove!')
        ->html('
            <h1>¡Bienvenido a LetsMove!</h1>
            <p>¡Gracias por registrarte en nuestro sitio!</p>
            <p>Estamos emocionados de tenerte con nosotros. Aquí podrás encontrar las mejores actividades deportivas en tu área.</p>
            <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
            <p>¡Disfruta de LetsMove!</p>
        ');

      // Enviar el correo electrónico
      $mailer->send($email);
    } catch (\Exception $e) {
      return $this->json(['error' => "Ha ocurrido un error al enviar el email $e"], 500);
    } */

    return $this->json(['ok' => 'Te has registrado correctamente', 'token' => $token]);
  }
}
