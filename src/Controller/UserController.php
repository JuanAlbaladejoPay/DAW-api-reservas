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

  /*   #[Route('/register', name: 'user_register', methods: ['POST'])] // Creo que hay que poner solo POST
  public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response {
    $user = new User();
    $dataBody = json_decode($request->getContent(), true); // Obtenemos los datos de la petición en un array asociativo (al pasar TRUE como parámetro)

    $email = $dataBody['email'];
    $hashedPassword = $passwordHasher->hashPassword($user, $dataBody['password']); // Con esto hasheamos la password
    $name = $dataBody['name'];
    $surname = $dataBody['surname'];
    $phone = $dataBody['phone'];

    $user->setEmail($email);
    $user->setRoles(['ROLE_USER']); // Por defecto, todos los usuarios son ROLE_USER (se podría cambiar en el formulario de registro
    $user->setPassword($hashedPassword);
    $user->setNombre($name);
    $user->setApellidos($surname);
    $user->setTelefono($phone);

    $entityManager->persist($user);
    $entityManager->flush();

    return $this->json("Usuario creado correctamente", Response::HTTP_CREATED);
    // Sería conveniente pasar por aquí la ruta de destino o en el cliente lo hacemos?
  } */

  #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
  public function new(Request $request, EntityManagerInterface $entityManager): Response {
    $user = new User();
    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $entityManager->persist($user);
      $entityManager->flush();

      return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->renderForm('user/new.html.twig', [
      'user' => $user,
      'form' => $form,
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
}

    /* 
    TODO:
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