<?php

namespace App\Controller;

use App\Entity\Instalacion;
use App\Form\InstalacionType;
use App\Repository\InstalacionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/instalaciones')]
class InstalacionController extends AbstractController {
  #[Route('/', name: 'app_instalacion_index', methods: ['GET'])]
  public function index(InstalacionRepository $instalacionRepository): JsonResponse {

    return $this->json([
      'instalaciones' => $instalacionRepository->findAll(),
    ]);
  }

  #[Route('/new', name: 'app_instalacion_new', methods: ['GET', 'POST'])]
  public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse {
    $instalacion = new Instalacion();

    $dataBody = json_decode($request->getContent(), true); // Obtenemos los datos de la petición en un array asociativo (al pasar TRUE como parámetro)

    $nombre = $dataBody['nombre'];
    $precioHora = $dataBody['precioHora'];

    $instalacion->setNombre($nombre);
    $instalacion->setPrecioHora($precioHora);

    $entityManager->persist($instalacion);
    $entityManager->flush();

    // Insertar nueva instalación (igual que con user)
    return $this->json(['message' => 'Instalación creada correctamente']);
  }

  #[Route('/{id}', name: 'app_instalacion_show', methods: ['GET'])]
  public function show(Instalacion $instalacion): JsonResponse {


    return $this->json(["instalacion" => $instalacion]);
  }

  #[Route('/{id}/edit', name: 'app_instalacion_edit', methods: ['GET', 'POST'])]
  public function edit(Request $request, Instalacion $instalacion, EntityManagerInterface $entityManager): Response {
    $dataBody = json_decode($request->getContent(), true);

    $nombre = $dataBody['nombre'];
    $precioHora = $dataBody['precioHora'];

    $instalacion->setNombre($nombre);
    $instalacion->setPrecioHora($precioHora);

    $entityManager->flush();

    return $this->json(['message' => "Instalación <{$instalacion->getId()}> actualizada correctamente"]);
  }

  #[Route('/{id}', name: 'app_instalacion_delete', methods: ['POST'])]
  public function delete(Request $request, Instalacion $instalacion, EntityManagerInterface $entityManager): Response {
    if ($this->isCsrfTokenValid('delete' . $instalacion->getId(), $request->request->get('_token'))) {
      $entityManager->remove($instalacion);
      $entityManager->flush();
    }

    return $this->redirectToRoute('app_instalacion_index', [], Response::HTTP_SEE_OTHER);
  }
}

/* TODO
- Implementar la lógica de borrado de una instalación
*/