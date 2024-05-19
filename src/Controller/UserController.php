<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;


#[Route('/api/user')]
class UserController extends AbstractController {
  #[Route('/', name: 'app_user_index', methods: ['GET'])]
  public function index(UserRepository $userRepository): Response {
    return $this->render('user/index.html.twig', [
      'users' => $userRepository->findAll(),
    ]);
  }

  #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
  public function show(User $user): Response {
    return $this->render('user/show.html.twig', [
      'user' => $user,
    ]);
  }

  #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
  public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response {
    if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
      $entityManager->remove($user);
      $entityManager->flush();
    }

    return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
  }

  #[Route('/update/{id}', name: 'app_user_update', methods: ['POST'])]
  public function update($id, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response {
    $user = $userRepository->find($id);

    if (!$user) {
      return $this->json(['message' => "El usuario con id <{$id}> no existe"], 404);
    }

    // Comprueba si el usuario actual tiene permiso para actualizar el usuario
    /** @var \App\Entity\User $usuarioActual */
    $usuarioActual = $this->getUser();
    if ($user->getId() !== $usuarioActual->getId()) {
      return $this->json(['message' => "No tienes permiso para actualizar este usuario"], 403);
    }

    // Obtiene los datos de la solicitud
    $nombre = $request->request->get('nombre');
    $apellidos = $request->request->get('apellidos');
    $telefono = $request->request->get('telefono');

    // Actualiza los campos del usuario
    if ($nombre !== null) {
      $user->setNombre($nombre);
    }
    if ($apellidos !== null) {
      $user->setApellidos($apellidos);
    }
    if ($telefono !== null) {
      $user->setTelefono($telefono);
    }
    if ($request->files->has('picture')) {
      $pictureFile = $request->files->get('picture');
      if ($pictureFile->isValid() && in_array($pictureFile->getMimeType(), ['image/jpeg', 'image/png', 'image/gif'])) {
        // Crea un cliente de Google
        $client = new Google_Client();
        $client->setAuthConfig(dirname(__DIR__, 2) . '/service_account.json');
        $client->addScope(Google_Service_Drive::DRIVE);
        $client->setSubject('reservas-drive@api-reservas-421108.iam.gserviceaccount.com');

        $driveService = new Google_Service_Drive($client);

        $fileMetadata = new Google_Service_Drive_DriveFile(array(
          'name' => $user->getId() . '_image',
          'parents' => array('1qC_YWSMnUUtXfk_LwTPVAGRmsHNJbgh-') // ID de la carpeta de Google Drive
        ));

        $content = file_get_contents($pictureFile->getPathname());

        $file = $driveService->files->create($fileMetadata, array(
          'data' => $content,
          'mimeType' => $pictureFile->getMimeType(),
          'uploadType' => 'multipart',
          'fields' => 'id'
        ));

        $permission = new \Google_Service_Drive_Permission();
        $permission->setRole('reader');
        $permission->setType('anyone');

        $driveService->permissions->create($file->id, $permission);
        $publicFileLink = 'https://drive.google.com/uc?id=' . $file->id;

        $user->setPicture($publicFileLink);
      }
    }

    // Guarda los cambios en la base de datos
    $entityManager->flush();

    return $this->json(['message' => "Usuario <{$user->getId()}> actualizado correctamente"]);
  }

}

/*
TODO:
- Controlar las rutas a las que puede acceder un usuario
- Se podría añadir esto para manejar errores

try {
  $params = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);


  return new Response(null, Response::HTTP_NO_CONTENT);
} catch (\JsonException $e) {
} catch (ValidatorException $e) {
  return new Response($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
} catch (InvalidAnnualLeaveIntervalException $e) {
  return new Response($e->getMessage(), Response::HTTP_CONFLICT);
} catch (\Throwable $e) {
  return new Response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
} */