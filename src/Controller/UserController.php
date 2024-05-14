<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


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

  #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
  public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response {
    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $entityManager->flush();

      return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->renderForm('user/edit.html.twig', [
      'user' => $user,
      'form' => $form,
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

  #[Route('/update/{id}', name: 'app_user_update', methods: ['PATCH'])]
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
    $data = json_decode($request->getContent(), true);
    
    // Actualiza los campos del usuario
    if (isset($data['nombre'])) {
      $user->setNombre($data['nombre']);
    }
    if (isset($data['apellidos'])) {
      $user->setApellidos($data['apellidos']);
    }
    if (isset($data['telefono'])) {
      $user->setTelefono($data['telefono']);
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