<?php

namespace App\Controller\Security;
// He añadio \Security

use App\Repository\UserRepository;
use App\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

// --> EMAIL
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;


#[Route('/api', name: 'api_')]
class RegistrationController extends AbstractController {
  protected $JWTManager;
  protected EmailService $emailService;
  protected VerifyEmailHelperInterface $verifyEmailHelper;

  public function __construct(JWTTokenManagerInterface $JWTManager, EmailService $emailService, VerifyEmailHelperInterface $verifyEmailHelper) { // Añadimos el servicio de verificación de email al constructor
    $this->JWTManager = $JWTManager;
    $this->emailService = $emailService;
    $this->verifyEmailHelper = $verifyEmailHelper;
  }

  #[Route('/register', name: 'register', methods: 'POST')]
  public function index(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse {
    $dataBody = json_decode($request->getContent(), true);

    // Comprobamos que los campos requeridos están en la petición
    $missingFieldMessage = $this->verifyRequiredFields($dataBody);
    if ($missingFieldMessage) {
      return $this->json(['error' => $missingFieldMessage], 400);
    }

    // Compruebo si ya existe este usuario en la BD (por si se registró con Google pero no puso password)
    $email = $dataBody['email'];
    $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

    // Si el usuario existía pero no tiene password (Viene de GOOGLE), le añadimos la password y el teléfono (previamente comprobamos que realmente es el mismo usuario)
    if ($user && $user->getPassword() === null) { // --> GOOGLE <--
      $name = $dataBody['name'];
      $surname = $dataBody['surname'];
      // Comprobamos que es el mismo user que inició sesión con Google (comprobando que el nombre y apellidos coinciden)
      if ($user->getNombre() !== $name || $user->getApellidos() !== $surname) {
        return $this->json(['error' => 'Ya existe un usuario con ese email'], 409);
      }

      $hashedPassword = $passwordHasher->hashPassword($user, $dataBody['password']); // Con esto hasheamos la password
      $user->setPassword($hashedPassword);
      $user->setTelefono($dataBody['phone']);
      // También seteamos sus roles y el isVerified para que tenga que verificar su correo
      $user->setRoles([]);
      $user->setVerified(false);

      $entityManager->flush();

      // Creamos la URL de verificación
      $urlVerificacion = $this->createUrlVerification($user->getId(), $user->getEmail());

      $this->emailService->sendRegistrationEmail($user->getEmail(), $urlVerificacion);

      return $this->json(['ok' => 'Te has registrado correctamente. Verifica tu email para iniciar sesión']);
    }

    // Compruebo que el usuario ya existía en la BD teniendo password (para responder que ya existe un usuario con ese email)
    if ($user && $user->getPassword() !== null) {
      return $this->json(['error' => 'Ya existe un usuario con ese email'], 409);
    }

    // Si el usuario no existía previamente lo creamos
    $newUser = new User();
    $newUser->setEmail($email);
    $newUser->setNombre($dataBody['name']);
    $newUser->setApellidos($dataBody['surname']);
    $newUser->setTelefono($dataBody['phone']);
    $hashedPassword = $passwordHasher->hashPassword($newUser, $dataBody['password']); // Con esto hasheamos la password
    $newUser->setPassword($hashedPassword);

    $entityManager->persist($newUser);
    $entityManager->flush();

    // Creamos la URL de verificación
    $urlVerificacion = $this->createUrlVerification($newUser->getId(), $newUser->getEmail());

    $this->emailService->sendRegistrationEmail($newUser->getEmail(), $urlVerificacion);

    return $this->json(['ok' => 'Te has registrado correctamente. Verifica tu email para iniciar sesión']);
  }

  #[Route('/verify/email', name: 'app_verify_email')]
  public function verifyUserEmail(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): JsonResponse {
    $user = $userRepository->find($request->query->get('id'));
    if (!$user) {
      return $this->json(['error' => 'No se encuentra ese usuario']);
    }
    try {
      $this->verifyEmailHelper->validateEmailConfirmation(
        $request->getUri(),
        $user->getId(),
        $user->getEmail(),
      );

      $user->setVerified(true);
      $user->setRoles(['ROLE_USER']);
      $entityManager->flush();

    } catch (VerifyEmailExceptionInterface $e) {
      $this->addFlash('error', $e->getReason());
      return $this->json(['error' => 'Ha ocurrido un error al verificar el email']);
    }
    return $this->json(['ok' => 'Email verificado correctamente']);
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

  private function createUrlVerification(int $id, string $email): string {
    $signatureComponents = $this->verifyEmailHelper->generateSignature(
      'api_app_verify_email',
      strval($id),
      $email,
      ['id' => $id]
    );

    return $signatureComponents->getSignedUrl();
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