<?php

namespace App\Controller;

use App\Entity\Reserva;
use App\Form\ReservaType;
use App\Repository\ReservaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/reservas')]
class ReservaController extends AbstractController {
  #[Route('/all', name: 'app_reserva_index', methods: ['GET'])]
  public function index(ReservaRepository $reservaRepository): JsonResponse {

    return $this->json(["reservas" => $reservaRepository->findAll()]);
  }

  #[Route('/new', name: 'app_reserva_new', methods: ['GET', 'POST'])]
  public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse {
    $reserva = new Reserva();

    $dataBody = json_decode($request->getContent(), true);

    $fecha = new \DateTime($dataBody['fecha']);
    $hora = new \DateTime($dataBody['hora']);
    $duracion = $dataBody['duracion'];
    $importe = $dataBody['importe'];
    $idUsuario = $dataBody['idUsuario'];
    $idInstalacion = $dataBody['idInstalacion'];

    $reserva->setFecha($fecha);
    $reserva->setHora($hora);
    $reserva->setDuracion($duracion);
    $reserva->setImporte($importe);
    $reserva->setIdUsuario($idUsuario);
    $reserva->setIdInstalacion($idInstalacion);

    $entityManager->persist($reserva);
    $entityManager->flush();

    return $this->json(['message' => 'Reserva creada correctamente']);
  }

  #[Route('/{id}', name: 'app_reserva_show', methods: ['GET'])]
  public function show(Reserva $reserva): JsonResponse {
    return $this->json(["reserva" => $reserva]);
  }

  #[Route('/{id}/edit', name: 'app_reserva_edit', methods: ['GET', 'POST'])]
  public function edit(Request $request, Reserva $reserva, EntityManagerInterface $entityManager): JsonResponse {
    $dataBody = json_decode($request->getContent(), true);

    $fecha = new \DateTime($dataBody['fecha']);
    $hora = new \DateTime($dataBody['hora']);
    $duracion = $dataBody['duracion'];
    $importe = $dataBody['importe'];
    $idInstalacion = $dataBody['idInstalacion'];

    $reserva->setFecha($fecha);
    $reserva->setHora($hora);
    $reserva->setDuracion($duracion);
    $reserva->setImporte($importe);
    $reserva->setIdInstalacion($idInstalacion);

    $entityManager->flush();

    return $this->json(['message' => "Reserva <{$reserva->getId()}> actualizada correctamente"]);
  }

  #[Route('/{id}', name: 'app_reserva_delete', methods: ['POST'])]
  public function delete(Request $request, Reserva $reserva, EntityManagerInterface $entityManager): Response {
    if ($this->isCsrfTokenValid('delete' . $reserva->getId(), $request->request->get('_token'))) {
      $entityManager->remove($reserva);
      $entityManager->flush();
    }

    return $this->redirectToRoute('app_reserva_index', [], Response::HTTP_SEE_OTHER);
  }
}

/* TODO
- Editar el método de DELETE reservas
*/
