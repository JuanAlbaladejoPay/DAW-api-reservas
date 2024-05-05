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
    $dataBody = json_decode($request->getContent(), true); // Obtenemos los datos de la petición en un array asociativo (al pasar TRUE como parámetro)

    // Comprobamos que los campos requeridos están en la petición
    $missingFieldMessage = $this->verifyRequiredFields($dataBody);
    if ($missingFieldMessage) {
      return $this->json(['error' => $missingFieldMessage], 400);
    }

    // Compruebo si ya existe este usuario en la BD (por si se registró con Google pero no puso password)
    $email = $dataBody['email'];
    $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

    // Si el usuario existía pero no tiene password (Viene de GOOGLE), le añadimos la password y el teléfono (previamente comprobamos que realmente es el mismo usuario)
    if ($user && $user->getPassword() === null) {
      $name = $dataBody['name'];
      $surname = $dataBody['surname'];
      if ($user->getNombre() !== $name || $user->getApellidos() !== $surname) {
        return $this->json(['error' => 'Ya existe un usuario con ese email']);
      }

      $hashedPassword = $passwordHasher->hashPassword($user, $dataBody['password']); // Con esto hasheamos la password
      $user->setPassword($hashedPassword);
      $user->setTelefono($dataBody['phone']);

      $entityManager->flush();

      $token = $this->JWTManager->create($user);
      return $this->json(['ok' => 'Te has registrado correctamente', 'results' => ['token' => $token, 'name' => $name, 'email' => $email, 'picture' => null]]);
    }

    // Compruebo que el usuario ya existía en la BD teniendo password (para responder que ya existe un usuario con ese email)
    if ($user && $user->getPassword() !== null) {
      return $this->json(['error' => 'Ya existe un usuario con ese email']);
    }

    // Si el usuario no existía previamente lo creamos
    $newUser = new User();
    $newUser->setEmail($email);
    $newUser->setRoles(['ROLE_USER']); // Por defecto, todos los usuarios son ROLE_USER (se podría cambiar en el formulario de registro
    $newUser->setNombre($dataBody['name']);
    $newUser->setApellidos($dataBody['surname']);
    $newUser->setTelefono($dataBody['phone']);
    $hashedPassword = $passwordHasher->hashPassword($newUser, $dataBody['password']); // Con esto hasheamos la password
    $newUser->setPassword($hashedPassword);

    $entityManager->persist($newUser);
    $entityManager->flush();

    $token = $this->JWTManager->create($newUser);

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

    return $this->json(['ok' => 'Te has registrado correctamente', 'results' => ['token' => $token, 'name' => $newUser->getNombre(), 'email' => $newUser->getEmail(), 'picture' => null]]);
  }

  private function verifyRequiredFields($dataBody) {
    $requiredFields = ['email', 'password', 'name', 'surname', 'phone'];

    foreach ($requiredFields as $field) {
      if (!isset($dataBody[$field])) {
        return "El campo '$field' es requerido.";
      }
    }

    return null;
  }
}

/* TODO
- Refactorizar el código (se repiten muchas líneas)
- Poner una columna "picture" en la bd?? De google recibimos también la foto de perfil, sería conveniente tener una columna para guardar imágenes
*/

/* TODO
- Subir imágenes desde el cliente
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;



/**
 * @Route("/upload", name="upload", methods={"POST"})
 *
public function upload(Request $request): Response
{
  $pictureFile = $request->files->get('picture');

  if ($pictureFile) {
    $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
    $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
    $newFilename = $safeFilename.'-'.uniqid().'.'.$pictureFile->guessExtension();

    try {
      $pictureFile->move(
        $this->getParameter('pictures_directory'),
        $newFilename
      );
    } catch (FileException $e) {
      // ... maneja la excepción si algo sale mal durante la carga del archivo
    }

    // Aquí puedes actualizar la entidad User con la ruta de la imagen
    // $user->setPicture($newFilename);
    // $entityManager->persist($user);
    // $entityManager->flush();

    return $this->json(['message' => 'Imagen subida con éxito']);
  }

  return $this->json(['message' => 'No se proporcionó ninguna imagen'], 400);
}
*/