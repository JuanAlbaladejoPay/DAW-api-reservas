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
  #[Route('/all', name: 'app_instalacion_index', methods: ['GET'])]
  public function index(InstalacionRepository $instalacionRepository): JsonResponse {

    return $this->json([
      'ok' => 'Todo ha ido correcto',
      'results' => $instalacionRepository->findAll(),
    ]);
  }

  #[Route('/show/{id}', name: 'app_instalacion_show', methods: ['GET'])]
  public function showInstallationInfo(Instalacion $installation): JsonResponse {
    if ($installation !== null) {
      return $this->json([
        'ok' => 'Todo ha ido correcto',
        'results' => [
          'id' => $installation->getId(),
          'nombre' => $installation->getNombre(),
          'precioHora' => $installation->getPrecioHora()
        ],
      ]);
    }

    return $this->json([
      'error' => 'No se ha encontrado esa instalación',
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
    return $this->json(['ok' => "Instalación <{$instalacion->getId()}> creada correctamente"]);
  }

  #[Route('/edit/{id}', name: 'app_instalacion_edit', methods: ['PATCH'])]
  public function edit(Request $request, Instalacion $instalacion, EntityManagerInterface $entityManager): Response {
    $dataBody = json_decode($request->getContent(), true);

    $nombre = $dataBody['nombre'];
    $precioHora = $dataBody['precioHora'];

    $instalacion->setNombre($nombre);
    $instalacion->setPrecioHora($precioHora);

    $entityManager->flush();

    return $this->json(['ok' => "Instalación <{$instalacion->getId()}> actualizada correctamente"]);
  }

  #[Route('/delete/{id}', name: 'app_instalacion_delete', methods: ['POST'])]
  public function delete(Instalacion $instalacion, EntityManagerInterface $entityManager): Response {
    $entityManager->remove($instalacion);
    $entityManager->flush();

    return $this->json(['ok' => "Instalación <{$instalacion->getId()}> eliminada correctamente"]);
  }
}

/* TODO
- Implementar la lógica de borrado de una instalación
- Comprobar que las rutas bloquean acceso a los usuarios que no son admins a los métodos new, delete y edit
*/